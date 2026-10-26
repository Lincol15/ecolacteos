<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectorController extends Controller
{
    /**
     * Asignación vigente del acopiador: comunidad, productores asignados y si es lista individual.
     */
    public function assignment(User $collector): JsonResponse
    {
        $this->ensureCollector($collector);

        return response()->json(['data' => $this->assignmentData($collector)]);
    }

    /**
     * Misma regla que "Configurar Asignación" en la web.
     */
    public function updateAssignment(Request $request, User $collector): JsonResponse
    {
        $this->ensureCollector($collector);
        $data = $request->validate(User::ASSIGNMENT_RULES);
        $collector->assignProducers($data['comunidad'] ?? null, $data['producer_ids'] ?? []);

        return response()->json(['data' => $this->assignmentData($collector->fresh())]);
    }

    /**
     * Vehículo asignado al acopiador (se copia como placa en cada entrega que registra).
     */
    public function updateVehicle(Request $request, User $collector): JsonResponse
    {
        $this->ensureCollector($collector);
        $data = $request->validate(['vehiculo' => 'nullable|string|max:20']);
        $collector->update($data);

        return response()->json(['data' => $this->assignmentData($collector->fresh())]);
    }

    /**
     * @return array{collector_id: int, comunidad: ?string, vehiculo: ?string, individual: bool, producer_ids: array<int, int>}
     */
    private function assignmentData(User $collector): array
    {
        return [
            'collector_id' => $collector->id,
            'comunidad' => $collector->comunidad,
            'vehiculo' => $collector->vehiculo,
            'individual' => $collector->assignedProducers()->exists(),
            'producer_ids' => $collector->resolveAssignedProducers()->pluck('id')->sort()->values()->all(),
        ];
    }

    private function ensureCollector(User $user): void
    {
        if ($user->role !== 'acopiador') {
            abort(404);
        }
    }
}
