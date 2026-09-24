@extends('layouts.app')

@section('page-title', $collector->fullname)
@section('page-subtitle', 'Detalle de acopiador')

@section('top-actions')
    <a href="{{ route('admin.collectors') }}" class="btn btn-ghost">← Volver a Acopiadores</a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Litros Hoy</div>
        <div class="stat-value green">{{ number_format($todayDeliveries->sum('liters'), 1) }} L</div>
        <div class="stat-icon-wrap green">🥛</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Entregas Hoy</div>
        <div class="stat-value blue">{{ $todayDeliveries->count() }}</div>
        <div class="stat-icon-wrap blue">📋</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Productores Asignados</div>
        <div class="stat-value purple">{{ $assignedProducers->count() }}</div>
        <div class="stat-icon-wrap purple">👨‍🌾</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚛 Entregas de Hoy</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Hora</th><th>Productor</th><th>Litros</th><th>Estado</th></tr></thead>
            <tbody>
                @forelse($todayDeliveries as $d)
                <tr>
                    <td>{{ $d->delivery_time?->format('H:i') ?? '-' }}</td>
                    <td>{{ $d->producer?->user?->fullname ?? 'N/A' }}</td>
                    <td style="font-weight:700;color:#059669">{{ number_format($d->liters, 2) }} L</td>
                    <td><span class="badge badge-blue">{{ ucfirst($d->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="empty"><div class="empty-icon">🚛</div><h3>Sin entregas hoy</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">👨‍🌾 Productores Asignados</div>
        <span class="badge {{ $explicitAssignedIds->isNotEmpty() ? 'badge-amber' : 'badge-gray' }}">
            {{ $explicitAssignedIds->isNotEmpty() ? 'Asignación individual' : 'Comunidad: '.($collector->comunidad ?? 'Sin asignar') }}
        </span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Código</th><th>Productor</th><th>Comunidad</th></tr></thead>
            <tbody>
                @forelse($assignedProducers as $p)
                <tr>
                    <td><code class="badge badge-blue">{{ $p->code }}</code></td>
                    <td>{{ $p->user?->fullname ?? 'N/A' }}</td>
                    <td>{{ $p->comunidad ?? $p->zone }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="empty"><div class="empty-icon">👨‍🌾</div><h3>Sin productores asignados</h3><p>Asígnalos desde el panel "Asignar Productores" más abajo.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel" id="asignacion">
    <div class="panel-header">
        <div class="panel-title">⚙️ Asignar Productores</div>
        <span class="badge badge-green"><span id="assignCount">{{ $assignedIds->count() }}</span>&nbsp;seleccionado(s)</span>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.collectors-assign', $collector) }}" id="assignForm">
            @csrf
            @method('PUT')

            <div class="assign-steps">
                <div class="assign-step">
                    <div class="assign-step-num">1</div>
                    <div class="assign-step-body">
                        <label class="form-label" for="assignComunidad">Comunidad del acopiador (opcional)</label>
                        <select name="comunidad" id="assignComunidad" class="form-select">
                            <option value="">Sin comunidad asignada</option>
                            @foreach($allComunidades as $comunidad)
                            <option value="{{ $comunidad }}" {{ $collector->comunidad == $comunidad ? 'selected' : '' }}>{{ $comunidad }}</option>
                            @endforeach
                        </select>
                        <small class="form-hint">Al elegir una comunidad se marcan automáticamente sus productores.</small>
                    </div>
                </div>
                <div class="assign-step">
                    <div class="assign-step-num">2</div>
                    <div class="assign-step-body">
                        <div class="form-label">Marca todos los productores que atenderá este acopiador</div>
                        <small class="form-hint">Puedes marcar varios, de cualquier comunidad. Los marcados son los asignados.</small>
                    </div>
                </div>
            </div>

            <div class="assign-toolbar">
                <input type="search" id="assignSearch" class="form-input" placeholder="🔍 Buscar por nombre, código o comunidad...">
                <select id="assignComunidadFilter" class="form-select" aria-label="Mostrar solo una comunidad">
                    <option value="">Ver todas las comunidades</option>
                    @foreach($allComunidades as $comunidad)
                    <option value="{{ $comunidad }}">Ver solo: {{ $comunidad }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-ghost btn-sm" id="assignSelectVisible">☑️ Marcar visibles</button>
                <button type="button" class="btn btn-ghost btn-sm" id="assignClear">✖ Quitar todos</button>
            </div>

            <div class="assign-list">
                @forelse($allActiveProducers as $p)
                <label class="assign-item" data-comunidad="{{ $p->comunidad }}"
                       data-search="{{ \Illuminate\Support\Str::lower(($p->user?->fullname ?? '').' '.$p->code.' '.$p->comunidad) }}">
                    <input type="checkbox" name="producer_ids[]" value="{{ $p->id }}" {{ $assignedIds->contains($p->id) ? 'checked' : '' }}>
                    <span class="assign-check" aria-hidden="true"></span>
                    <span class="assign-avatar">{{ strtoupper(substr($p->user?->name ?? 'P', 0, 1).substr($p->user?->lastname ?? '', 0, 1)) }}</span>
                    <span class="assign-info">
                        <strong>{{ $p->user?->fullname ?? 'N/A' }}</strong>
                        <small>{{ $p->code }} · {{ $p->comunidad ?? $p->zone }}</small>
                    </span>
                </label>
                @empty
                <div class="empty"><div class="empty-icon">👨‍🌾</div><h3>No hay productores activos</h3></div>
                @endforelse
                <div class="empty" id="assignNoResults" style="display:none;"><h3>Sin resultados</h3><p>Prueba con otro nombre o comunidad.</p></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Guardar Asignación</button>
            </div>
        </form>
    </div>
</div>

<style>
    .assign-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 18px; }
    .assign-step { display: flex; gap: 12px; padding: 14px; border: 1px solid var(--border); border-radius: 12px; background: #fbfdfc; }
    .assign-step-num { flex: none; width: 28px; height: 28px; border-radius: 50%; background: var(--primary); color: #fff; font-weight: 700; display: grid; place-items: center; font-size: 13px; }
    .assign-step-body { flex: 1; min-width: 0; }
    .assign-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 12px; }
    .assign-toolbar .form-input { flex: 1 1 240px; }
    .assign-toolbar .form-select { flex: 0 1 240px; }
    .assign-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; max-height: 420px; overflow-y: auto; padding: 2px; }
    .assign-item { position: relative; display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 12px; background: #fff; cursor: pointer; transition: border-color .15s, background .15s, box-shadow .15s; }
    .assign-item:hover { border-color: var(--primary-light); }
    .assign-item input { position: absolute; opacity: 0; pointer-events: none; }
    .assign-check { flex: none; width: 22px; height: 22px; border-radius: 7px; border: 2px solid #c5d6cc; display: grid; place-items: center; transition: all .15s; }
    .assign-item input:checked ~ .assign-check { background: var(--primary); border-color: var(--primary); }
    .assign-item input:checked ~ .assign-check::after { content: ''; width: 6px; height: 11px; border: solid #fff; border-width: 0 2.5px 2.5px 0; transform: rotate(45deg) translate(-1px, -1px); }
    .assign-item:has(input:checked) { border-color: var(--primary); background: #effaf4; box-shadow: 0 0 0 3px rgba(11, 170, 114, .12); }
    .assign-item input:focus-visible ~ .assign-check { outline: 2px solid var(--primary-light); outline-offset: 2px; }
    .assign-avatar { flex: none; width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #F2C94C, #D6A927); color: #054529; font-weight: 800; font-size: 13px; display: grid; place-items: center; }
    .assign-info { display: flex; flex-direction: column; min-width: 0; }
    .assign-info strong { font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .assign-info small { font-size: 12px; color: var(--text-light); }
</style>

<script>
(function () {
    const items = Array.from(document.querySelectorAll('.assign-item'));
    const count = document.getElementById('assignCount');
    const search = document.getElementById('assignSearch');
    const filter = document.getElementById('assignComunidadFilter');
    const comunidad = document.getElementById('assignComunidad');
    const noResults = document.getElementById('assignNoResults');

    const checkbox = (item) => item.querySelector('input[type=checkbox]');
    const updateCount = () => { count.textContent = items.filter((item) => checkbox(item).checked).length; };
    const applyFilters = () => {
        const term = search.value.trim().toLowerCase();
        let visible = 0;
        items.forEach((item) => {
            const show = (!filter.value || item.dataset.comunidad === filter.value)
                && (!term || item.dataset.search.includes(term));
            item.style.display = show ? '' : 'none';
            if (show) { visible++; }
        });
        noResults.style.display = visible === 0 && items.length ? '' : 'none';
    };

    items.forEach((item) => checkbox(item).addEventListener('change', updateCount));
    search.addEventListener('input', applyFilters);
    filter.addEventListener('change', applyFilters);

    document.getElementById('assignSelectVisible').addEventListener('click', () => {
        items.filter((item) => item.style.display !== 'none').forEach((item) => { checkbox(item).checked = true; });
        updateCount();
    });
    document.getElementById('assignClear').addEventListener('click', () => {
        items.forEach((item) => { checkbox(item).checked = false; });
        updateCount();
    });

    comunidad.addEventListener('change', () => {
        if (!comunidad.value) { return; }
        items.forEach((item) => { checkbox(item).checked = item.dataset.comunidad === comunidad.value; });
        updateCount();
    });
})();
</script>
@endsection
