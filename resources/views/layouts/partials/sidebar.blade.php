@php
    $user = auth()->user();
    $role = $user?->role;

    $dashboardRoute = match ($role) {
        'admin', 'gerente' => 'admin.dashboard',
        'acopiador' => 'collector.dashboard',
        'control_calidad' => 'quality.dashboard',
        'trabajador_planta' => 'plant.dashboard',
        'productor' => 'producer.dashboard',
        default => null,
    };

    $unreadCount = $role === 'productor'
        ? \App\Models\Notification::visibleForUser($user)->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))->count()
        : 0;

    /**
     * Menú por rol: cada sección tiene ítems con su ruta, patrones de ruta activa e ícono.
     *
     * @var array<int, array{title: string, items: array<int, array{label: string, route: string, active: array<int, string>, icon: string, badge?: int}>}> $menu
     */
    $menu = match ($role) {
        'admin' => [
            ['title' => 'Gestión', 'items' => [
                ['label' => 'Usuarios', 'route' => 'admin.users', 'active' => ['admin.users*'], 'icon' => 'users'],
                ['label' => 'Acopiadores', 'route' => 'admin.collectors', 'active' => ['admin.collectors*'], 'icon' => 'truck'],
                ['label' => 'Productores', 'route' => 'admin.producers', 'active' => ['admin.producers*'], 'icon' => 'sprout'],
                ['label' => 'Clientes', 'route' => 'admin.customers', 'active' => ['admin.customers*'], 'icon' => 'bag'],
                ['label' => 'Pagos', 'route' => 'admin.payments', 'active' => ['admin.payments*'], 'icon' => 'card'],
                ['label' => 'Insumos', 'route' => 'admin.ingredients', 'active' => ['admin.ingredients*'], 'icon' => 'flask'],
                ['label' => 'Productos', 'route' => 'admin.products', 'active' => ['admin.products*'], 'icon' => 'package'],
            ]],
            ['title' => 'Calidad', 'items' => [
                ['label' => 'Historial de Análisis', 'route' => 'admin.quality', 'active' => ['admin.quality'], 'icon' => 'microscope'],
                ['label' => 'Sanciones', 'route' => 'admin.sanctions', 'active' => ['admin.sanctions*'], 'icon' => 'ban'],
            ]],
            ['title' => 'Sistema', 'items' => [
                ['label' => 'Quejas', 'route' => 'admin.complaints', 'active' => ['admin.complaints*'], 'icon' => 'message'],
                ['label' => 'Avisos', 'route' => 'admin.notifications', 'active' => ['admin.notifications*'], 'icon' => 'megaphone'],
                ['label' => 'Actualizar Precio', 'route' => 'admin.price', 'active' => ['admin.price*'], 'icon' => 'dollar'],
                ['label' => 'Configuración', 'route' => 'admin.config', 'active' => ['admin.config*'], 'icon' => 'settings'],
            ]],
        ],
        'gerente' => [
            ['title' => 'Gestión', 'items' => [
                ['label' => 'Productores', 'route' => 'admin.producers', 'active' => ['admin.producers*'], 'icon' => 'sprout'],
                ['label' => 'Acopio', 'route' => 'admin.deliveries', 'active' => ['admin.deliveries*'], 'icon' => 'milk'],
                ['label' => 'Calidad', 'route' => 'admin.quality', 'active' => ['admin.quality*'], 'icon' => 'microscope'],
                ['label' => 'Pagos', 'route' => 'admin.payments', 'active' => ['admin.payments*'], 'icon' => 'card'],
                ['label' => 'Producción', 'route' => 'admin.production', 'active' => ['admin.production'], 'icon' => 'factory'],
                ['label' => 'Insumos', 'route' => 'admin.ingredients', 'active' => ['admin.ingredients*'], 'icon' => 'flask'],
                ['label' => 'Ventas', 'route' => 'admin.sales', 'active' => ['admin.sales'], 'icon' => 'bag'],
                ['label' => 'Clientes', 'route' => 'admin.customers', 'active' => ['admin.customers*'], 'icon' => 'users'],
            ]],
        ],
        'productor' => [
            ['title' => 'Mi Cuenta', 'items' => [
                ['label' => 'Mis Entregas', 'route' => 'producer.deliveries', 'active' => ['producer.deliveries*'], 'icon' => 'milk'],
                ['label' => 'Mis Pagos', 'route' => 'producer.payments', 'active' => ['producer.payments*', 'producer.payment-*'], 'icon' => 'wallet'],
                ['label' => 'Calidad', 'route' => 'producer.quality', 'active' => ['producer.quality*'], 'icon' => 'shield'],
                ['label' => 'Quejas', 'route' => 'producer.complaints', 'active' => ['producer.complaints*'], 'icon' => 'message'],
                ['label' => 'Notificaciones', 'route' => 'producer.notifications', 'active' => ['producer.notifications*'], 'icon' => 'bell', 'badge' => $unreadCount],
            ]],
        ],
        'acopiador' => [
            ['title' => 'Acopio', 'items' => [
                ['label' => 'Registrar Acopio', 'route' => 'collector.delivery-create', 'active' => ['collector.delivery-create', 'collector.delivery-store'], 'icon' => 'milk'],
                ['label' => 'Mis Productores', 'route' => 'collector.producers', 'active' => ['collector.producers*'], 'icon' => 'users'],
                ['label' => 'Historial Jornada', 'route' => 'collector.journal', 'active' => ['collector.journal*'], 'icon' => 'clipboard'],
            ]],
            ['title' => 'Mi Cuenta', 'items' => [
                ['label' => 'Mis Pagos', 'route' => 'collector.payments', 'active' => ['collector.payments*', 'collector.payment-*'], 'icon' => 'wallet'],
            ]],
        ],
        'control_calidad' => [
            ['title' => 'Laboratorio', 'items' => [
                ['label' => 'Nuevo Análisis', 'route' => 'quality.report-create', 'active' => ['quality.report-create', 'quality.report-store'], 'icon' => 'plus'],
                ['label' => 'Historial', 'route' => 'quality.reports', 'active' => ['quality.reports', 'quality.report-show'], 'icon' => 'clipboard'],
            ]],
        ],
        'trabajador_planta' => [
            ['title' => 'Planta', 'items' => [
                ['label' => 'Producción', 'route' => 'plant.production', 'active' => ['plant.production*', 'plant.batches*', 'plant.recipe-create'], 'icon' => 'factory'],
                ['label' => 'Ventas / Salidas', 'route' => 'plant.sales', 'active' => ['plant.sales*', 'plant.inventory*'], 'icon' => 'bag'],
            ]],
        ],
        default => [],
    };

    $profileRoute = match ($role) {
        'productor' => 'producer.profile',
        'acopiador' => 'collector.profile',
        'control_calidad' => 'quality.profile',
        'trabajador_planta' => 'plant.profile',
        default => null,
    };
