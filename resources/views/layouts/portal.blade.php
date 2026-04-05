<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '360Cars Portal')</title>
    <meta name="description" content="Paneles operativos buyer, seller y admin para 360Cars.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800|space-grotesk:400,500,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-shell bg-ink-950 text-white antialiased">
    <div class="portal-layout">
        <aside class="portal-sidebar" data-sidebar>
            <div class="portal-sidebar__brand">
                <a href="{{ route('home') }}" class="brand">
                    <span class="brand__mark">360</span>
                    <span class="brand__text">Cars</span>
                </a>
                <button class="menu-toggle menu-toggle--portal" type="button" aria-expanded="false" aria-controls="portal-nav" data-sidebar-toggle>
                    <span></span>
                    <span></span>
                </button>
            </div>

            <div class="portal-sidebar__content" id="portal-nav">
                <div class="portal-user">
                    <span class="muted-label">Sesion activa</span>
                    <strong>{{ auth()->user()?->name }}</strong>
                    <p>{{ auth()->user()?->email }}</p>
                </div>
                @yield('sidebar')
            </div>
        </aside>

        <div class="portal-main">
            <header class="portal-header">
                <div>
                    <p class="eyebrow">@yield('portal-eyebrow', 'Portal')</p>
                    <h1>@yield('portal-title', 'Dashboard')</h1>
                    <p class="portal-header__copy">@yield('portal-copy', 'Workspace operativo conectado a la API de 360Cars y listo para trabajo diario.')</p>
                </div>
                <div class="portal-header__actions">
                    @yield('header-actions')
                    <a href="{{ route('home') }}" class="button button--ghost">Volver al home</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="button button--ghost">Cerrar sesion</button>
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
