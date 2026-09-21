@extends('layouts.app')

@section('title', 'Dashboard Acopiador - Ecolácteos Huata')
@section('page-title', 'Panel de Acopiador')
@section('page-subtitle', 'Comunidad: ' . (auth()->user()->comunidad ?? 'Sin asignar') . ' · Vehículo: ' . (auth()->user()->vehiculo ?? 'Sin asignar'))

@section('top-actions')
    <a href="{{ route('collector.delivery-create') }}" class="btn btn-primary">
        <span>➕</span> Registrar Entrega
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card cyan">
        <div class="stat-icon-wrap cyan">🗓️</div>
        <div class="stat-label">Litros Hoy</div>
        <div class="stat-value" style="color:#0891b2">{{ number_format($todayDeliveries ?? 0, 2) }} L</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon-wrap green">📅</div>
        <div class="stat-label">Litros Semana</div>
        <div class="stat-value green">{{ number_format($weekDeliveries ?? 0, 2) }} L</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">💲</div>
        <div class="stat-label">Monto Hoy</div>
        <div class="stat-value amber">S/ {{ number_format(($todayDeliveries ?? 0) * 3.50, 2) }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">🗺️</div>
        <div class="stat-label">Mis Rutas</div>
        <div class="stat-value blue">{{ count($myRoutes ?? []) }}</div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🗺️ Ruta de Hoy</div>
            @if(!empty($myRoute))
            <a href="{{ route('collector.route-show', $myRoute->id) }}" class="btn btn-sm btn-primary">Gestionar →</a>
            @endif
        </div>
        <div class="panel-body">
            @if(!empty($myRoute))
            <div style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);border-radius:14px;padding:18px;margin-bottom:18px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <div>
                        <div style="font-weight:800;font-size:17px;color:#1e40af">{{ $myRoute->name ?? 'Ruta del Día' }}</div>
                        <div style="font-size:12px;color:#3b82f6;font-weight:500">{{ $myRoute->day ?? 'Hoy' }} · {{ count($myRoute->stops ?? []) }} paradas</div>
                    </div>
                    <span class="badge {{ ($myRoute->status ?? 'pendiente') === 'completado' ? 'badge-green' : (($myRoute->status ?? 'pendiente') === 'en_curso' ? 'badge-blue' : 'badge-amber') }}">
                        {{ ucfirst($myRoute->status ?? 'Pendiente') }}
                    </span>
                </div>
                <div class="progress-wrap" style="height:8px;margin-top:10px">
                    <div class="progress blue" style="width:{{ count($myRoute->stops ?? []) > 0 ? (collect($myRoute->stops ?? [])->filter(fn($s) => $s->status === 'completado')->count() / count($myRoute->stops ?? [])) * 100 : 0 }}%"></div>
                </div>
            </div>

            <div>
                <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px">📍 Paradas de la Ruta</div>
                @forelse($myRoute->stops ?? [] as $stop)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9">
                    <div style="width:36px;height:36px;border-radius:10px;background:{{ $stop->status === 'completado' ? 'linear-gradient(135deg,#d1fae5,#a7f3d0)' : ($stop->status === 'en_curso' ? 'linear-gradient(135deg,#dbeafe,#bfdbfe)' : 'linear-gradient(135deg,#fef3c7,#fde68a)') }};display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">
                        {{ $stop->status === 'completado' ? '✅' : ($stop->status === 'en_curso' ? '🚛' : '📍') }}
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:600;font-size:13.5px">{{ $stop->producer?->user?->fullname ?? 'Parada ' . ($loop->iteration) }}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $stop->address ?? $stop->producer?->location ?? '-' }}</div>
                    </div>
                    <span class="badge {{ $stop->status === 'completado' ? 'badge-green' : ($stop->status === 'en_curso' ? 'badge-blue' : 'badge-gray') }}">
                        {{ ucfirst($stop->status ?? 'pendiente') }}
                    </span>
                </div>
                @empty
                <div class="empty" style="padding:30px 10px"><div class="empty-icon" style="font-size:40px">📍</div><h3 style="font-size:15px">Sin paradas programadas</h3></div>
                @endforelse
            </div>
            @else
            <div class="empty"><div class="empty-icon">🗺️</div><h3>Sin ruta asignada hoy</h3><p>No tienes una ruta programada para el día de hoy.</p></div>
            @endif
        </div>
    </div>

    <div>
        <div class="panel" style="margin-bottom:24px">
            <div class="panel-header">
                <div class="panel-title">🚛 Entregas Recientes</div>
                <a href="{{ route('collector.deliveries') }}" class="btn btn-sm btn-ghost">Ver todas →</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>Fecha</th><th>Productor</th><th>Litros</th><th>Estado</th>
                    </tr></thead>
                    <tbody>
                        @forelse($recentDeliveries ?? [] as $d)
                        <tr>
                            <td style="font-size:12px;color:#64748b;font-weight:600">{{ $d->delivery_date?->format('d/m') }}</td>
                            <td>
                                <div style="font-weight:600;font-size:13px">{{ $d->producer?->user?->fullname ?? 'N/A' }}</div>
                                <div style="font-size:11px;color:#94a3b8">{{ $d->producer?->code ?? '' }}</div>
                            </td>
                            <td style="font-weight:800;color:#059669">{{ number_format($d->liters, 2) }} L</td>
                            <td>
                                @if($d->status === 'aceptado' || $d->status === 'analizado') <span class="badge badge-green">✅ {{ $d->status }}</span>
                                @elseif($d->status === 'rechazado') <span class="badge badge-red">❌ {{ $d->status }}</span>
                                @else <span class="badge badge-amber">⏳ {{ $d->status }}</span> @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="empty" style="padding:30px 10px"><div class="empty-icon" style="font-size:40px">🚛</div><h3 style="font-size:15px">Sin entregas recientes</h3></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">🗺️ Mis Rutas Asignadas</div>
                <a href="{{ route('collector.routes') }}" class="btn btn-sm btn-ghost">Ver todas →</a>
            </div>
            <div class="panel-body" style="padding:12px 0">
                @forelse($myRoutes ?? [] as $r)
                <a href="{{ route('collector.route-show', $r->id) }}" style="display:flex;align-items:center;gap:12px;padding:12px 22px;border-bottom:1px solid #f1f5f9;text-decoration:none;color:inherit;transition:all 0.2s" onmouseover="this.style.background='#f8fafc'">
                    <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#cffafe,#a5f3fc);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🗺️</div>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:700;font-size:14px">{{ $r->name ?? 'Ruta ' . $r->id }}</div>
                        <div style="font-size:12px;color:#94a3b8">{{ $r->day ?? 'Sin día' }} · {{ count($r->stops ?? []) }} paradas</div>
                    </div>
                    <span class="badge {{ ($r->status ?? 'pendiente') === 'completado' ? 'badge-green' : 'badge-blue' }}">{{ ucfirst($r->status ?? 'Pendiente') }}</span>
                </a>
                @empty
                <div class="empty" style="padding:30px 10px"><div class="empty-icon" style="font-size:40px">🗺️</div><h3 style="font-size:15px">Sin rutas asignadas</h3></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if(!empty($notifications ?? []))
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📢 Notificaciones</div>
    </div>
    <div class="panel-body" style="padding:12px 0">
        @forelse($notifications as $n)
        <div style="padding:14px 22px;border-left:4px solid {{ $n->priority === 'urgente' ? '#ef4444' : ($n->priority === 'alta' ? '#f59e0b' : '#3b82f6') }};margin-bottom:4px;background:{{ $n->priority === 'urgente' ? '#fef2f2' : ($n->priority === 'alta' ? '#fffbeb' : '#eff6ff') }}">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
                <div style="font-weight:700;font-size:14px">{{ $n->title }}</div>
                <span class="badge {{ $n->priority === 'urgente' ? 'badge-red' : ($n->priority === 'alta' ? 'badge-amber' : 'badge-blue') }}">{{ $n->priority }}</span>
            </div>
            <div style="font-size:13px;color:#475569">{{ $n->message }}</div>
        </div>
        @empty
        @endforelse
    </div>
</div>
@endif
@endsection
