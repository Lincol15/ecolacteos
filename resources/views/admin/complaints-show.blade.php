@extends('layouts.app')

@section('page-title', 'Detalle de Reclamo')
@section('page-subtitle', 'Ticket ' . ($complaint->ticket_number ?? ''))

@section('top-actions')
    <a href="{{ route('admin.complaints') }}" class="btn btn-ghost">
        ← Volver a Bandeja
    </a>
@endsection

@section('content')
<div class="grid-2">
    <div>
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    📩 {{ $complaint->subject }}
                    <span class="badge badge-blue" style="margin-left:8px;">{{ $complaint->ticket_number }}</span>
                </div>
                <div>
                    <span class="badge badge-{{ match($complaint->priority) {
                        'urgente' => 'red', 'alta' => 'amber', 'normal' => 'blue', 'baja' => 'gray', default => 'gray'
                    } }}">
                        {{ match($complaint->priority) {
                            'urgente' => '🚨 Urgente',
                            'alta' => '🔴 Alta',
                            'normal' => '🟡 Normal',
                            'baja' => '🟢 Baja',
                            default => ucfirst($complaint->priority)
                        } }}
                    </span>
                    <span class="badge badge-{{ match($complaint->status) {
                        'pendiente' => 'red', 'proceso' => 'amber', 'resuelto' => 'green', 'cerrado' => 'gray', default => 'gray'
                    } }}" style="margin-left:6px;">
                        {{ match($complaint->status) {
                            'pendiente' => '🔴 Pendiente',
                            'proceso' => '🟠 Proceso',
                            'resuelto' => '🟢 Resuelto',
                            'cerrado' => '⚫ Cerrado',
                            default => ucfirst($complaint->status)
                        } }}
                    </span>
                </div>
            </div>
            <div class="panel-body">
                <div style="padding:14px; background:#f8fafc; border-radius:12px; border:1px solid var(--border); margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; padding-bottom:10px; border-bottom:1px dashed var(--border);">
                        <div>
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; letter-spacing:0.5px;">Reportado por</div>
                            <strong>{{ $complaint->producer->user->name ?? 'Sin productor' }} {{ $complaint->producer->user->lastname ?? '' }}</strong>
                            <div style="font-size:12px; color:var(--text-light);">
                                {{ $complaint->producer->farm_name ?? '' }} · {{ $complaint->producer->code ?? '' }}
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; letter-spacing:0.5px;">Categoría</div>
                            <strong>{{ $complaint->category }}</strong>
                            <div style="font-size:12px; color:var(--text-light);">
                                {{ $complaint->created_at ? $complaint->created_at->format('d/m/Y H:i') : '' }}
                            </div>
                        </div>
                    </div>
                    <div style="font-size:13px; line-height:1.7; color:var(--text); white-space:pre-wrap;">
                        {{ $complaint->description }}
                    </div>
                    @if($complaint->attachment_path ?? false)
                        <div style="margin-top:14px; padding-top:12px; border-top:1px dashed var(--border);">
                            <a href="#" class="btn btn-info btn-sm">📎 Ver Adjunto ({{ basename($complaint->attachment_path) }})</a>
                        </div>
                    @endif
                </div>

                <h3 style="font-size:15px; font-weight:700; margin-bottom:16px;">💬 Hilo de Respuestas</h3>
                <div style="display:flex; flex-direction:column; gap:14px;">
                    @forelse($complaint->replies ?? [] as $reply)
                    <div style="padding:14px; background:{{ $reply->from_admin ? '#ecfeff' : '#f0fdf4' }}; border-radius:12px; border:1px solid var(--border); {{ $reply->from_admin ? 'margin-left:24px;' : 'margin-right:24px;' }}">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <strong style="font-size:13px;">
                                {{ $reply->from_admin ? '👔 Atención al Cliente' : '👨‍🌾 Productor' }}
                            </strong>
                            <span style="font-size:11px; color:var(--text-light);">
                                {{ $reply->created_at ? \Carbon\Carbon::parse($reply->created_at)->format('d/m/Y H:i') : '' }}
                            </span>
                        </div>
                        <div style="font-size:13px; line-height:1.6; white-space:pre-wrap;">{{ $reply->message }}</div>
                    </div>
                    @empty
                    <div class="empty" style="padding:30px;">
                        <div class="empty-icon" style="font-size:36px;">💬</div>
                        <h3 style="font-size:15px;">Sin respuestas</h3>
                        <p>Sé el primero en responder este reclamo.</p>
                    </div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.complaint-update', $complaint) }}" style="margin-top:20px;">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label class="form-label">✏️ Escribir Respuesta</label>
                        <textarea name="reply_message" class="form-textarea" placeholder="Escribe tu respuesta al reclamo...">{{ old('reply_message') }}</textarea>
                    </div>
                    <input type="hidden" name="action" value="reply">
                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">📨 Enviar Respuesta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">⚙️ Gestionar Reclamo</div>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('admin.complaint-update', $complaint) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="manage">
                    <div class="form-group">
                        <label class="form-label">Cambiar Estado</label>
                        <select name="status" class="form-select">
                            <option value="pendiente" {{ $complaint->status == 'pendiente' ? 'selected' : '' }}>🔴 Pendiente</option>
                            <option value="proceso" {{ $complaint->status == 'proceso' ? 'selected' : '' }}>🟠 En Proceso</option>
                            <option value="resuelto" {{ $complaint->status == 'resuelto' ? 'selected' : '' }}>🟢 Resuelto</option>
                            <option value="cerrado" {{ $complaint->status == 'cerrado' ? 'selected' : '' }}>⚫ Cerrado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cambiar Prioridad</label>
                        <select name="priority" class="form-select">
                            <option value="baja" {{ $complaint->priority == 'baja' ? 'selected' : '' }}>🟢 Baja</option>
                            <option value="normal" {{ $complaint->priority == 'normal' ? 'selected' : '' }}>🟡 Normal</option>
                            <option value="alta" {{ $complaint->priority == 'alta' ? 'selected' : '' }}>🔴 Alta</option>
                            <option value="urgente" {{ $complaint->priority == 'urgente' ? 'selected' : '' }}>🚨 Urgente</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asignar a (Responsable)</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Sin asignar</option>
                            @forelse($staff ?? [] as $user)
                            <option value="{{ $user->id }}" {{ ($complaint->assigned_to ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} {{ $user->lastname }} ({{ $user->role }})
                            </option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nota Interna</label>
                        <textarea name="internal_note" class="form-textarea" style="min-height:80px;" placeholder="Solo visible para el personal...">{{ old('internal_note', $complaint->internal_note ?? '') }}</textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
