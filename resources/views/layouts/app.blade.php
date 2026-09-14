<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VACA - Sistema de Gestión Láctea')</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-light: #6ee7b7;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --danger: #ef4444;
            --info: #3b82f6;
            --purple: #8b5cf6;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 50%, #fef3c7 100%);
            min-height: 100vh;
            color: var(--text);
        }
        .app-container { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #065f46 0%, #064e3b 100%);
            color: #fff;
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 20px rgba(0,0,0,0.15);
            z-index: 50;
        }
        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
        }
        .sidebar-logo-text { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .sidebar-logo-text span { color: #fbbf24; }
        .sidebar-nav { padding: 16px 12px; }
        .nav-section { font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.5); padding: 16px 12px 8px; font-weight: 600; }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; margin: 2px 0;
            border-radius: 10px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: #fff; transform: translateX(2px); }
        .nav-item.active {
            background: linear-gradient(90deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }
        .nav-icon { font-size: 18px; width: 22px; text-align: center; }
        .sidebar-footer {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 16px; border-top: 1px solid rgba(255,255,255,0.1);
        }
        .user-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px; border-radius: 10px;
            background: rgba(255,255,255,0.08);
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: #065f46;
            font-size: 14px;
        }
        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 11px; color: rgba(255,255,255,0.5); }
        .logout-btn {
            width: 34px; height: 34px; border-radius: 8px;
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .logout-btn:hover { background: #ef4444; color: #fff; }

        /* Main */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 24px 32px;
        }
        .top-bar {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 28px;
        }
        .page-title h1 {
            font-size: 26px; font-weight: 800; letter-spacing: -0.5px;
            background: linear-gradient(135deg, #065f46, #0891b2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .page-title p { font-size: 13px; color: var(--text-light); margin-top: 4px; }
        .top-actions { display: flex; align-items: center; gap: 12px; }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 600; font-size: 13.5px;
            text-decoration: none;
            border: none; cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-lg); }
        .btn:active { transform: translateY(0); }
        .btn-primary { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
        .btn-primary:hover { background: linear-gradient(135deg, #059669, #047857); }
        .btn-accent { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
        .btn-info { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; }
        .btn-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #fff; }
        .btn-ghost { background: #fff; color: var(--text); border: 1px solid var(--border); }
        .btn-ghost:hover { background: #f1f5f9; }
        .btn-sm { padding: 7px 12px; font-size: 12.5px; border-radius: 8px; }

        /* Cards & Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--card);
            border-radius: 18px;
            padding: 22px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.6);
            transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
        .stat-card::before {
            content: '';
            position: absolute; top: 0; right: 0;
            width: 100px; height: 100px;
            opacity: 0.12;
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        .stat-card.green::before { background: #10b981; }
        .stat-card.amber::before { background: #f59e0b; }
        .stat-card.blue::before { background: #3b82f6; }
        .stat-card.purple::before { background: #8b5cf6; }
        .stat-card.red::before { background: #ef4444; }
        .stat-card.cyan::before { background: #06b6d4; }
        .stat-label { font-size: 12px; color: var(--text-light); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 30px; font-weight: 800; margin-top: 8px; letter-spacing: -1px; }
        .stat-value.green { color: #059669; }
        .stat-value.amber { color: #d97706; }
        .stat-value.blue { color: #2563eb; }
        .stat-value.purple { color: #7c3aed; }
        .stat-value.red { color: #dc2626; }
        .stat-icon-wrap {
            position: absolute; top: 22px; right: 22px;
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .stat-icon-wrap.green { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #059669; }
        .stat-icon-wrap.amber { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }
        .stat-icon-wrap.blue { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; }
        .stat-icon-wrap.purple { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #7c3aed; }
        .stat-icon-wrap.red { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626; }
        .stat-icon-wrap.cyan { background: linear-gradient(135deg, #cffafe, #a5f3fc); color: #0891b2; }

        /* Panels */
        .panel {
            background: var(--card);
            border-radius: 18px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255,255,255,0.6);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .panel-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
        }
        .panel-title { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .panel-body { padding: 22px; }

        /* Tables */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 14px; text-align: left; }
        th {
            font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px;
            color: var(--text-light); font-weight: 700;
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
        }
        td { border-bottom: 1px solid #f1f5f9; font-size: 13.5px; }
        tr:hover td { background: #f8fafc; }
        tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px;
            font-size: 11.5px; font-weight: 600;
            white-space: nowrap;
        }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .badge-cyan { background: #cffafe; color: #155e75; }
        .badge-pink { background: #fce7f3; color: #9d174d; }

        /* Forms */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 13px; font-weight: 600; color: var(--text); }
        .form-input, .form-select, .form-textarea {
            padding: 10px 13px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: #fff;
            font-size: 14px;
            transition: all 0.2s;
            width: 100%;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .form-textarea { resize: vertical; min-height: 100px; }
        .form-actions {
            display: flex; gap: 12px; justify-content: flex-end;
            padding-top: 20px; border-top: 1px solid var(--border);
        }

        /* Grids */
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        @media (max-width: 900px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 12px;
            font-size: 14px; font-weight: 500;
        }
        .alert-success { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; border: 1px solid #6ee7b7; }
        .alert-error { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; border: 1px solid #fca5a5; }
        .alert-info { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; border: 1px solid #93c5fd; }

        /* Charts */
        .chart-container { position: relative; height: 300px; width: 100%; }

        /* Pagination */
        .pagination { display: flex; align-items: center; justify-content: center; gap: 6px; padding-top: 20px; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 7px 12px;
            border-radius: 8px;
            background: #fff;
            border: 1px solid var(--border);
            text-decoration: none;
            color: var(--text);
            font-size: 13px; font-weight: 500;
            transition: all 0.2s;
        }
        .pagination a:hover { background: #f1f5f9; }
        .pagination span.active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border-color: #059669;
        }

        /* Filters */
        .filters {
            display: flex; gap: 12px; flex-wrap: wrap;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 18px;
        }
        .filters .form-group { flex: 1; min-width: 160px; }

        /* Progress */
        .progress-wrap { background: #e2e8f0; border-radius: 8px; height: 8px; overflow: hidden; }
        .progress {
            height: 100%;
            border-radius: 8px;
            background: linear-gradient(90deg, #10b981, #34d399);
            transition: width 0.5s ease;
        }
        .progress.amber { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .progress.blue { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .progress.red { background: linear-gradient(90deg, #ef4444, #f87171); }

        /* Empty state */
        .empty {
            text-align: center; padding: 60px 20px;
            color: var(--text-light);
        }
        .empty-icon { font-size: 60px; margin-bottom: 16px; opacity: 0.4; }
        .empty h3 { font-size: 17px; font-weight: 600; margin-bottom: 6px; color: var(--text); }
        .empty p { font-size: 13px; }

        /* Links */
        .link { color: #2563eb; text-decoration: none; font-weight: 500; }
        .link:hover { text-decoration: underline; }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="app-container">
        @section('sidebar')
            <aside class="sidebar">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon">🥛</div>
                    <div class="sidebar-logo-text">VACA<span>SYS</span></div>
                </div>
                <nav class="sidebar-nav">
                    <div class="nav-section">Principal</div>
                    <a href="{{ auth()->check() ? match(auth()->user()->role) {
                        'admin', 'gerente' => route('admin.dashboard'),
                        'acopiador' => route('collector.dashboard'),
                        'control_calidad' => route('quality.dashboard'),
                        'trabajador_planta' => route('plant.dashboard'),
                        'productor' => route('producer.dashboard'),
                        default => route('home')
                    } : route('home') }}" class="nav-item {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>

                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'gerente']))
                    <div class="nav-section">Gestión</div>
                    <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <span class="nav-icon">👥</span> Usuarios
                    </a>
                    <a href="{{ route('admin.producers') }}" class="nav-item {{ request()->routeIs('admin.producers*') ? 'active' : '' }}">
                        <span class="nav-icon">👨‍🌾</span> Productores
                    </a>
                    <a href="{{ route('admin.deliveries') }}" class="nav-item {{ request()->routeIs('admin.deliveries*') ? 'active' : '' }}">
                        <span class="nav-icon">🚛</span> Entregas Leche
                    </a>
                    <a href="{{ route('admin.quality') }}" class="nav-item {{ request()->routeIs('admin.quality*') ? 'active' : '' }}">
                        <span class="nav-icon">🧪</span> Control Calidad
                    </a>
                    <a href="{{ route('admin.production') }}" class="nav-item {{ request()->routeIs('admin.production*') ? 'active' : '' }}">
                        <span class="nav-icon">🏭</span> Producción
                    </a>
                    <a href="{{ route('admin.inventory') }}" class="nav-item {{ request()->routeIs('admin.inventory*') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span> Inventario
                    </a>
                    <a href="{{ route('admin.sales') }}" class="nav-item {{ request()->routeIs('admin.sales*') ? 'active' : '' }}">
                        <span class="nav-icon">💰</span> Ventas
                    </a>
                    <a href="{{ route('admin.payments') }}" class="nav-item {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                        <span class="nav-icon">💳</span> Pagos
                    </a>
                    <a href="{{ route('admin.routes') }}" class="nav-item {{ request()->routeIs('admin.routes*') ? 'active' : '' }}">
                        <span class="nav-icon">🗺️</span> Rutas
                    </a>
                    <div class="nav-section">Sistema</div>
                    <a href="{{ route('admin.complaints') }}" class="nav-item {{ request()->routeIs('admin.complaints*') ? 'active' : '' }}">
                        <span class="nav-icon">📩</span> Reclamos
                    </a>
                    <a href="{{ route('admin.notifications') }}" class="nav-item {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                        <span class="nav-icon">📢</span> Avisos
                    </a>
                    <a href="{{ route('admin.config') }}" class="nav-item {{ request()->routeIs('admin.config*') ? 'active' : '' }}">
                        <span class="nav-icon">⚙️</span> Configuración
                    </a>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'productor')
                    <div class="nav-section">Mi Cuenta</div>
                    <a href="{{ route('producer.deliveries') }}" class="nav-item {{ request()->routeIs('producer.deliveries*') ? 'active' : '' }}">
                        <span class="nav-icon">🚛</span> Mis Entregas
                    </a>
                    <a href="{{ route('producer.quality') }}" class="nav-item {{ request()->routeIs('producer.quality*') ? 'active' : '' }}">
                        <span class="nav-icon">🧪</span> Análisis Calidad
                    </a>
                    <a href="{{ route('producer.payments') }}" class="nav-item {{ request()->routeIs('producer.payments*') ? 'active' : '' }}">
                        <span class="nav-icon">💳</span> Mis Pagos
                    </a>
                    <a href="{{ route('producer.complaints') }}" class="nav-item {{ request()->routeIs('producer.complaints*') ? 'active' : '' }}">
                        <span class="nav-icon">📩</span> Mis Reclamos
                    </a>
                    <a href="{{ route('producer.profile') }}" class="nav-item {{ request()->routeIs('producer.profile*') ? 'active' : '' }}">
                        <span class="nav-icon">👤</span> Mi Perfil
                    </a>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'acopiador')
                    <div class="nav-section">Acopio</div>
                    <a href="{{ route('collector.routes') }}" class="nav-item {{ request()->routeIs('collector.routes*') ? 'active' : '' }}">
                        <span class="nav-icon">🗺️</span> Mis Rutas
                    </a>
                    <a href="{{ route('collector.deliveries') }}" class="nav-item {{ request()->routeIs('collector.deliveries*') ? 'active' : '' }}">
                        <span class="nav-icon">🚛</span> Entregas
                    </a>
                    <a href="{{ route('collector.profile') }}" class="nav-item {{ request()->routeIs('collector.profile*') ? 'active' : '' }}">
                        <span class="nav-icon">👤</span> Perfil
                    </a>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'control_calidad')
                    <div class="nav-section">Laboratorio</div>
                    <a href="{{ route('quality.reports') }}" class="nav-item {{ request()->routeIs('quality.reports*') ? 'active' : '' }}">
                        <span class="nav-icon">📋</span> Reportes
                    </a>
                    <a href="{{ route('quality.profile') }}" class="nav-item {{ request()->routeIs('quality.profile*') ? 'active' : '' }}">
                        <span class="nav-icon">👤</span> Perfil
                    </a>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'trabajador_planta')
                    <div class="nav-section">Planta</div>
                    <a href="{{ route('plant.batches') }}" class="nav-item {{ request()->routeIs('plant.batches*') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span> Lotes
                    </a>
                    <a href="{{ route('plant.inventory') }}" class="nav-item {{ request()->routeIs('plant.inventory*') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span> Inventario
                    </a>
                    <a href="{{ route('plant.profile') }}" class="nav-item {{ request()->routeIs('plant.profile*') ? 'active' : '' }}">
                        <span class="nav-icon">👤</span> Perfil
                    </a>
                    @endif
                </nav>
                @auth
                <div class="sidebar-footer">
                    <div class="user-card">
                        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->lastname ?? '', 0, 1)) }}</div>
                        <div class="user-info">
                            <div class="user-name">{{ auth()->user()->fullname }}</div>
                            <div class="user-role">{{ auth()->user()->roleLabel }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('¿Cerrar sesión?')">
                            @csrf
                            <button type="submit" class="logout-btn" title="Cerrar sesión">🚪</button>
                        </form>
                    </div>
                </div>
                @endauth
            </aside>
        @show

        <main class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>@yield('page-title', 'Dashboard')</h1>
                    <p>@yield('page-subtitle', 'Bienvenido al sistema de gestión láctea')</p>
                </div>
                <div class="top-actions">
                    @yield('top-actions')
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">❌ {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    ❌
                    <div>
                        @foreach($errors->all() as $e)
                            <div>{{ $e }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
