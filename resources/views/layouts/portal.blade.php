<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Movikaa | Panel')</title>
    <meta name="description" content="Panel de gesti?n de Movikaa para compradores, vendedores y administraci?n.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-shell antialiased">
    <div class="portal-layout">
        <aside class="portal-sidebar" data-sidebar>
            <div class="portal-sidebar__brand">
                <a href="{{ route('home') }}" class="portal-brand">
                    <span class="portal-brand__mark">M</span>
                    <span>
                        <strong>Movikaa</strong>
                        <small>Panel de gesti?n</small>
                    </span>
                </a>
                <button class="portal-sidebar__toggle" type="button" data-sidebar-toggle aria-expanded="false" aria-controls="portal-nav">
                    <span></span>
                    <span></span>
                </button>
            </div>

            <div class="portal-user-card">
                <span class="portal-kicker">Sesi?n activa</span>
                <strong>{{ auth()->user()?->name }}</strong>
                <p>{{ auth()->user()?->email }}</p>
            </div>

            <div class="portal-sidebar__content" id="portal-nav">
                @yield('sidebar')
            </div>
        </aside>

        <div class="portal-main">
            <header class="portal-header">
                <div>
                    <p class="portal-kicker">@yield('portal-eyebrow', 'Panel')</p>
                    <h1>@yield('portal-title', 'Gestiona tu cuenta')</h1>
                    <p class="portal-header__copy">@yield('portal-copy', 'Todo lo que necesitas para administrar tu actividad en Movikaa desde un solo lugar.')</p>
                </div>
                <div class="portal-header__actions">
                    @yield('header-actions')
                    <a href="{{ route('home') }}" class="button button--ghost">Ir al sitio</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="button button--ghost">Cerrar sesi?n</button>
                    </form>
                </div>
            </header>

            <main class="portal-content">
                @if (session('status'))
                    <div class="flash-banner flash-banner--success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="flash-banner flash-banner--error">
                        <strong>Revisa los datos enviados.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
