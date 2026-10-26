<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\NotificationRead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    /**
     * Avisos visibles para el usuario (misma regla que la web) con su estado de lectura.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        // El administrador puede ver todos los avisos publicados para gestionarlos.
        if ($user->isAdmin() && $request->scope === 'all') {
            return NotificationResource::collection(Notification::latest()->paginate(100));
        }

        $notifications = Notification::visibleForUser($user)
            ->withExists(['reads as read_by_user' => fn ($q) => $q->where('user_id', $user->id)])
            ->paginate(50);

        return NotificationResource::collection($notifications);
    }

    public function store(StoreNotificationRequest $request)
    {
        $data = $request->validated();
        $notification = Notification::create([
            'created_by' => $request->user()->id,
            ...$data,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return (new NotificationResource($notification))->response()->setStatusCode(201);
    }

    public function markAsRead(Request $request, Notification $notification): NotificationResource
    {
        $user = $request->user();
        if (! Notification::visibleForUser($user)->whereKey($notification->id)->exists()) {
            abort(403, 'Este aviso no está dirigido a su cuenta.');
        }

        NotificationRead::firstOrCreate([
            'notification_id' => $notification->id,
            'user_id' => $user->id,
        ], ['read_at' => now()]);
        $notification->setAttribute('read_by_user', true);

        return new NotificationResource($notification);
    }
}
