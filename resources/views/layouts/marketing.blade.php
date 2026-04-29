<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    @include('partials.seo.meta')
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="shortcut icon" href="/favicon.ico">
    @include('partials.seo.json-ld')
    @if (! empty($seoData))
        <script>window.SEO_DATA = @json($seoData);</script>
    @endif
    @yield('head')
</head>
<body>
    @yield('content')
</body>
</html>



