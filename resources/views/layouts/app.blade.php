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
            @include('layouts.partials.sidebar')
        @show

        <main class="main-content">
            @if(session()->has(\App\Http\Controllers\ImpersonationController::SESSION_KEY))
                <div class="alert alert-impersonation">
                    👁️ <span>Estás viendo el sistema como <strong>{{ auth()->user()->fullname }}</strong> ({{ auth()->user()->roleLabel }}).</span>
                    <form method="POST" action="{{ route('impersonation.stop') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">↩ Volver a mi cuenta de administrador</button>
                    </form>
                </div>
            @endif

            <div class="top-bar">
                <button type="button" class="menu-toggle" data-sidebar-open aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">
                    <x-icon name="menu" />
                </button>
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
