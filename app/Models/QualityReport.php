<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'milk_delivery_id',
        'analyst_id',
        'analyzer_model',
        'sample_code',
        'grasa_pct',
        'proteina_pct',
        'lactosa_pct',
        'solidos_no_grasos_pct',
        'total_solidos_pct',
        'agua_aniadida_pct',
        'ph',
        'punto_congelacion',
        'acidez_dornic',
        'densidad',
        'observations',
        'result',
        'rejection_reason',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'grasa_pct' => 'decimal:2',
            'proteina_pct' => 'decimal:2',
            'lactosa_pct' => 'decimal:2',
            'solidos_no_grasos_pct' => 'decimal:2',
            'total_solidos_pct' => 'decimal:2',
            'agua_aniadida_pct' => 'decimal:2',
            'ph' => 'decimal:2',
            'punto_congelacion' => 'decimal:3',
            'analyzed_at' => 'datetime',
        ];
    }

    public const RESULTS = [
        'aprobado' => 'Aprobado',
        'rechazado' => 'Rechazado',
        'aceptable' => 'Aceptable',
        'observado' => 'Observado',
    ];

    public const REJECTION_REASONS = [
        'ninguno' => 'Ninguno',
        'baja_grasa' => 'Baja Grasa',
        'baja_proteina' => 'Baja Proteína',
        'exceso_agua' => 'Exceso de Agua',
        'acidez_alta' => 'Acidez Alta',
        'ph_anormal' => 'pH Anormal',
        'congelacion_anormal' => 'Punto de Congelación Anormal',
        'contaminacion' => 'Contaminación',
    ];

    public const QUALITY_PARAMS = [
        'grasa_pct' => ['label' => 'Grasa', 'unit' => '%', 'min' => 3.2, 'max' => 5.5],
        'proteina_pct' => ['label' => 'Proteína', 'unit' => '%', 'min' => 2.9, 'max' => 4.2],
        'lactosa_pct' => ['label' => 'Lactosa', 'unit' => '%', 'min' => 4.5, 'max' => 5.2],
        'solidos_no_grasos_pct' => ['label' => 'SNG', 'unit' => '%', 'min' => 8.3, 'max' => 9.5],
        'total_solidos_pct' => ['label' => 'Total Sólidos', 'unit' => '%', 'min' => 11.5, 'max' => 15.0],
        'agua_aniadida_pct' => ['label' => 'Agua Añadida', 'unit' => '%', 'min' => 0, 'max' => 5],
        'ph' => ['label' => 'pH', 'unit' => '', 'min' => 6.4, 'max' => 6.8],
        'punto_congelacion' => ['label' => 'Pto. Congelación', 'unit' => '°C', 'min' => -0.550, 'max' => -0.510],
    ];

    public function milkDelivery()
    {
        return $this->belongsTo(MilkDelivery::class);
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }

    public function producer()
    {
        return $this->hasOneThrough(Producer::class, MilkDelivery::class);
    }

    public function isParamInRange(string $param): bool
    {
        if (!isset(self::QUALITY_PARAMS[$param]) || !isset($this->{$param})) {
            return true;
        }
        $spec = self::QUALITY_PARAMS[$param];
        return $this->{$param} >= $spec['min'] && $this->{$param} <= $spec['max'];
    }

    public function qualityScore(): float
    {
        $params = array_keys(self::QUALITY_PARAMS);
        $inRange = 0;
        $count = 0;
        foreach ($params as $param) {
            if (isset($this->{$param})) {
                $count++;
                if ($this->isParamInRange($param)) {
                    $inRange++;
                }
            }
        }
        return $count > 0 ? round(($inRange / $count) * 100, 1) : 0;
    }
}
