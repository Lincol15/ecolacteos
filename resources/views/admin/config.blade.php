@extends('layouts.app')

@section('page-title', 'Configuración del Sistema')
@section('page-subtitle', 'Datos de la empresa, reglas de acopio y pagos, y límites de calidad para toda la web')

@section('content')
<nav class="cfg-nav" aria-label="Secciones de configuración">
    @foreach($groups as $key => $group)
    <a href="#{{ $key }}" class="cfg-chip">{{ $group['icon'] }} {{ $group['title'] }}</a>
    @endforeach
    <a href="#avanzado" class="cfg-chip">🛠️ Avanzado</a>
</nav>

@foreach($groups as $groupKey => $group)
<section class="panel cfg-group" id="{{ $groupKey }}">
    <div class="panel-header" style="flex-wrap:wrap; gap:10px;">
        <div class="cfg-head">
            <span class="cfg-icon">{{ $group['icon'] }}</span>
            <div>
                <div class="panel-title">{{ $group['title'] }}</div>
                <div class="cfg-desc">{{ $group['description'] }}</div>
            </div>
        </div>
        @if($group['updated_at'])
        <span class="cfg-updated">Actualizado {{ $group['updated_at']->diffForHumans() }}</span>
        @endif
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.config-save-group', $groupKey) }}" class="{{ $groupKey === 'empresa' ? 'cfg-with-preview' : '' }}" data-cfg-form="{{ $groupKey }}">
            @csrf
            @method('PUT')
            <div class="cfg-fields">
                @foreach($group['settings'] as $key => $setting)
                @php($value = old("settings.$key", $setting['value']))
                <div class="cfg-field {{ $setting['type'] === 'text' ? 'wide' : '' }}">
                    <label class="form-label" for="cfg_{{ $key }}">{{ $setting['label'] }}</label>
                    <div class="cfg-control">
                        @isset($setting['prefix'])<span class="cfg-affix">{{ $setting['prefix'] }}</span>@endisset
                        @if($setting['type'] === 'text')
                            <textarea name="settings[{{ $key }}]" id="cfg_{{ $key }}" rows="2" class="form-textarea" data-preview="{{ $key }}" placeholder="{{ $setting['placeholder'] ?? '' }}">{{ $value }}</textarea>
                        @elseif($setting['type'] === 'select')
                            <select name="settings[{{ $key }}]" id="cfg_{{ $key }}" class="form-select">
                                @foreach($setting['options'] as $option)
                                <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $setting['type'] === 'number' ? 'number' : 'text' }}" @if($setting['type'] === 'number') step="any" min="0" @endif
                                   name="settings[{{ $key }}]" id="cfg_{{ $key }}" value="{{ $value }}" class="form-input"
                                   data-preview="{{ $key }}" placeholder="{{ $setting['placeholder'] ?? '' }}">
                        @endif
                        @isset($setting['suffix'])<span class="cfg-affix">{{ $setting['suffix'] }}</span>@endisset
                    </div>
                    @error("settings.$key")
                        <small class="cfg-error">{{ $message }}</small>
                    @else
                        <small class="form-hint">{{ $setting['help'] }}</small>
                    @enderror
                </div>
                @endforeach
            </div>

            @if($groupKey === 'empresa')
            <aside class="cfg-preview" aria-label="Vista previa en la web">
                <div class="cfg-preview-label">Así se verá en la web</div>
                <div class="cfg-preview-card">
                    <div class="cfg-preview-brand" data-show="nombre_planta"></div>
                    <ul>
                        <li>📍 <span data-show="direccion_planta"></span></li>
                        <li>📞 <span data-show="telefono_planta"></span></li>
                        <li>✉️ <span data-show="email_planta"></span></li>
                        <li>🕒 <span data-show="horario_atencion"></span></li>
                        <li data-optional="whatsapp_planta">💬 WhatsApp: <span data-show="whatsapp_planta"></span></li>
                    </ul>
                </div>
            </aside>
            @endif

            <div class="form-actions cfg-actions">
                <button type="submit" class="btn btn-primary">💾 Guardar {{ mb_strtolower($group['title']) }}</button>
            </div>
        </form>
    </div>
</section>
@endforeach

