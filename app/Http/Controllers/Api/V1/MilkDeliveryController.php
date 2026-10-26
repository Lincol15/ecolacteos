<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMilkDeliveryRequest;
use App\Http\Requests\UpdateMilkDeliveryRequest;
use App\Http\Resources\MilkDeliveryResource;
use App\Models\MilkDelivery;
use App\Models\RouteStop;
use App\Models\User;
use App\Services\MilkDeliveryService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class MilkDeliveryController extends Controller
{
    /**
     * Recolecciones visibles según el rol: el acopiador ve las suyas, el productor las de su ficha,
     * y el personal de gestión/planta/calidad todas.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = MilkDelivery::with('producer.user', 'collector');

        if ($user->isCollector()) {
            $query->where('collector_id', $user->id);
        } elseif ($user->isProducer()) {
            $query->where('producer_id', $user->producer?->id ?? 0);
        } elseif ($collectorId = $request->collector_id) {
            $query->where('collector_id', $collectorId);
        }

        if ($from = $request->from) {
            $query->where('delivery_date', '>=', $from);
        }
        if ($to = $request->to) {
            $query->where('delivery_date', '<=', $to);
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($producerId = $request->producer_id) {
            $query->where('producer_id', $producerId);
        }
        if ($request->filled('recibido')) {
            $query->where('recibido', $request->boolean('recibido'));
        }

        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        return MilkDeliveryResource::collection(
            $query->latest('delivery_date')->latest('id')->paginate($perPage)->withQueryString()
        );
    }

    public function show(Request $request, MilkDelivery $delivery): MilkDeliveryResource
    {
        $this->ensureCanView($request->user(), $delivery);

        return new MilkDeliveryResource($delivery->load('producer.user', 'collector'));
    }

    public function store(StoreMilkDeliveryRequest $request, MilkDeliveryService $deliveryService)
    {
        $user = $request->user();
        $data = $request->validated();

        // Reintento de una recolección sincronizada desde el modo sin conexión: ya existe, no se duplica.
        if ($existing = $this->findSyncedDelivery($data['client_uuid'] ?? null, $user)) {
            return new MilkDeliveryResource($existing->load('producer.user', 'collector'));
        }

        if ($user->isCollector()) {
            $this->ensureCollectorCanRegister($user, $data);
            $collectorId = $user->id;
        } else {
            $collectorId = $this->resolveCollectorForManager($request) ?? $user->id;
        }

        try {
            $delivery = $deliveryService->create($data, $collectorId);
        } catch (UniqueConstraintViolationException $exception) {
            // Dos envíos simultáneos del mismo registro: el otro ya lo guardó.
            $delivery = $this->findSyncedDelivery($data['client_uuid'] ?? null, $user) ?? throw $exception;

            return new MilkDeliveryResource($delivery->load('producer.user', 'collector'));
        }

        return (new MilkDeliveryResource($delivery->load('producer.user', 'collector')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Entrega ya registrada con el identificador generado por la app, solo si pertenece al mismo usuario.
     */
    private function findSyncedDelivery(?string $clientUuid, User $user): ?MilkDelivery
    {
        if ($clientUuid === null) {
            return null;
        }

        $delivery = MilkDelivery::where('client_uuid', $clientUuid)->first();

        if ($delivery && $user->isCollector() && $delivery->collector_id !== $user->id) {
            throw ValidationException::withMessages([
                'client_uuid' => 'El identificador de la recolección ya está en uso.',
            ]);
        }

        return $delivery;
    }

    public function update(UpdateMilkDeliveryRequest $request, MilkDelivery $delivery, MilkDeliveryService $deliveryService): MilkDeliveryResource
    {
        $delivery = $deliveryService->update($delivery, $request->validated());

        return new MilkDeliveryResource($delivery->load('producer.user', 'collector'));
    }

    /**
     * Aceptar o rechazar una entrega (solo gestión).
     */
    public function updateStatus(Request $request, MilkDelivery $delivery, MilkDeliveryService $deliveryService): MilkDeliveryResource
    {
        $data = $request->validate([
            'status' => 'required|in:aceptado,rechazado',
            'reason' => 'required_if:status,rechazado|nullable|string|max:500',
        ]);

        $delivery = $data['status'] === 'aceptado'
            ? $deliveryService->approve($delivery, $request->user()->id)
            : $deliveryService->reject($delivery, $data['reason'], $request->user()->id);

        return new MilkDeliveryResource($delivery->load('producer.user', 'collector'));
    }

    /**
     * Registrar la recepción de la entrega en planta.
     */
    public function receive(MilkDelivery $delivery, MilkDeliveryService $deliveryService): MilkDeliveryResource
    {
        $delivery = $deliveryService->markReceived($delivery);

        return new MilkDeliveryResource($delivery->load('producer.user', 'collector'));
    }

    private function ensureCanView(User $user, MilkDelivery $delivery): void
    {
        $allowed = match (true) {
            $user->isCollector() => $delivery->collector_id === $user->id,
            $user->isProducer() => $delivery->producer_id === $user->producer?->id,
            default => true,
        };

        if (! $allowed) {
            abort(403, 'No tiene acceso a esta entrega.');
        }
    }

    /**
     * El acopiador solo registra para sus productores asignados o para la parada de una ruta suya.
     *
     * @param  array{producer_id: int|string, route_stop_id?: int|string|null}  $data
     */
    private function ensureCollectorCanRegister(User $collector, array $data): void
    {
        $routeStop = ! empty($data['route_stop_id']) ? RouteStop::with('collectionRoute')->find($data['route_stop_id']) : null;

        if ($routeStop && $routeStop->collectionRoute?->collector_id !== $collector->id) {
            throw ValidationException::withMessages([
                'route_stop_id' => 'La parada no pertenece a una de sus rutas.',
            ]);
        }

        $isAssigned = $collector->resolveAssignedProducers()->contains('id', (int) $data['producer_id']);
        $isRouteStopProducer = $routeStop && $routeStop->producer_id === (int) $data['producer_id'];

        if (! $isAssigned && ! $isRouteStopProducer) {
            throw ValidationException::withMessages([
                'producer_id' => 'El productor no está asignado a su cuenta.',
            ]);
        }
    }

    private function resolveCollectorForManager(Request $request): ?int
    {
        if (! $request->filled('collector_id')) {
            return null;
        }

        $request->validate([
            'collector_id' => 'integer|exists:users,id,role,acopiador',
        ]);

        return $request->integer('collector_id');
    }
}
