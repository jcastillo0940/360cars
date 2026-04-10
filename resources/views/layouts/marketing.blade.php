<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Movikaa')</title>
    <meta name="description" content="@yield('meta_description', 'Marketplace automotriz Movikaa para comprar y vender autos en Costa Rica.')"/>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="shortcut icon" href="/favicon.ico">
    @yield('head')
</head>
<body>
    @yield('content')
</body>
</html>



