<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProducerRequest;
use App\Http\Requests\UpdateProducerApiRequest;
use App\Http\Resources\ProducerResource;
use App\Models\Producer;
use App\Services\ProducerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProducerController extends Controller
{
    /**
     * El acopiador recibe solo sus productores asignados (misma regla que la web);
     * el resto de roles autorizados consulta el padrón completo con filtros.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->isCollector()) {
            $producers = $user->resolveAssignedProducers()->sortBy('code')->values();

            return ProducerResource::collection($producers);
        }

        $query = Producer::with('user');
        if ($search = $request->search) {
            $like = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($search, $like) {
                $q->whereHas('user', fn ($uq) => $uq
                    ->where(DB::raw('LOWER(name)'), 'like', $like)
                    ->orWhere(DB::raw('LOWER(lastname)'), 'like', $like)
                    ->orWhere('dni', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"))
                    ->orWhere('comunidad', 'like', "%{$search}%");
            });
        }
        if ($comunidad = $request->comunidad) {
            $query->where('comunidad', $comunidad);
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        return ProducerResource::collection($query->orderBy('code')->paginate($perPage)->withQueryString());
    }

    public function show(Request $request, Producer $producer): ProducerResource
    {
        $user = $request->user();
        if ($user->isCollector() && ! $user->resolveAssignedProducers()->contains('id', $producer->id)) {
            abort(403, 'Este productor no está asignado a su cuenta.');
        }

        return new ProducerResource($producer->load('user'));
    }

    public function store(StoreProducerRequest $request, ProducerService $producerService)
    {
        $producer = $producerService->register($request->validated());

        return (new ProducerResource($producer->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProducerApiRequest $request, Producer $producer, ProducerService $producerService): ProducerResource
    {
        return new ProducerResource($producerService->update($producer, $request->validated()));
    }

    /**
     * Elimina un productor sin historial (sin entregas ni pagos); si tiene, responde 422.
     */
    public function destroy(Producer $producer, ProducerService $producerService): Response
    {
        $producerService->delete($producer);

        return response()->noContent();
    }

    public function updateStatus(Request $request, Producer $producer): ProducerResource
    {
        $data = $request->validate([
            'status' => 'required|in:activo,inactivo',
        ]);
        $producer->update($data);

        return new ProducerResource($producer->fresh('user'));
    }
}
