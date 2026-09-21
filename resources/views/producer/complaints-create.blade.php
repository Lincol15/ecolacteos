@extends('layouts.app')

@section('page-title', 'Nuevo Reclamo')
@section('page-subtitle', 'Cuéntanos tu inconveniente y te responderemos a la brevedad')

@section('top-actions')
    <a href="{{ route('producer.complaints') }}" class="btn btn-ghost">← Volver a Mis Reclamos</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📣 Enviar Reclamo</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('producer.complaints-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select name="category" required class="form-select">
                        <option value="">-- Seleccionar --</option>
                        @foreach(\App\Models\Complaint::CATEGORIES as $value => $label)
                        <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridad *</label>
                    <select name="priority" required class="form-select">
                        @foreach(\App\Models\Complaint::PRIORITIES as $value => $label)
                        <option value="{{ $value }}" {{ old('priority', 'normal') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Asunto *</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="form-input" maxlength="200" placeholder="Resumen breve del inconveniente">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Descripción *</label>
                    <textarea name="description" required class="form-textarea" maxlength="2000" placeholder="Describe con detalle lo sucedido...">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('producer.complaints') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">📨 Enviar Reclamo</button>
            </div>
        </form>
    </div>
</div>
@endsection
