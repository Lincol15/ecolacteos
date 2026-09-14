@extends('layouts.app')

@section('page-title', 'Configuración del Sistema')
@section('page-subtitle', 'Parámetros y variables globales de la planta')

@section('top-actions')
    <span class="badge badge-blue">Total: {{ $configs->count() }} variables</span>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Precios Base Leche</div>
        <div class="stat-value green">{{ $configs->where('key', 'like', '%precio%')->count() + $configs->where('key', 'like', '%price%')->count() }}</div>
        <div class="stat-icon-wrap green">💲</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Umbrales Calidad</div>
        <div class="stat-value amber">{{ $configs->where('key', 'like', '%calidad%')->count() + $configs->where('key', 'like', '%quality%')->count() }}</div>
        <div class="stat-icon-wrap amber">🧪</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Parámetros Planta</div>
        <div class="stat-value blue">{{ $configs->where('key', 'like', '%planta%')->count() + $configs->where('key', 'like', '%plant%')->count() }}</div>
        <div class="stat-icon-wrap blue">🏭</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Impuestos / Tasas</div>
        <div class="stat-value purple">{{ $configs->where('key', 'like', '%tax%')->count() + $configs->where('key', 'like', '%igv%')->count() }}</div>
        <div class="stat-icon-wrap purple">📊</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">➕ Agregar Nueva Variable de Configuración</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.config-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Clave (Key) *</label>
                    <input type="text" name="key" value="{{ old('key') }}" required class="form-input" placeholder="Ej: precio_leche_base" style="text-transform: lowercase;">
                </div>
                <div class="form-group">
                    <label class="form-label">Etiqueta *</label>
                    <input type="text" name="label" value="{{ old('label') }}" required class="form-input" placeholder="Ej: Precio Base de Leche (Litro)">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Valor *</label>
                    <select name="value_type" required class="form-select" id="valueTypeSelect">
                        <option value="string" {{ old('value_type') == 'string' ? 'selected' : '' }}>🔤 Texto Corto</option>
                        <option value="number" {{ old('value_type') == 'number' ? 'selected' : '' }}>🔢 Número</option>
                        <option value="boolean" {{ old('value_type') == 'boolean' ? 'selected' : '' }}>✅ Sí / No (Booleano)</option>
                        <option value="date" {{ old('value_type') == 'date' ? 'selected' : '' }}>📅 Fecha</option>
                        <option value="text" {{ old('value_type') == 'text' ? 'selected' : '' }}>📝 Texto Largo</option>
                    </select>
                </div>
                <div class="form-group" id="valueField">
                    <label class="form-label">Valor *</label>
                    <input type="text" name="value" id="valueInput" value="{{ old('value') }}" required class="form-input">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-textarea" style="min-height:70px;" placeholder="Explicación del uso de esta variable...">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">➕ Agregar Variable</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">⚙️ Variables de Configuración Existentes</div>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Etiqueta</th>
                        <th>Tipo</th>
                        <th>Valor Actual</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($configs as $config)
                    <tr>
                        <form method="POST" action="{{ route('admin.config-update', $config) }}">
                            @csrf
                            @method('PUT')
                            <td style="vertical-align: middle;">
                                <input type="text" name="key" value="{{ $config->key }}" required class="form-input" style="font-family:monospace; font-size:12px;">
                            </td>
                            <td style="vertical-align: middle;">
                                <input type="text" name="label" value="{{ $config->label }}" required class="form-input">
                            </td>
                            <td style="vertical-align: middle;">
                                <select name="value_type" class="form-select form-value-type" style="min-width:130px;">
                                    <option value="string" {{ $config->value_type == 'string' ? 'selected' : '' }}>🔤 Texto</option>
                                    <option value="number" {{ $config->value_type == 'number' ? 'selected' : '' }}>🔢 Número</option>
                                    <option value="boolean" {{ $config->value_type == 'boolean' ? 'selected' : '' }}>✅ Bool</option>
                                    <option value="date" {{ $config->value_type == 'date' ? 'selected' : '' }}>📅 Fecha</option>
                                    <option value="text" {{ $config->value_type == 'text' ? 'selected' : '' }}>📝 Largo</option>
                                </select>
                            </td>
                            <td style="vertical-align: middle;">
                                @if($config->value_type == 'boolean')
                                    <select name="value" class="form-select">
                                        <option value="1" {{ $config->value == '1' || $config->value === true ? 'selected' : '' }}>✅ Sí</option>
                                        <option value="0" {{ $config->value == '0' || $config->value === false ? 'selected' : '' }}>❌ No</option>
                                    </select>
                                @elseif($config->value_type == 'text')
                                    <textarea name="value" class="form-textarea" style="min-height:50px;">{{ $config->value }}</textarea>
                                @elseif($config->value_type == 'date')
                                    <input type="date" name="value" value="{{ $config->value ? \Carbon\Carbon::parse($config->value)->format('Y-m-d') : '' }}" class="form-input">
                                @else
                                    <input type="{{ $config->value_type == 'number' ? 'number' : 'text' }}" step="any" name="value" value="{{ $config->value }}" class="form-input">
                                @endif
                            </td>
                            <td style="vertical-align: middle; max-width:220px;">
                                <input type="text" name="description" value="{{ $config->description }}" class="form-input" style="font-size:12px;">
                            </td>
                            <td style="vertical-align: middle;">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Guardar</button>
                            </td>
                        </form>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty">
                                <div class="empty-icon">⚙️</div>
                                <h3>No hay variables de configuración</h3>
                                <p>Agrega tu primera variable usando el formulario superior.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('valueTypeSelect').addEventListener('change', function() {
    const v = this.value;
    const field = document.getElementById('valueField');
    const oldInput = document.getElementById('valueInput');
    let newEl;
    if (v === 'boolean') {
        newEl = document.createElement('select');
        newEl.name = 'value';
        newEl.id = 'valueInput';
        newEl.className = 'form-select';
        newEl.required = true;
        newEl.innerHTML = '<option value="1">✅ Sí</option><option value="0">❌ No</option>';
    } else if (v === 'date') {
        newEl = document.createElement('input');
        newEl.type = 'date';
        newEl.name = 'value';
        newEl.id = 'valueInput';
        newEl.className = 'form-input';
        newEl.required = true;
    } else if (v === 'text') {
        newEl = document.createElement('textarea');
        newEl.name = 'value';
        newEl.id = 'valueInput';
        newEl.className = 'form-textarea';
        newEl.required = true;
        newEl.rows = 3;
    } else if (v === 'number') {
        newEl = document.createElement('input');
        newEl.type = 'number';
        newEl.step = 'any';
        newEl.name = 'value';
        newEl.id = 'valueInput';
        newEl.className = 'form-input';
        newEl.required = true;
    } else {
        newEl = document.createElement('input');
        newEl.type = 'text';
        newEl.name = 'value';
        newEl.id = 'valueInput';
        newEl.className = 'form-input';
        newEl.required = true;
    }
    field.innerHTML = '<label class="form-label">Valor *</label>';
    field.appendChild(newEl);
});
</script>
@endsection
