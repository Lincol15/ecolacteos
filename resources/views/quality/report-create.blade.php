@extends('layouts.app')

@section('title', 'Nuevo Análisis LACTOMAT - Ecolácteos Huata')
@section('page-title', 'Nuevo Análisis de Calidad')
@section('page-subtitle', 'Registrar resultados del análisis LACTOMAT por entrega de leche')

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧪 Formulario de Análisis LACTOMAT</div>
        <a href="{{ route('quality.reports') }}" class="btn btn-sm btn-ghost">← Volver a Reportes</a>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('quality.report-store') }}" enctype="multipart/form-data" id="reportForm">
            @csrf
            <input type="hidden" name="ticket_photo_path" id="ticket_photo_path" value="">

            <div style="background:#f5f3ff;border-radius:12px;padding:16px 20px;margin-bottom:20px">
                <div style="font-weight:700;color:#4c1d95;margin-bottom:8px">📷 Escaneo de Ticket LACTOMAT (OCR)</div>
                <div style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label class="form-label">Foto del Ticket</label>
                        <input type="file" name="ticket_photo" id="ticket_photo" accept="image/*" class="form-input">
                    </div>
                    <button type="button" id="scanBtn" class="btn btn-purple">🔍 Escanear Ticket</button>
                </div>
                <div id="ocrStatus" style="font-size:12.5px; margin-top:8px; color:#6b21a8;"></div>
            </div>

            <div class="form-grid" style="margin-bottom:20px">
                <div class="form-group" style="grid-column:1 / -1">
                    <label class="form-label">Entrega de Leche (id_registro_acopio, opcional)</label>
                    <select name="milk_delivery_id" id="milk_delivery_id" class="form-select">
                        <option value="">-- Análisis sin entrega asociada --</option>
                        @forelse($pendingDeliveries ?? [] as $d)
                        <option value="{{ $d->id }}" data-producer="{{ $d->producer_id }}" {{ (request('delivery_id') == $d->id || old('milk_delivery_id') == $d->id) ? 'selected' : '' }}>
                            #{{ $d->id }} - {{ $d->producer?->user?->fullname ?? 'N/A' }} ({{ number_format($d->liters, 2) }} L) - {{ $d->delivery_date?->format('d/m/Y') }}
                        </option>
                        @empty
                        <option value="" disabled>No hay entregas pendientes de análisis</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group" style="grid-column:1 / -1">
                    <label class="form-label">Productor *</label>
                    <select name="producer_id" id="producer_id" class="form-select" required>
                        <option value="">-- Seleccionar productor --</option>
                        @foreach($producers ?? [] as $p)
                        <option value="{{ $p->id }}" {{ (old('producer_id') ?? $delivery?->producer_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->user?->fullname }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="background:#f8fafc;border-radius:12px;padding:16px 20px;margin-bottom:24px">
                <div style="font-weight:700;color:#065f46;margin-bottom:8px">📋 Datos de Muestra</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Código de Muestra</label>
                        <input type="text" name="sample_code" value="{{ old('sample_code', 'SMP-' . str_pad(rand(1,99999), 5, '0', STR_PAD_LEFT)) }}" class="form-input" placeholder="SMP-00001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Temperatura (°C)</label>
                        <input type="number" step="0.01" name="temperatura" value="{{ old('temperatura', $delivery?->temperature) }}" class="form-input" placeholder="4.50">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Origen de los Datos</label>
                        <select name="origen_datos" class="form-select">
                            <option value="manual" {{ old('origen_datos', 'manual') === 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="ocr" {{ old('origen_datos') === 'ocr' ? 'selected' : '' }}>OCR</option>
                            <option value="ocr_corregido" {{ old('origen_datos') === 'ocr_corregido' ? 'selected' : '' }}>OCR Corregido</option>
                        </select>
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('milk_delivery_id')?.addEventListener('change', function () {
                    const producerId = this.selectedOptions[0]?.dataset?.producer;
                    if (producerId) {
                        document.getElementById('producer_id').value = producerId;
                    }
                });
            </script>

            <div style="background:#eff6ff;border-radius:12px;padding:16px 20px;margin-bottom:24px">
                <div style="font-weight:700;color:#1e40af;margin-bottom:8px">📊 Parámetros LACTOMAT (%)</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Grasa % *</label>
                        <input type="number" step="0.01" name="grasa_pct" value="{{ old('grasa_pct', 3.5) }}" class="form-input" required placeholder="3.50">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Proteína % *</label>
                        <input type="number" step="0.01" name="proteina_pct" value="{{ old('proteina_pct', 3.2) }}" class="form-input" required placeholder="3.20">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lactosa % *</label>
                        <input type="number" step="0.01" name="lactosa_pct" value="{{ old('lactosa_pct', 4.8) }}" class="form-input" required placeholder="4.80">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sólidos No Grasos % *</label>
                        <input type="number" step="0.01" name="solidos_no_grasos_pct" value="{{ old('solidos_no_grasos_pct', 8.7) }}" class="form-input" required placeholder="8.70">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Sólidos % *</label>
                        <input type="number" step="0.01" name="total_solidos_pct" value="{{ old('total_solidos_pct', 12.2) }}" class="form-input" required placeholder="12.20">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Agua Añadida % *</label>
                        <input type="number" step="0.01" name="agua_aniadida_pct" value="{{ old('agua_aniadida_pct', 0) }}" class="form-input" required placeholder="0.00">
                    </div>
                </div>
            </div>

            <div style="background:#fef3c7;border-radius:12px;padding:16px 20px;margin-bottom:24px">
                <div style="font-weight:700;color:#92400e;margin-bottom:8px">🔬 Parámetros Físico-Químicos</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">pH *</label>
                        <input type="number" step="0.01" name="ph" value="{{ old('ph', 6.6) }}" class="form-input" required placeholder="6.60">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Punto de Congelación (°C) *</label>
                        <input type="number" step="0.001" name="punto_congelacion" value="{{ old('punto_congelacion', -0.520) }}" class="form-input" required placeholder="-0.520">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Acidez Dornic (°D) *</label>
                        <input type="number" step="0.1" name="acidez_dornic" value="{{ old('acidez_dornic', 16) }}" class="form-input" required placeholder="16.0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Densidad (g/L) *</label>
                        <input type="number" step="0.1" name="densidad" value="{{ old('densidad', 1028) }}" class="form-input" required placeholder="1028.0">
                    </div>
                </div>
            </div>

            <div style="background:#dcfce7;border-radius:12px;padding:16px 20px;margin-bottom:24px">
                <div style="font-weight:700;color:#065f46;margin-bottom:8px">🎯 Resultado del Análisis</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Resultado Final *</label>
                        <select name="result" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="aprobado" {{ old('result') === 'aprobado' ? 'selected' : '' }}>✅ Aprobado</option>
                            <option value="rechazado" {{ old('result') === 'rechazado' ? 'selected' : '' }}>❌ Rechazado</option>
                            <option value="aceptable" {{ old('result') === 'aceptable' ? 'selected' : '' }}>👍 Aceptable</option>
                            <option value="observado" {{ old('result') === 'observado' ? 'selected' : '' }}>👁️ Observado</option>
                            <option value="pendiente" {{ old('result') === 'pendiente' ? 'selected' : '' }}>⏳ Pendiente</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Motivo de Rechazo</label>
                        <select name="rejection_reason" class="form-select">
                            <option value="ninguno">Ninguno</option>
                            <option value="baja_grasa" {{ old('rejection_reason') === 'baja_grasa' ? 'selected' : '' }}>Baja Grasa</option>
                            <option value="baja_proteina" {{ old('rejection_reason') === 'baja_proteina' ? 'selected' : '' }}>Baja Proteína</option>
                            <option value="exceso_agua" {{ old('rejection_reason') === 'exceso_agua' ? 'selected' : '' }}>Exceso de Agua</option>
                            <option value="acidez_alta" {{ old('rejection_reason') === 'acidez_alta' ? 'selected' : '' }}>Acidez Alta</option>
                            <option value="ph_anormal" {{ old('rejection_reason') === 'ph_anormal' ? 'selected' : '' }}>pH Anormal</option>
                            <option value="congelacion_anormal" {{ old('rejection_reason') === 'congelacion_anormal' ? 'selected' : '' }}>Congelación Anormal</option>
                            <option value="contaminacion" {{ old('rejection_reason') === 'contaminacion' ? 'selected' : '' }}>Contaminación</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column:1 / -1">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observations" class="form-textarea" rows="3" placeholder="Notas adicionales sobre el análisis...">{{ old('observations') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('quality.dashboard') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar Análisis</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('scanBtn')?.addEventListener('click', function () {
    const fileInput = document.getElementById('ticket_photo');
    const statusEl = document.getElementById('ocrStatus');
    if (!fileInput.files.length) {
        statusEl.textContent = 'Selecciona primero una foto del ticket.';
        return;
    }
    const formData = new FormData();
    formData.append('ticket_photo', fileInput.files[0]);
    formData.append('_token', document.querySelector('input[name="_token"]').value);

    statusEl.textContent = 'Escaneando...';
    this.disabled = true;

    fetch('{{ route('quality.report-ocr-scan') }}', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            document.getElementById('ticket_photo_path').value = data.ticket_photo_path || '';
            Object.entries(data.fields || {}).forEach(([field, value]) => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) input.value = value;
            });
            const origenSelect = document.querySelector('[name="origen_datos"]');
            if (origenSelect && Object.keys(data.fields || {}).length) {
                origenSelect.value = 'ocr';
            }
            statusEl.textContent = data.message || 'Escaneo completado.';
        })
        .catch(() => { statusEl.textContent = 'Error al escanear la imagen.'; })
        .finally(() => { this.disabled = false; });
});
</script>
@endsection
