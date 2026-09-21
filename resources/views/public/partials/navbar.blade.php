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
            <a href="{{ route('login') }}" class="btn btn-outline">Iniciar Sesión</a>
            <a href="{{ route('catalog') }}" class="btn btn-primary">Ver Productos</a>
        </div>
    </div>
</nav>
