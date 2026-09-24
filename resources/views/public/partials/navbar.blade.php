<nav class="navbar">
    <div class="container nav-inner">
        <a href="{{ route('home') }}" class="logo">
            <div class="logo-icon">
                <img src="{{ asset('images/logo-ecolacteos.png') }}" alt="Ecolácteos Huata">
            </div>
            <div class="logo-wordmark">
                <span class="lw-main">Ecolácteos</span>
                <span class="lw-sub">Huata</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">Nosotros</a>
            <a href="{{ route('catalog') }}" class="{{ request()->routeIs('catalog') || request()->routeIs('product.show') ? 'active' : '' }}">Productos</a>
            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contacto</a>
        </div>
        <div class="nav-cta">
            <a href="{{ route('cart.index') }}" class="btn btn-outline" title="Carrito">
                🛒 Carrito
                @php($cartCount = collect(session('cart', []))->sum())
                @if($cartCount > 0)
                <span style="background:var(--c-gold);color:var(--c-green-dark);border-radius:50%;width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;">{{ $cartCount }}</span>
                @endif
            </a>
            @auth('web')
            <a href="{{ route('home') }}" class="btn btn-outline">📊 Mi Panel</a>
            @elseauth('customer')
            <a href="{{ route('customer.account') }}" class="btn btn-outline">👤 {{ auth('customer')->user()->name }}</a>
            @else
            <a href="{{ route('login') }}" class="btn btn-outline">Iniciar Sesión</a>
            @endauth
            <a href="{{ route('catalog') }}" class="btn btn-primary">Ver Productos</a>
        </div>
    </div>
</nav>
