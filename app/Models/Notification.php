<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'target',
        'title',
        'message',
        'priority',
        'created_by',
        'published_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public const TARGETS = [
        'todos' => 'Todos',
        'productores' => 'Productores',
        'acopiadores' => 'Acopiadores',
        'gerencia' => 'Gerencia',
        'admin' => 'Administradores',
    ];

    public const PRIORITIES = [
        'baja' => 'Baja',
        'normal' => 'Normal',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleForUser($query, User $user)
    {
        $targets = ['todos'];
        if ($user->isProducer()) {
            $targets[] = 'productores';
        }
        if ($user->isCollector()) {
            $targets[] = 'acopiadores';
        }
        if ($user->isManager()) {
            $targets[] = 'gerencia';
        }
        if ($user->isAdmin()) {
            $targets[] = 'admin';
        }

        return $query
            ->whereIn('target', $targets)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('priority')
            ->orderByDesc('published_at');
    }
}
