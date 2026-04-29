<title>{{ $seoData['title'] ?? trim($__env->yieldContent('title', 'Movikaa')) }}</title>
<meta name="description" content="{{ $seoData['description'] ?? trim($__env->yieldContent('meta_description', 'Marketplace automotriz Movikaa para comprar y vender autos en Costa Rica.')) }}"/>
<meta name="robots" content="{{ $seoData['robots'] ?? 'index,follow' }}"/>
<link rel="canonical" href="{{ $seoData['canonical'] ?? url()->current() }}"/>
<meta property="og:site_name" content="{{ data_get($seoData, 'og.site_name', config('app.name', 'Movikaa')) }}"/>
<meta property="og:title" content="{{ data_get($seoData, 'og.title', $seoData['title'] ?? '') }}"/>
<meta property="og:description" content="{{ data_get($seoData, 'og.description', $seoData['description'] ?? '') }}"/>
<meta property="og:type" content="{{ data_get($seoData, 'og.type', 'website') }}"/>
<meta property="og:url" content="{{ data_get($seoData, 'og.url', $seoData['canonical'] ?? url()->current()) }}"/>
<meta property="og:image" content="{{ data_get($seoData, 'og.image', asset('img/logo.png')) }}"/>
<meta name="twitter:card" content="{{ data_get($seoData, 'twitter.card', 'summary_large_image') }}"/>
<meta name="twitter:title" content="{{ data_get($seoData, 'twitter.title', $seoData['title'] ?? '') }}"/>
<meta name="twitter:description" content="{{ data_get($seoData, 'twitter.description', $seoData['description'] ?? '') }}"/>
<meta name="twitter:image" content="{{ data_get($seoData, 'twitter.image', asset('img/logo.png')) }}"/>
@if (filled(data_get($seoData, 'siteVerification.google')))
    <meta name="google-site-verification" content="{{ data_get($seoData, 'siteVerification.google') }}"/>
@endif
