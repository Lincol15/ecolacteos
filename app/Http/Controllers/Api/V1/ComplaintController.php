<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ComplaintController extends Controller
{
    /**
     * El productor ve sus reclamos; gestión ve todos.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Complaint::with('producer.user');

        if ($user->isProducer()) {
            $query->where('producer_id', $user->producer?->id ?? 0);
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        return ComplaintResource::collection($query->latest()->paginate($perPage)->withQueryString());
    }

    /**
     * Mismas reglas que el formulario web del productor.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user->producer) {
            abort(403, 'Su cuenta no tiene una ficha de productor.');
        }

        $data = $request->validate([
            'category' => 'required|in:precio,pesaje,calidad,pago,atencion,ruta,otro',
            'priority' => 'required|in:baja,normal,alta,urgente',
            'subject' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
        ]);

        $complaint = Complaint::create([
            ...$data,
            'ticket_number' => Complaint::nextTicketNumber(),
            'producer_id' => $user->producer->id,
            'user_id' => $user->id,
            'status' => 'abierto',
        ]);

        return (new ComplaintResource($complaint->load('producer.user')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Responder / cambiar estado (mismas reglas que el panel web del administrador).
     */
    public function update(Request $request, Complaint $complaint): ComplaintResource
    {
        $data = $request->validate([
            'status' => 'required|in:abierto,en_revision,respondido,cerrado,rechazado',
            'staff_response' => 'nullable|string|max:2000',
        ]);

        return new ComplaintResource($complaint->updateStatus($data)->load('producer.user'));
    }
}
