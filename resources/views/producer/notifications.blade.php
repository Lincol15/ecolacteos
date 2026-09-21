@extends('layouts.app')

@section('page-title', 'Notificaciones')
@section('page-subtitle', 'Avisos de la planta')

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🔔 Avisos</div>
    </div>
    <div class="panel-body">
        @forelse($notifications as $n)
        <div style="padding:16px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
            <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                    <strong>{{ $n->title }}</strong>
                    @unless($readIds->contains($n->id))
                    <span class="badge badge-amber">Nueva</span>
                    @endunless
                    <span class="badge {{ match($n->priority) { 'urgente' => 'badge-red', 'alta' => 'badge-orange', 'baja' => 'badge-gray', default => 'badge-blue' } }}">{{ ucfirst($n->priority) }}</span>
                </div>
                <p style="font-size:13.5px; color:var(--text-light);">{{ $n->message }}</p>
                <div style="font-size:11px; color:var(--text-light); margin-top:6px;">{{ $n->published_at?->format('d/m/Y H:i') ?? $n->created_at->format('d/m/Y H:i') }}</div>
            </div>
            @unless($readIds->contains($n->id))
            <form method="POST" action="{{ route('producer.notification-read', $n) }}">
                @csrf @method('PUT')
                <button type="submit" class="btn btn-ghost btn-sm">✔ Marcar leída</button>
            </form>
            @endunless
        </div>
        @empty
        <div class="empty">
            <div class="empty-icon">🔔</div>
            <h3>Sin notificaciones</h3>
            <p>No hay avisos publicados por el momento.</p>
        </div>
        @endforelse
    </div>
    @if(method_exists($notifications, 'links'))
    <div style="padding:20px">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
