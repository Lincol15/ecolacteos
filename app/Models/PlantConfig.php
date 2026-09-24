<?php

namespace App\Models;

use Carbon\Carbon;
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

    /**
     * Ajustes conocidos del sistema, agrupados como se muestran en Configuración.
     *
     * @var array<string, array{title: string, icon: string, description: string, settings: array<string, array{label: string, type: string, default: string, help: string, placeholder?: string, suffix?: string, prefix?: string, options?: array<int, string>}>}>
     */
    public const SETTINGS = [
        'empresa' => [
            'title' => 'Empresa y contacto',
            'icon' => '🏢',
            'description' => 'Se muestran en la página web pública (pie de página, Contacto e Inicio) y en los comprobantes de pago.',
            'settings' => [
                'nombre_planta' => ['label' => 'Nombre de la empresa', 'type' => 'string', 'default' => 'Ecolácteos Huata S.A.C.', 'help' => 'Aparece en los comprobantes y boletas.'],
                'ruc_planta' => ['label' => 'RUC', 'type' => 'string', 'default' => '', 'help' => 'Se imprime en los comprobantes de pago.', 'placeholder' => '20123456789'],
                'direccion_planta' => ['label' => 'Dirección', 'type' => 'text', 'default' => 'Av. Principal S/N, Huata, Ancash, Perú', 'help' => 'Ubicación que ven los clientes en la web.'],
                'telefono_planta' => ['label' => 'Teléfono', 'type' => 'string', 'default' => '+51 43 123456', 'help' => 'Número de contacto que ven los clientes.', 'placeholder' => '+51 999 999 999'],
                'whatsapp_planta' => ['label' => 'WhatsApp', 'type' => 'string', 'default' => '', 'help' => 'Si lo completas, la web muestra un botón para escribir por WhatsApp.', 'placeholder' => '+51 999 999 999'],
                'email_planta' => ['label' => 'Correo de contacto', 'type' => 'string', 'default' => 'info@ecolacteoshuata.com', 'help' => 'Correo que ven los clientes en la web.', 'placeholder' => 'contacto@empresa.com'],
                'horario_atencion' => ['label' => 'Horario de atención', 'type' => 'string', 'default' => 'Lunes a Sábado', 'help' => 'Se muestra junto al teléfono.', 'placeholder' => 'Lunes a Sábado, 7:00 a 17:00'],
                'facebook_url' => ['label' => 'Facebook', 'type' => 'string', 'default' => '', 'help' => 'Enlace a la página de Facebook (opcional).', 'placeholder' => 'https://facebook.com/...'],
                'instagram_url' => ['label' => 'Instagram', 'type' => 'string', 'default' => '', 'help' => 'Enlace a la cuenta de Instagram (opcional).', 'placeholder' => 'https://instagram.com/...'],
            ],
        ],
        'acopio' => [
            'title' => 'Acopio y pagos',
            'icon' => '🥛',
            'description' => 'Reglas que usan los acopiadores al registrar leche y el cálculo de las liquidaciones a productores.',
            'settings' => [
                'precio_litro_leche' => ['label' => 'Precio por litro de leche', 'type' => 'number', 'default' => '1.70', 'help' => 'Se aplica a cada nueva entrega de leche. Los acopiadores no pueden cambiarlo.', 'prefix' => 'S/'],
                'litros_maximos_entrega' => ['label' => 'Máximo de litros por entrega', 'type' => 'number', 'default' => '10000', 'help' => 'Evita errores de digitación al registrar una entrega.', 'suffix' => 'L'],
                'dias_pago_semanal' => ['label' => 'Día de pago a productores', 'type' => 'select', 'default' => 'Viernes', 'help' => 'Se informa a los productores en “Mis Pagos”.', 'options' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']],
                'bono_volumen_minimo_litros' => ['label' => 'Litros mínimos para bono por volumen', 'type' => 'number', 'default' => '500', 'help' => 'Si el productor entrega más de estos litros en el período, gana un bono del 3%.', 'suffix' => 'L'],
                'bono_calidad_minimo_score' => ['label' => 'Entregas aprobadas para bono por calidad', 'type' => 'number', 'default' => '90', 'help' => 'Porcentaje de entregas con calidad aprobada que da derecho a un bono del 5%.', 'suffix' => '%'],
            ],
        ],
        'calidad' => [
            'title' => 'Control de calidad',
            'icon' => '🧪',
            'description' => 'Límites que usa el laboratorio al analizar cada entrega.',
            'settings' => [
                'tolerancia_agua_pct' => ['label' => 'Agua añadida máxima permitida', 'type' => 'number', 'default' => '5', 'help' => 'Si un análisis supera este porcentaje, la entrega se rechaza por exceso de agua.', 'suffix' => '%'],
            ],
        ],
    ];

    protected const CACHE_KEY = 'plant_config.values';

    protected static function booted(): void
    {
        static::saved(fn () => app()->forgetInstance(self::CACHE_KEY));
        static::deleted(fn () => app()->forgetInstance(self::CACHE_KEY));
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Todos los ajustes guardados, leídos una sola vez por petición.
     *
     * @return array<string, array{value: ?string, type: string}>
     */
    protected static function allValues(): array
    {
        if (! app()->bound(self::CACHE_KEY)) {
            app()->instance(self::CACHE_KEY, self::query()->get(['key', 'value', 'value_type'])
                ->mapWithKeys(fn (self $c) => [$c->key => ['value' => $c->value, 'type' => $c->value_type]])
                ->all());
        }

        return app(self::CACHE_KEY);
    }

    public static function getValue(string $key, $default = null)
    {
        $config = static::allValues()[$key] ?? null;
        if (! $config) {
            return $default;
        }

        return self::castValue($config['value'], $config['type']);
    }

    /**
     * Datos de la empresa para la web pública y los comprobantes.
     *
     * @return array<string, string>
     */
    public static function site(): array
    {
        return collect(self::SETTINGS['empresa']['settings'])
            ->map(fn (array $setting, string $key) => (string) (static::allValues()[$key]['value'] ?? $setting['default']))
            ->all();
    }

    public static function castValue(?string $value, string $type): mixed
    {
        return match ($type) {
            'number' => (float) $value,
            'boolean' => (bool) $value,
            'date' => $value ? new Carbon($value) : null,
            default => $value,
        };
    }

    public function getCastedValueAttribute(): mixed
    {
        return self::castValue($this->value, $this->value_type);
    }
}
