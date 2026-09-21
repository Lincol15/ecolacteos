<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ecolácteos Huata - Sistema de Gestión')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/logo-ecolacteos.png') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-container">
        @section('sidebar')
            <aside class="sidebar">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <img src="{{ asset('images/logo-ecolacteos.png') }}" alt="Ecolácteos Huata" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                    </div>
                    <div>
                        <div class="sidebar-logo-text">Ecolácteos<span> Huata</span></div>
                        <div class="sidebar-logo-brand">by 4bytes</div>
                    </div>
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

                    @if(auth()->check() && auth()->user()->isAdmin())
                    <div class="nav-section">Gestión</div>
                    <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <span class="nav-icon">👥</span> Usuarios
                    </a>
                    <a href="{{ route('admin.collectors') }}" class="nav-item {{ request()->routeIs('admin.collectors*') ? 'active' : '' }}">
                        <span class="nav-icon">🚜</span> Acopiadores
                    </a>
                    <a href="{{ route('admin.producers') }}" class="nav-item {{ request()->routeIs('admin.producers*') ? 'active' : '' }}">
                        <span class="nav-icon">🌾</span> Productores
                    </a>
                    <a href="{{ route('admin.payments') }}" class="nav-item {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                        <span class="nav-icon">💳</span> Pagos
                    </a>
                    <a href="{{ route('admin.ingredients') }}" class="nav-item {{ request()->routeIs('admin.ingredients*') ? 'active' : '' }}">
                        <span class="nav-icon">🧂</span> Insumos
                    </a>
                    <div class="nav-section">Calidad</div>
                    <a href="{{ route('admin.quality') }}" class="nav-item {{ request()->routeIs('admin.quality') ? 'active' : '' }}">
                        <span class="nav-icon">🔬</span> Historial de Análisis
                    </a>
                    <a href="{{ route('admin.sanctions') }}" class="nav-item {{ request()->routeIs('admin.sanctions*') ? 'active' : '' }}">
                        <span class="nav-icon">🚫</span> Sanciones
                    </a>
                    <div class="nav-section">Sistema</div>
                    <a href="{{ route('admin.complaints') }}" class="nav-item {{ request()->routeIs('admin.complaints*') ? 'active' : '' }}">
                        <span class="nav-icon">📣</span> Quejas
                    </a>
                    <a href="{{ route('admin.notifications') }}" class="nav-item {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                        <span class="nav-icon">📢</span> Avisos
                    </a>
                    <a href="{{ route('admin.price') }}" class="nav-item {{ request()->routeIs('admin.price*') ? 'active' : '' }}">
                        <span class="nav-icon">💰</span> Actualizar Precio
                    </a>
                    <a href="{{ route('admin.config') }}" class="nav-item {{ request()->routeIs('admin.config*') ? 'active' : '' }}">
                        <span class="nav-icon">⚙️</span> Configuración
                    </a>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'gerente')
                    <div class="nav-section">Gestión</div>
                    <a href="{{ route('admin.producers') }}" class="nav-item {{ request()->routeIs('admin.producers*') ? 'active' : '' }}">
                        <span class="nav-icon">🌾</span> Productores
                    </a>
                    <a href="{{ route('admin.deliveries') }}" class="nav-item {{ request()->routeIs('admin.deliveries*') ? 'active' : '' }}">
                        <span class="nav-icon">🥛</span> Acopio
                    </a>
                    <a href="{{ route('admin.quality') }}" class="nav-item {{ request()->routeIs('admin.quality*') ? 'active' : '' }}">
                        <span class="nav-icon">🔬</span> Calidad
                    </a>
                    <a href="{{ route('admin.payments') }}" class="nav-item {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                        <span class="nav-icon">💳</span> Pagos
                    </a>
                    <a href="{{ route('admin.production') }}" class="nav-item {{ request()->routeIs('admin.production') ? 'active' : '' }}">
                        <span class="nav-icon">🧀</span> Producción
                    </a>
                    <a href="{{ route('admin.ingredients') }}" class="nav-item {{ request()->routeIs('admin.ingredients*') ? 'active' : '' }}">
                        <span class="nav-icon">🧂</span> Insumos
                    </a>
                    <a href="{{ route('admin.sales') }}" class="nav-item {{ request()->routeIs('admin.sales') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span> Ventas
                    </a>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'productor')
                    <div class="nav-section">Mi Cuenta</div>
                    <a href="{{ route('producer.deliveries') }}" class="nav-item {{ request()->routeIs('producer.deliveries*') ? 'active' : '' }}">
                        <span class="nav-icon">🥛</span> Mis Entregas
                    </a>
                    <a href="{{ route('producer.payments') }}" class="nav-item {{ request()->routeIs('producer.payments*') ? 'active' : '' }}">
                        <span class="nav-icon">💳</span> Mis Pagos
                    </a>
                    <a href="{{ route('producer.quality') }}" class="nav-item {{ request()->routeIs('producer.quality*') ? 'active' : '' }}">
                        <span class="nav-icon">🔬</span> Calidad
                    </a>
                    <a href="{{ route('producer.complaints') }}" class="nav-item {{ request()->routeIs('producer.complaints*') ? 'active' : '' }}">
                        <span class="nav-icon">📣</span> Quejas
                    </a>
                    <a href="{{ route('producer.notifications') }}" class="nav-item {{ request()->routeIs('producer.notifications*') ? 'active' : '' }}">
                        <span class="nav-icon">🔔</span> Notificaciones
                        @php($unreadCount = \App\Models\Notification::visibleForUser(auth()->user())->whereDoesntHave('reads', fn($q) => $q->where('user_id', auth()->id()))->count())
                        @if($unreadCount > 0)
                        <span class="badge badge-red" style="margin-left:auto;">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'acopiador')
                    <div class="nav-section">Acopio</div>
                    <a href="{{ route('collector.delivery-create') }}" class="nav-item {{ request()->routeIs('collector.delivery-create') || request()->routeIs('collector.delivery-store') ? 'active' : '' }}">
                        <span class="nav-icon">🥛</span> Registrar Acopio
                    </a>
                    <a href="{{ route('collector.producers') }}" class="nav-item {{ request()->routeIs('collector.producers*') ? 'active' : '' }}">
                        <span class="nav-icon">👥</span> Mis Productores
                    </a>
                    <a href="{{ route('collector.journal') }}" class="nav-item {{ request()->routeIs('collector.journal*') ? 'active' : '' }}">
                        <span class="nav-icon">📋</span> Historial Jornada
                    </a>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'control_calidad')
                    <div class="nav-section">Laboratorio</div>
                    <a href="{{ route('quality.report-create') }}" class="nav-item {{ request()->routeIs('quality.report-create') || request()->routeIs('quality.report-store') ? 'active' : '' }}">
                        <span class="nav-icon">📝</span> Nuevo Análisis
                    </a>
                    <a href="{{ route('quality.reports') }}" class="nav-item {{ request()->routeIs('quality.reports') || request()->routeIs('quality.report-show') ? 'active' : '' }}">
                        <span class="nav-icon">📋</span> Historial
                    </a>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'trabajador_planta')
                    <div class="nav-section">Planta</div>
                    <a href="{{ route('plant.production') }}" class="nav-item {{ request()->routeIs('plant.production*') || request()->routeIs('plant.batches*') || request()->routeIs('plant.recipe-create') ? 'active' : '' }}">
                        <span class="nav-icon">🧀</span> Producción
                    </a>
                    <a href="{{ route('plant.sales') }}" class="nav-item {{ request()->routeIs('plant.sales*') || request()->routeIs('plant.inventory*') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span> Ventas / Salidas
                    </a>
                    @endif
                </nav>
                @auth
                @php($profileRoute = match(auth()->user()->role) {
                    'productor' => 'producer.profile',
                    'acopiador' => 'collector.profile',
                    'control_calidad' => 'quality.profile',
                    'trabajador_planta' => 'plant.profile',
                    default => null,
                })
                <div class="sidebar-footer">
                    <div class="user-card">
                        @if($profileRoute)
                        <a href="{{ route($profileRoute) }}" style="display:flex; align-items:center; gap:10px; flex:1; min-width:0; text-decoration:none; color:inherit;" title="Mi perfil">
                        @else
                        <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        @endif
                            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->lastname ?? '', 0, 1)) }}</div>
                            <div class="user-info">
                                <div class="user-name">{{ auth()->user()->fullname }}</div>
                                <div class="user-role">{{ auth()->user()->roleLabel }}</div>
                            </div>
                        @if($profileRoute)
                        </a>
                        @else
                        </div>
                        @endif
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
