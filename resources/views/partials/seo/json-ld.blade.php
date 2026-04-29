@if (! empty($seoData['jsonLd']))
    <script type="application/ld+json">{!! json_encode($seoData['jsonLd'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
