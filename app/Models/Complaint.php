<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'producer_id',
        'user_id',
        'category',
        'subject',
        'description',
        'evidence_files',
        'priority',
        'status',
        'staff_response',
        'assigned_to',
        'responded_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'evidence_files' => 'array',
            'responded_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public const CATEGORIES = [
        'precio' => 'Precio',
        'pesaje' => 'Pesaje',
        'calidad' => 'Calidad',
        'pago' => 'Pago',
        'atencion' => 'Atención',
        'ruta' => 'Ruta',
        'otro' => 'Otro',
    ];

    public const PRIORITIES = [
        'baja' => 'Baja',
        'normal' => 'Normal',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    public const STATUS = [
        'abierto' => 'Abierto',
        'en_revision' => 'En Revisión',
        'respondido' => 'Respondido',
        'cerrado' => 'Cerrado',
        'rechazado' => 'Rechazado',
    ];

    /**
     * Siguiente número de ticket correlativo (TK-AÑO-00001).
     */
    public static function nextTicketNumber(): string
    {
        return 'TK-'.now()->year.'-'.str_pad((string) (self::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Actualiza estado/respuesta registrando la fecha de respuesta y de cierre la primera vez.
     *
     * @param  array{status: string, staff_response?: ?string, assigned_to?: ?int}  $data
     */
    public function updateStatus(array $data): self
    {
        if ($data['status'] === 'respondido' && $this->status !== 'respondido') {
            $data['responded_at'] = now();
        }
        if ($data['status'] === 'cerrado' && $this->status !== 'cerrado') {
            $data['closed_at'] = now();
        }
        $this->update($data);

        return $this;
    }

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
