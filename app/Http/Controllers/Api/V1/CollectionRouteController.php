<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionRouteResource;
use App\Http\Resources\RouteStopResource;
use App\Models\CollectionRoute;
use App\Models\RouteStop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollectionRouteController extends Controller
{
    /**
     * El acopiador ve solo sus rutas; gestión ve todas.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = CollectionRoute::with('stops.producer.user');

        if ($user->isCollector()) {
            $query->where('collector_id', $user->id);
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($day = $request->day) {
            $query->where('day', $day);
        }

        return CollectionRouteResource::collection($query->latest()->paginate(20)->withQueryString());
    }

    public function show(Request $request, CollectionRoute $route): CollectionRouteResource
    {
        $user = $request->user();
        if ($user->isCollector() && $route->collector_id !== $user->id) {
            abort(403, 'Esta ruta no está asignada a su cuenta.');
        }

        return new CollectionRouteResource($route->load('stops.producer.user'));
    }

    /**
     * Actualizar estado de una parada (misma regla que la web del acopiador).
     */
    public function updateStop(Request $request, RouteStop $stop): RouteStopResource
    {
        if ($stop->collectionRoute->collector_id !== $request->user()->id) {
            abort(403, 'Esta parada no pertenece a una de sus rutas.');
        }

        $data = $request->validate([
            'status' => 'required|in:pendiente,en_camino,visitado,ausente,rechazado',
            'estimated_liters' => 'nullable|numeric|min:0',
        ]);

        return new RouteStopResource($stop->updateStatus($data)->load('producer.user'));
    }
}
