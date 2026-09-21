@extends('layouts.app')

@section('page-title', 'Mis Reclamos')
@section('page-subtitle', 'Historial de quejas y reclamos enviados')

@section('top-actions')
    <a href="{{ route('producer.complaints-create') }}" class="btn btn-primary">
        <span>➕</span> Nuevo Reclamo
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📣 Mis Reclamos</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Ticket</th><th>Categoría</th><th>Asunto</th><th>Prioridad</th><th>Estado</th><th>Fecha</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($complaints as $c)
                <tr>
                    <td><code class="badge badge-blue">{{ $c->ticket_number }}</code></td>
                    <td>{{ \App\Models\Complaint::CATEGORIES[$c->category] ?? $c->category }}</td>
                    <td>{{ $c->subject }}</td>
                    <td>
                        <span class="badge {{ match($c->priority) { 'urgente' => 'badge-red', 'alta' => 'badge-orange', 'baja' => 'badge-gray', default => 'badge-blue' } }}">
                            {{ \App\Models\Complaint::PRIORITIES[$c->priority] ?? $c->priority }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ match($c->status) { 'abierto' => 'badge-amber', 'en_revision' => 'badge-blue', 'respondido' => 'badge-green', 'cerrado' => 'badge-gray', 'rechazado' => 'badge-red', default => 'badge-gray' } }}">
                            {{ \App\Models\Complaint::STATUS[$c->status] ?? $c->status }}
                        </span>
                    </td>
                    <td style="font-size:12px;color:var(--text-light)">{{ $c->created_at->format('d/m/Y') }}</td>
                    <td><a href="{{ route('producer.complaints-show', $c) }}" class="btn btn-sm btn-ghost">👁️ Ver</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty"><div class="empty-icon">📣</div><h3>Sin reclamos registrados</h3><p>Si tienes algún inconveniente, envía un nuevo reclamo.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($complaints, 'links'))
    <div style="padding:20px">{{ $complaints->links() }}</div>
    @endif
</div>
@endsection
