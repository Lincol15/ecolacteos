@extends('layouts.app')

@section('page-title', 'Reclamo ' . $complaint->ticket_number)
@section('page-subtitle', $complaint->subject)

@section('top-actions')
    <a href="{{ route('producer.complaints') }}" class="btn btn-ghost">← Volver a Mis Reclamos</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📣 {{ $complaint->subject }}</div>
        <div>
            <span class="badge {{ match($complaint->priority) { 'urgente' => 'badge-red', 'alta' => 'badge-orange', 'baja' => 'badge-gray', default => 'badge-blue' } }}">
                {{ \App\Models\Complaint::PRIORITIES[$complaint->priority] ?? $complaint->priority }}
            </span>
            <span class="badge {{ match($complaint->status) { 'abierto' => 'badge-amber', 'en_revision' => 'badge-blue', 'respondido' => 'badge-green', 'cerrado' => 'badge-gray', 'rechazado' => 'badge-red', default => 'badge-gray' } }}">
                {{ \App\Models\Complaint::STATUS[$complaint->status] ?? $complaint->status }}
            </span>
        </div>
    </div>
    <div class="panel-body">
        <div style="padding:14px; background:#f8fafc; border-radius:12px; border:1px solid var(--border); margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px; padding-bottom:10px; border-bottom:1px dashed var(--border);">
                <div>
                    <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; letter-spacing:0.5px;">Ticket</div>
                    <strong>{{ $complaint->ticket_number }}</strong>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; letter-spacing:0.5px;">Categoría</div>
                    <strong>{{ \App\Models\Complaint::CATEGORIES[$complaint->category] ?? $complaint->category }}</strong>
                    <div style="font-size:12px; color:var(--text-light);">{{ $complaint->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            <div style="font-size:13.5px; line-height:1.7; white-space:pre-wrap;">{{ $complaint->description }}</div>
        </div>

        <h3 style="font-size:15px; font-weight:700; margin-bottom:16px;">💬 Respuesta del Equipo</h3>
        @if($complaint->staff_response)
        <div style="padding:14px; background:#ecfeff; border-radius:12px; border:1px solid var(--border);">
            <div style="font-size:11px; color:var(--text-light); margin-bottom:6px;">
                {{ $complaint->responded_at?->format('d/m/Y H:i') }}
            </div>
            <div style="font-size:13.5px; line-height:1.6; white-space:pre-wrap;">{{ $complaint->staff_response }}</div>
        </div>
        @else
        <div class="empty" style="padding:30px;">
            <div class="empty-icon" style="font-size:36px;">💬</div>
            <h3 style="font-size:15px;">Aún sin respuesta</h3>
            <p>Te notificaremos cuando el equipo responda tu reclamo.</p>
        </div>
        @endif
    </div>
</div>
@endsection
