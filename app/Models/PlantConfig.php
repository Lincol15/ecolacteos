<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantConfig extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'key',
        'label',
        'value',
        'value_type',
        'description',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'value_type' => 'string',
        ];
    }

    public const VALUE_TYPES = [
        'string' => 'Texto',
        'number' => 'Número',
        'boolean' => 'Booleano',
        'date' => 'Fecha',
        'text' => 'Texto Largo',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getValue(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();
        if (!$config) {
            return $default;
        }
        return self::castValue($config->value, $config->value_type);
    }

    public static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'number' => (float) $value,
            'boolean' => (bool) $value,
            'date' => $value ? new \Carbon\Carbon($value) : null,
            default => $value,
        };
    }

    public function getCastedValueAttribute(): mixed
    {
        return self::castValue($this->value, $this->value_type);
    }
}
