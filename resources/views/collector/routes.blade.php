@extends('layouts.app')

@section('title', 'Mis Rutas - VACA SYS')
@section('page-title', 'Mis Rutas de Recolección')
@section('page-subtitle', 'Rutas asignadas y paradas programadas')

@section('content')
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">🗺️</div>
        <div class="stat-label">Total Rutas</div>
        <div class="stat-value blue">{{ count($routes ?? []) }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">⏳</div>
        <div class="stat-label">Pendientes</div>
        <div class="stat-value amber">{{ ($routes ?? collect())->where('status','pendiente')->count() }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon-wrap green">✅</div>
        <div class="stat-label">Completadas</div>
        <div class="stat-value green">{{ ($routes ?? collect())->where('status','completado')->count() }}</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-icon-wrap cyan">📍</div>
        <div class="stat-label">Paradas Totales</div>
        <div class="stat-value" style="color:#0891b2">
            {{ collect($routes ?? [])->sum(fn($r) => count($r->stops ?? [])) }}
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🗺️ Lista de Rutas Asignadas</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Ruta</th>
                <th>Día</th>
                <th>Paradas</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr></thead>
            <tbody>
                @forelse($routes ?? [] as $r)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#cffafe,#a5f3fc);display:flex;align-items:center;justify-content:center;font-size:18px">🗺️</div>
                            <div>
                                <div style="font-weight:700;font-size:14.5px">{{ $r->name ?? 'Ruta ' . $r->id }}</div>
                                <div style="font-size:11px;color:#94a3b8">Código: RUT-{{ str_pad($r->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:600">{{ $r->day ?? '-' }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-weight:800;color:#0891b2;font-size:17px">{{ count($r->stops ?? []) }}</span>
                            <div style="flex:1;min-width:80px">
                                <div class="progress-wrap" style="height:6px">
                                    <div class="progress blue" style="width:{{ count($r->stops ?? []) > 0 ? ((collect($r->stops ?? [])->filter(fn($s) => $s->status === 'completado')->count() / count($r->stops ?? [])) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ ($r->status ?? 'pendiente') === 'completado' ? 'badge-green' : (($r->status ?? 'pendiente') === 'en_curso' ? 'badge-blue' : 'badge-amber') }}">
                            {{ ($r->status ?? 'pendiente') === 'completado' ? '✅ Completada' : (($r->status ?? 'pendiente') === 'en_curso' ? '🚛 En Curso' : '⏳ Pendiente' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('collector.route-show', $r->id) }}" class="btn btn-sm btn-primary">👁️ Gestionar</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="empty"><div class="empty-icon">🗺️</div><h3>Sin rutas asignadas</h3><p>No tienes rutas de recolección asignadas.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($routes ?? [], 'links'))
    <div style="padding:20px">
        {{ $routes->links() }}
    </div>
    @endif
</div>
@endsection
