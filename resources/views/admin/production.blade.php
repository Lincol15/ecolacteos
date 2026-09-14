@extends('layouts.app')

@section('page-title', 'Producción de Planta')
@section('page-subtitle', 'Seguimiento de lotes de producción de productos lácteos')

@section('top-actions')
    <a href="{{ route('admin.production-create') }}" class="btn btn-primary">
        ➕ Nuevo Lote
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Lotes Activas</div>
        <div class="stat-value green">{{ $batches->where('status', 'proceso')->count() }}</div>
        <div class="stat-icon-wrap green">🏭</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Leche Utilizada (Período)</div>
        <div class="stat-value amber">{{ number_format($batches->sum('milk_used_liters'), 0) }} L</div>
        <div class="stat-icon-wrap amber">🥛</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Unidades Producidas</div>
        <div class="stat-value blue">{{ number_format($batches->sum('planned_quantity'), 0) }}</div>
        <div class="stat-icon-wrap blue">📦</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Costo Est. Total</div>
        <div class="stat-value purple">S/{{ number_format($batches->sum('estimated_cost'), 2) }}</div>
        <div class="stat-icon-wrap purple">💵</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🏭 Lotes de Producción</div>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha Producción</th>
                        <th>Código Lote</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Leche Usada</th>
                        <th>Costo Est.</th>
                        <th>Estado</th>
                        <th>Supervisor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                    <tr>
                        <td>
                            <strong>{{ $batch->production_date ? \Carbon\Carbon::parse($batch->production_date)->format('d/m/Y') : '—' }}</strong>
                        </td>
                        <td><code class="badge badge-purple">{{ $batch->batch_code }}</code></td>
                        <td>
                            <strong>{{ $batch->product->name ?? '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">{{ $batch->unit }}</div>
                        </td>
                        <td><strong>{{ number_format($batch->planned_quantity, 0) }}</strong> {{ $batch->unit }}</td>
                        <td>{{ number_format($batch->milk_used_liters, 1) }} L</td>
                        <td>S/{{ number_format($batch->estimated_cost, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ match($batch->status) {
                                'planificado' => 'blue',
                                'proceso' => 'amber',
                                'terminado' => 'green',
                                'cancelado' => 'red',
                                default => 'gray'
                            } }}">
                                {{ match($batch->status) {
                                    'planificado' => '📋 Planificado',
                                    'proceso' => '⏳ En Proceso',
                                    'terminado' => '✅ Terminado',
                                    'cancelado' => '❌ Cancelado',
                                    default => ucfirst($batch->status)
                                } }}
                            </span>
                        </td>
                        <td>{{ $batch->supervisor->name ?? '—' }} {{ $batch->supervisor->lastname ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty">
                                <div class="empty-icon">🏭</div>
                                <h3>No hay lotes de producción</h3>
                                <p>Crea un nuevo lote de producción presionando el botón "Nuevo Lote".</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
