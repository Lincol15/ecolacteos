<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sanction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'producer_id',
        'issued_by',
        'type',
        'motivo',
        'description',
        'amount',
        'status',
        'sanction_date',
        'resolved_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sanction_date' => 'date',
            'resolved_date' => 'date',
        ];
    }

    public const TYPES = [
        'calidad' => 'Calidad',
        'incumplimiento' => 'Incumplimiento',
        'fraude' => 'Fraude',
        'pesaje' => 'Pesaje',
        'otro' => 'Otro',
    ];

    public const STATUS = [
        'activa' => 'Activa',
        'cumplida' => 'Cumplida',
        'anulada' => 'Anulada',
    ];

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