@endphp

<aside class="sidebar" id="sidebar" aria-label="Menú principal">
    <div class="sidebar-logo">
        <a href="{{ $dashboardRoute ? route($dashboardRoute) : route('home') }}" class="sidebar-brand">
            <span class="sidebar-logo-icon">
                <img src="{{ asset('images/logo-ecolacteos.png') }}" alt="">
            </span>
            <span>
                <span class="sidebar-logo-text">Ecolácteos<span> Huata</span></span>
                <span class="sidebar-logo-brand">Sistema de Gestión</span>
            </span>
        </a>
        <button type="button" class="sidebar-close" data-sidebar-close aria-label="Cerrar menú">
            <x-icon name="close" />
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a href="{{ $dashboardRoute ? route($dashboardRoute) : route('home') }}"
           class="nav-item {{ request()->routeIs('*.dashboard') ? 'active' : '' }}"
           @if(request()->routeIs('*.dashboard')) aria-current="page" @endif>
            <x-icon name="dashboard" class="nav-icon" />
            <span class="nav-label">Dashboard</span>
        </a>

        @foreach($menu as $section)
            <div class="nav-section">{{ $section['title'] }}</div>
            @foreach($section['items'] as $item)
                @php($isActive = request()->routeIs(...$item['active']))
                <a href="{{ route($item['route']) }}" class="nav-item {{ $isActive ? 'active' : '' }}" @if($isActive) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" class="nav-icon" />
                    <span class="nav-label">{{ $item['label'] }}</span>
                    @if(($item['badge'] ?? 0) > 0)
                        <span class="nav-badge">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    @if($user)
        <div class="sidebar-footer">
            <div class="user-card">
                @if($profileRoute)
                    <a href="{{ route($profileRoute) }}" class="user-link" title="Mi perfil">
                @else
                    <div class="user-link">
                @endif
                    <span class="user-avatar">{{ strtoupper(substr($user->name, 0, 1).substr($user->lastname ?? '', 0, 1)) }}</span>
                    <span class="user-info">
                        <span class="user-name">{{ $user->fullname }}</span>
                        <span class="user-role">{{ $user->roleLabel }}</span>
                    </span>
                    @if($profileRoute)
                        <x-icon name="chevron-right" class="user-chevron" />
                    @endif
                @if($profileRoute)
                    </a>
                @else
                    </div>
                @endif
            </div>
            <button type="button" class="logout-btn" data-logout-open>
                <x-icon name="logout" />
                Cerrar sesión
            </button>
        </div>
    @endif
</aside>
<div class="sidebar-backdrop" data-sidebar-close></div>

@if($user)
<dialog class="logout-dialog" id="logoutDialog" aria-labelledby="logoutTitle">
    <div class="logout-dialog-icon"><x-icon name="logout" /></div>
    <h2 id="logoutTitle">¿Cerrar sesión?</h2>
    <p>Saldrás de tu cuenta, <strong>{{ $user->name }}</strong>. Para volver a entrar necesitarás tu correo o DNI y tu contraseña.</p>
    <form method="POST" action="{{ route('logout') }}" class="logout-dialog-actions">
        @csrf
        <button type="button" class="btn btn-ghost" data-logout-close>Cancelar</button>
        <button type="submit" class="btn btn-danger">Sí, cerrar sesión</button>
    </form>
</dialog>
@endif
