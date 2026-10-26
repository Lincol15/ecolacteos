<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'lastname',
        'dni',
        'email',
        'phone',
        'address',
        'comunidad',
        'vehiculo',
        'monthly_salary',
        'password',
        'role',
        'active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'monthly_salary' => 'decimal:2',
        ];
    }

    public const ROLES = [
        'admin' => 'Administrador',
        'gerente' => 'Gerente',
        'acopiador' => 'Acopiador',
        'control_calidad' => 'Control de Calidad',
        'trabajador_planta' => 'Trabajador de Planta',
        'productor' => 'Productor',
    ];

    public function hasRole($roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'gerente'], true);
    }

    public function isCollector(): bool
    {
        return $this->role === 'acopiador';
    }

    public function isQuality(): bool
    {
        return $this->role === 'control_calidad';
    }

    public function isPlantWorker(): bool
    {
        return $this->role === 'trabajador_planta';
    }

    public function isProducer(): bool
    {
        return $this->role === 'productor';
    }

    public function getFullnameAttribute(): string
    {
        return trim(($this->name ?? '').' '.($this->lastname ?? ''));
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? 'N/A';
    }

    public function producer()
    {
        return $this->hasOne(Producer::class);
    }

    public function milkDeliveriesAsCollector()
    {
        return $this->hasMany(MilkDelivery::class, 'collector_id');
    }

    public function assignedRoutes()
    {
        return $this->hasMany(CollectionRoute::class, 'collector_id');
    }

    public function collectorPayments()
    {
        return $this->hasMany(CollectorPayment::class, 'collector_id');
    }

    public function assignedProducers()
    {
        return $this->belongsToMany(Producer::class, 'collector_producer_assignments', 'collector_id', 'producer_id')
            ->withTimestamps();
    }

    /**
     * Productores que este acopiador atiende, según lo que asigna el admin en
     * "Configurar Asignación" del detalle del acopiador:
     * 1. Asignación individual explícita (checklist de excepciones), si existe.
     * 2. Si no hay ninguna, los productores activos de su comunidad asignada.
     */
    public function resolveAssignedProducers()
    {
        $explicit = $this->assignedProducers()->with('user')->get();
        if ($explicit->isNotEmpty()) {
            return $explicit;
        }

        if (! $this->comunidad) {
            return collect();
        }

        return Producer::where('status', 'activo')->where('comunidad', $this->comunidad)->with('user')->get();
    }

    /**
     * Reglas de validación de la asignación de productores (web y API).
     *
     * @var array<string, string>
     */
    public const ASSIGNMENT_RULES = [
        'comunidad' => 'nullable|string|max:150',
        'producer_ids' => 'nullable|array',
        'producer_ids.*' => 'integer|exists:producers,id',
    ];

    /**
     * Asigna al acopiador una comunidad y/o una lista individual de productores.
     * Si lo marcado es exactamente la comunidad, no se guarda lista individual:
     * así los productores nuevos de esa comunidad se asignan solos.
     *
     * @param  array<int, int|string>  $producerIds
     * @return int total de productores que quedan asignados
     */
    public function assignProducers(?string $comunidad, array $producerIds): int
    {
        $selectedIds = collect($producerIds)->map(fn ($id) => (int) $id)->unique()->sort()->values();
        $comunidadIds = $comunidad
            ? Producer::where('status', 'activo')->where('comunidad', $comunidad)->pluck('id')->sort()->values()
            : collect();

        $this->update(['comunidad' => $comunidad]);
        $this->assignedProducers()->sync(
            $comunidad && $selectedIds->all() === $comunidadIds->all() ? [] : $selectedIds->all()
        );

        return $this->resolveAssignedProducers()->count();
    }

    public function complaintsReported()
    {
        return $this->hasMany(Complaint::class, 'user_id');
    }

    public function complaintsAssigned()
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    public function sanctionsIssued()
    {
        return $this->hasMany(Sanction::class, 'issued_by');
    }
}
