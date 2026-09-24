<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Ecolácteos Huata</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/logo-ecolacteos.png') }}">
    <style>
        :root { --green: #075B3A; --green-dark: #043521; --gold: #D6A927; --text: #1B2A23; --muted: #62736A; --line: #E3EEE8; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; color: var(--text); background: #EEF3F0; padding: 24px 16px; -webkit-font-smoothing: antialiased; }
        .toolbar { max-width: 820px; margin: 0 auto 16px; display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
        .toolbar a, .toolbar button {
            display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px;
            border-radius: 10px; border: 1px solid #CFDDD5; background: #fff; color: var(--text);
            font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;
        }
        .toolbar button { background: var(--green); border-color: var(--green); color: #fff; }
        .toolbar svg { width: 18px; height: 18px; }
        .sheet { max-width: 820px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(4, 53, 33, 0.1); overflow: hidden; }
        .sheet-head { display: flex; justify-content: space-between; gap: 20px; padding: 28px 32px; background: linear-gradient(135deg, var(--green), var(--green-dark)); color: #fff; flex-wrap: wrap; }
        .brand { display: flex; gap: 14px; align-items: center; }
        .brand img { width: 56px; height: 56px; border-radius: 50%; background: #fff; border: 2px solid rgba(255,255,255,.8); object-fit: cover; }
        .brand-name { font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 700; }
        .brand-name span { color: #F2C94C; }
        .brand-sub { font-size: 12px; opacity: .8; margin-top: 2px; }
        .doc { text-align: right; }
        .doc-type { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: .8; }
        .doc-number { font-family: 'Poppins', sans-serif; font-size: 22px; font-weight: 700; margin-top: 2px; }
        .doc-date { font-size: 12.5px; opacity: .85; margin-top: 4px; }
        .sheet-body { padding: 28px 32px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .box { border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px; }
        .box h4 { font-size: 11px; text-transform: uppercase; letter-spacing: .8px; color: var(--muted); margin-bottom: 8px; }
        .box p { font-size: 13.5px; line-height: 1.6; }
        .box strong { font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .6px; color: var(--muted); padding: 10px 12px; background: #F6FBF8; border-bottom: 1px solid var(--line); }
        td { padding: 10px 12px; border-bottom: 1px solid #F0F4F2; }
        .num { text-align: right; white-space: nowrap; }
        .summary { margin-left: auto; width: min(100%, 360px); }
        .line { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; border-bottom: 1px dashed var(--line); }
        .line span:first-child { color: var(--muted); }
        .plus { color: #067647; }
        .minus { color: #B42318; }
        .total { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding: 14px 16px; border-radius: 12px; background: #F2FAF6; border: 1px solid #BFE5D2; }
        .total span { font-weight: 600; }
        .total strong { font-family: 'Poppins', sans-serif; font-size: 22px; color: var(--green); }
        .status { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .status.pagado { background: #E7F7EF; color: #067647; }
        .status.pendiente, .status.procesando, .status.parcial { background: #FEF4E6; color: #B54708; }
        .status.rechazado { background: #F2F4F7; color: #475467; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 48px; }
        .signature { border-top: 1px solid #98A2B3; padding-top: 8px; text-align: center; font-size: 12px; color: var(--muted); }
        .foot { padding: 16px 32px; border-top: 1px solid var(--line); font-size: 11.5px; color: var(--muted); display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; border-radius: 0; max-width: none; }
            .sheet-head { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .total, th, .status { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ $backUrl }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Volver
        </a>
        <button type="button" onclick="window.print()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
            Descargar PDF / Imprimir
        </button>
    </div>

    <main class="sheet">
        <header class="sheet-head">
            <div class="brand">
                <img src="{{ asset('images/logo-ecolacteos.png') }}" alt="Ecolácteos Huata">
                <div>
                    <div class="brand-name">Ecolácteos<span> Huata</span></div>
                    <div class="brand-sub">
                        {{ $site['nombre_planta'] ?: 'Sistema de Gestión y Acopio de Productos Lácteos' }}
                        @if($site['ruc_planta']) · RUC {{ $site['ruc_planta'] }} @endif
                    </div>
                    @if($site['direccion_planta'])
                    <div class="brand-sub">{{ $site['direccion_planta'] }}@if($site['telefono_planta']) · {{ $site['telefono_planta'] }}@endif</div>
                    @endif
                </div>
            </div>
            <div class="doc">
                <div class="doc-type">@yield('doc-type')</div>
                <div class="doc-number">@yield('doc-number')</div>
                <div class="doc-date">Emitido: {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </header>

        <div class="sheet-body">
            @yield('body')
        </div>

        <footer class="foot">
            <span>Documento generado por el sistema de {{ $site['nombre_planta'] ?: 'Ecolácteos Huata' }}.</span>
            <span>@yield('doc-number')</span>
        </footer>
    </main>

    @if(request()->boolean('download'))
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
    @endif
</body>
</html>
