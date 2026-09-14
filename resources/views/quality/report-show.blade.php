@extends('layouts.app')

@section('title', 'Detalle Reporte Calidad - VACA SYS')
@section('page-title', 'Reporte de Análisis #' . ($report->id ?? ''))
@section('page-subtitle', 'Resultados detallados del análisis LACTOMAT')

@section('top-actions')
    <a href="{{ route('quality.reports') }}" class="btn btn-ghost">← Volver a Reportes</a>
    <a href="{{ route('quality.report-create') }}" class="btn btn-primary">➕ Nuevo Análisis</a>
@endsection

@section('content')
@if(isset($report))
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-icon-wrap green">🧪</div>
        <div class="stat-label">Código Muestra</div>
        <div class="stat-value green" style="font-size:22px">{{ $report->sample_code ?? 'N/A' }}</div>
    </div>
    <div class="stat-card {{ $report->result === 'aprobado' ? 'green' : ($report->result === 'rechazado' ? 'red' : 'amber') }}">
        <div class="stat-icon-wrap {{ $report->result === 'aprobado' ? 'green' : ($report->result === 'rechazado' ? 'red' : 'amber') }}">
            {{ $report->result === 'aprobado' ? '✅' : ($report->result === 'rechazado' ? '❌' : '👁️') }}
        </div>
        <div class="stat-label">Resultado</div>
        <div>
            <span class="badge {{ $report->result === 'aprobado' ? 'badge-green' : ($report->result === 'rechazado' ? 'badge-red' : ($report->result === 'aceptable' ? 'badge-blue' : 'badge-amber')) }}" style="font-size:15px;padding:8px 16px">
                {{ strtoupper($report->result) }}
            </span>
        </div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">📊</div>
        <div class="stat-label">Puntuación de Calidad</div>
        <div class="stat-value blue">{{ $report->qualityScore() ?? 0 }}%</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon-wrap purple">📅</div>
        <div class="stat-label">Fecha de Análisis</div>
        <div class="stat-value purple" style="font-size:18px">{{ $report->created_at?->format('d/m/Y') }}</div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">👨‍🌾 Información del Productor</div>
        </div>
        <div class="panel-body">
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Nombre</div>
                    <div style="font-weight:700;font-size:15px">{{ $report->milkDelivery?->producer?->user?->fullname ?? 'N/A' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Código</div>
                    <div style="font-weight:700">{{ $report->milkDelivery?->producer?->code ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Teléfono</div>
                    <div>{{ $report->milkDelivery?->producer?->user?->phone ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Ubicación</div>
                    <div>{{ $report->milkDelivery?->producer?->location ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🚛 Entrega Asociada</div>
        </div>
        <div class="panel-body">
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Entrega #</div>
                    <div style="font-weight:800;color:#2563eb;font-size:18px">#{{ $report->milk_delivery_id }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Fecha Entrega</div>
                    <div style="font-weight:700">{{ $report->milkDelivery?->delivery_date?->format('d/m/Y') ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Litros</div>
                    <div style="font-weight:800;color:#059669;font-size:20px">{{ number_format($report->milkDelivery?->liters ?? 0, 2) }} L</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Acopiador</div>
                    <div style="font-weight:600">{{ $report->milkDelivery?->collector?->fullname ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Temperatura</div>
                    <div>{{ $report->milkDelivery?->temperature ?? '-' }} °C</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Estado Entrega</div>
                    <span class="badge {{ $report->milkDelivery?->status === 'aceptado' ? 'badge-green' : ($report->milkDelivery?->status === 'rechazado' ? 'badge-red' : 'badge-amber') }}">
                        {{ $report->milkDelivery?->status ?? '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📊 Parámetros LACTOMAT (%)</div>
    </div>
    <div class="panel-body">
        <div class="grid-3" style="margin-bottom:24px">
            <div style="background:#f0fdf4;border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:11px;color:#065f46;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Grasa</div>
                <div style="font-size:28px;font-weight:900;color:#059669;margin-top:4px">{{ $report->grasa_pct ?? 0 }}%</div>
            </div>
            <div style="background:#eff6ff;border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:11px;color:#1e40af;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Proteína</div>
                <div style="font-size:28px;font-weight:900;color:#2563eb;margin-top:4px">{{ $report->proteina_pct ?? 0 }}%</div>
            </div>
            <div style="background:#fef3c7;border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:11px;color:#92400e;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Lactosa</div>
                <div style="font-size:28px;font-weight:900;color:#d97706;margin-top:4px">{{ $report->lactosa_pct ?? 0 }}%</div>
            </div>
            <div style="background:#ede9fe;border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:11px;color:#5b21b6;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Sólidos No Grasos</div>
                <div style="font-size:28px;font-weight:900;color:#7c3aed;margin-top:4px">{{ $report->solidos_no_grasos_pct ?? 0 }}%</div>
            </div>
            <div style="background:#cffafe;border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:11px;color:#155e75;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Total Sólidos</div>
                <div style="font-size:28px;font-weight:900;color:#0891b2;margin-top:4px">{{ $report->total_solidos_pct ?? 0 }}%</div>
            </div>
            <div style="background:#fee2e2;border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:11px;color:#991b1b;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Agua Añadida</div>
                <div style="font-size:28px;font-weight:900;color:#dc2626;margin-top:4px">{{ $report->agua_aniadida_pct ?? 0 }}%</div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🔬 Parámetros Físico-Químicos</div>
        </div>
        <div class="panel-body">
            <div class="form-grid">
                <div style="background:#f8fafc;border-radius:10px;padding:12px 16px">
                    <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase">pH</div>
                    <div style="font-weight:800;font-size:18px;margin-top:4px">{{ $report->ph ?? '-' }}</div>
                </div>
                <div style="background:#f8fafc;border-radius:10px;padding:12px 16px">
                    <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase">Pto. Congelación</div>
                    <div style="font-weight:800;font-size:18px;margin-top:4px">{{ $report->punto_congelacion ?? '-' }} °C</div>
                </div>
                <div style="background:#f8fafc;border-radius:10px;padding:12px 16px">
                    <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase">Acidez Dornic</div>
                    <div style="font-weight:800;font-size:18px;margin-top:4px">{{ $report->acidez_dornic ?? '-' }} °D</div>
                </div>
                <div style="background:#f8fafc;border-radius:10px;padding:12px 16px">
                    <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase">Densidad</div>
                    <div style="font-weight:800;font-size:18px;margin-top:4px">{{ $report->densidad ?? '-' }} g/L</div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📝 Detalles del Resultado</div>
        </div>
        <div class="panel-body">
            <div style="margin-bottom:18px">
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;margin-bottom:6px">Motivo Rechazo</div>
                <div>
                    <span class="badge {{ $report->rejection_reason && $report->rejection_reason !== 'ninguno' ? 'badge-red' : 'badge-gray' }}">
                        {{ $report->rejection_reason ? ucfirst(str_replace('_', ' ', $report->rejection_reason)) : 'Ninguno' }}
                    </span>
                </div>
            </div>
            <div style="margin-bottom:18px">
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;margin-bottom:6px">Analista</div>
                <div style="font-weight:700">{{ $report->analyst?->fullname ?? 'Sistema' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;margin-bottom:6px">Observaciones</div>
                <div style="background:#f8fafc;padding:12px 16px;border-radius:10px;border-left:4px solid #10b981;font-size:14px">
                    {{ $report->observations ?? 'Sin observaciones.' }}
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-body">
        <div class="empty">
            <div class="empty-icon">📋</div>
            <h3>Reporte no encontrado</h3>
            <p>El reporte de calidad solicitado no existe o fue eliminado.</p>
            <a href="{{ route('quality.reports') }}" class="btn btn-primary" style="margin-top:16px">← Volver a Reportes</a>
        </div>
    </div>
</div>
@endif
@endsection