<details class="panel cfg-advanced" id="avanzado" @if($errors->hasAny(['key', 'label', 'value', 'value_type'])) open @endif>
    <summary class="panel-header">
        <div class="cfg-head">
            <span class="cfg-icon">🛠️</span>
            <div>
                <div class="panel-title">Avanzado: variables adicionales</div>
                <div class="cfg-desc">Solo para uso técnico. Estas variables no aparecen en la web a menos que el sistema las use.</div>
            </div>
        </div>
        <span class="badge badge-gray">{{ $customConfigs->count() }}</span>
    </summary>
    <div class="panel-body">
        @if($customConfigs->isNotEmpty())
        <div class="table-wrap" style="margin-bottom:20px;">
            <table>
                <thead><tr><th>Variable</th><th>Valor</th><th>Descripción</th><th></th></tr></thead>
                <tbody>
                    @foreach($customConfigs as $config)
                    <tr>
                        <td>
                            <strong>{{ $config->label }}</strong>
                            <div style="font-family:monospace; font-size:11.5px; color:var(--text-light)">{{ $config->key }}</div>
                        </td>
                        <td colspan="3">
                            <form method="POST" action="{{ route('admin.config-update', $config) }}" style="display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.3fr) auto; gap:8px; align-items:center;">
                                @csrf
                                @method('PUT')
                                @if($config->value_type === 'boolean')
                                <select name="value" class="form-select">
                                    <option value="1" @selected($config->value == '1')>Sí</option>
                                    <option value="0" @selected($config->value == '0')>No</option>
                                </select>
                                @else
                                <input type="{{ match($config->value_type) { 'number' => 'number', 'date' => 'date', default => 'text' } }}" step="any" name="value" value="{{ $config->value }}" class="form-input" required>
                                @endif
                                <input type="text" name="description" value="{{ $config->description }}" class="form-input" placeholder="Descripción">
                                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <h4 style="font-size:14px; margin-bottom:10px;">➕ Nueva variable</h4>
        <form method="POST" action="{{ route('admin.config-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Clave *</label>
                    <input type="text" name="key" value="{{ old('key') }}" required class="form-input" placeholder="ej: dias_credito_clientes" style="font-family:monospace">
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="label" value="{{ old('label') }}" required class="form-input" placeholder="Ej: Días de crédito a clientes">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo *</label>
                    <select name="value_type" required class="form-select">
                        @foreach(\App\Models\PlantConfig::VALUE_TYPES as $type => $label)
                        <option value="{{ $type }}" @selected(old('value_type') === $type)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Valor *</label>
                    <input type="text" name="value" value="{{ old('value') }}" required class="form-input">
                </div>
                <div class="form-group" style="grid-column:1 / -1;">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="description" value="{{ old('description') }}" class="form-input" placeholder="Para qué sirve esta variable">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-ghost">➕ Agregar variable</button>
            </div>
        </form>
    </div>
</details>

<style>
    .cfg-nav { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
    .cfg-chip { padding: 8px 14px; border-radius: 999px; background: #fff; border: 1px solid var(--border); font-size: 13px; font-weight: 600; color: var(--text); text-decoration: none; }
    .cfg-chip:hover { border-color: var(--primary-light); color: var(--primary); }
    .cfg-group { scroll-margin-top: 16px; }
    .cfg-head { display: flex; gap: 12px; align-items: center; }
    .cfg-icon { width: 42px; height: 42px; flex: none; border-radius: 12px; background: #EDF7F1; display: grid; place-items: center; font-size: 20px; }
    .cfg-desc { font-size: 13px; color: var(--text-light); margin-top: 3px; max-width: 640px; }
    .cfg-updated { font-size: 12px; color: var(--text-light); }
    .cfg-fields { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px 20px; }
    .cfg-field.wide { grid-column: 1 / -1; }
    .cfg-field .form-label { margin-bottom: 6px; }
    .cfg-control { display: flex; align-items: stretch; }
    .cfg-control > .form-input, .cfg-control > .form-select, .cfg-control > .form-textarea { flex: 1; min-width: 0; }
    .cfg-affix { display: grid; place-items: center; padding: 0 12px; background: #F2F6F4; border: 1px solid var(--border); color: var(--text-light); font-weight: 600; font-size: 13px; }
    .cfg-affix:first-child { border-radius: 10px 0 0 10px; border-right: 0; }
    .cfg-affix:last-child { border-radius: 0 10px 10px 0; border-left: 0; }
    .cfg-affix:first-child + .form-input { border-top-left-radius: 0; border-bottom-left-radius: 0; }
    .cfg-control > .form-input:not(:last-child) { border-top-right-radius: 0; border-bottom-right-radius: 0; }
    .cfg-field .form-hint { display: block; margin-top: 6px; }
    .cfg-error { display: block; margin-top: 6px; color: #B42318; font-size: 12px; font-weight: 600; }
    .cfg-with-preview { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 24px; }
    .cfg-with-preview .cfg-actions { grid-column: 1 / -1; }
    .cfg-preview { align-self: start; position: sticky; top: 16px; }
    .cfg-preview-label { font-size: 11.5px; text-transform: uppercase; letter-spacing: .6px; font-weight: 700; color: var(--text-light); margin-bottom: 8px; }
    .cfg-preview-card { border-radius: 14px; padding: 18px; background: linear-gradient(160deg, #06492F, #043521); color: #fff; }
    .cfg-preview-brand { font-family: 'Poppins', 'Inter', sans-serif; font-weight: 700; font-size: 16px; margin-bottom: 12px; color: #F2C94C; }
    .cfg-preview-card ul { list-style: none; display: grid; gap: 8px; font-size: 13px; opacity: .92; }
    .cfg-preview-card li span:empty::before { content: '—'; opacity: .6; }
    .cfg-advanced summary { cursor: pointer; list-style: none; }
    .cfg-advanced summary::-webkit-details-marker { display: none; }
    @media (max-width: 980px) { .cfg-with-preview { grid-template-columns: 1fr; } .cfg-preview { position: static; } }
</style>

<script>
(function () {
    const form = document.querySelector('[data-cfg-form="empresa"]');
    if (!form) { return; }
    const update = () => {
        form.querySelectorAll('[data-show]').forEach((el) => {
            const input = form.querySelector(`[data-preview="${el.dataset.show}"]`);
            el.textContent = input ? input.value.trim() : '';
        });
        form.querySelectorAll('[data-optional]').forEach((el) => {
            const input = form.querySelector(`[data-preview="${el.dataset.optional}"]`);
            el.style.display = input && input.value.trim() ? '' : 'none';
        });
    };
    form.addEventListener('input', update);
    update();
})();
</script>
@endsection
