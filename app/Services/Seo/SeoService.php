<?php

namespace App\Services\Seo;

use App\Models\NewsPost;
use App\Models\Vehicle;
use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Http\Request;

class SeoService
{
    public function __construct(
        private readonly ValuationSettingsService $settings,
    ) {
    }

    public function defaults(?Request $request = null): array
    {
        $siteName = $this->siteName();
        $title = (string) $this->settings->get('seo.default_title', $siteName);
        $description = (string) $this->settings->get(
            'seo.default_description',
            'Marketplace automotriz para comprar y vender autos en Costa Rica con inventario real, tasación y contacto directo.'
        );

        return $this->build([
            'title' => $title,
            'description' => $description,
            'canonical' => $this->currentUrl($request),
            'ogImage' => $this->defaultOgImage(),
            'robots' => 'index,follow',
            'type' => 'website',
            'schema' => $this->organizationSchema($request),
        ], $request);
    }

    public function forHome(?Request $request = null): array
    {
        return $this->build([
            'title' => 'Encuentra tu próximo auto en Costa Rica | '.$this->siteName(),
            'description' => 'Explora autos en venta en Costa Rica, descubre marcas, compara opciones y publica tu vehículo en '.$this->siteName().'.',
            'canonical' => route('home'),
            'ogImage' => $this->defaultOgImage(),
            'robots' => 'index,follow',
            'type' => 'website',
            'schema' => $this->organizationSchema($request),
        ], $request);
    }

    public function forBrandsIndex(?Request $request = null): array
    {
        return $this->build([
            'title' => 'Marcas de autos en Costa Rica | '.$this->siteName(),
            'description' => 'Explora marcas de autos, cantidad de publicaciones disponibles y modelos activos dentro del marketplace de '.$this->siteName().'.',
            'canonical' => route('brands.index'),
            'robots' => 'index,follow',
            'type' => 'website',
        ], $request);
    }

    public function forCatalogIndex(array $filters, ?Request $request = null): array
    {
        $request ??= request();
        $segments = collect([
            $filters['make'] ?: null,
            $filters['model'] ?: null,
            $filters['province'] ?: null,
        ])->filter()->values();

        $title = $segments->isNotEmpty()
            ? sprintf('%s en venta | %s', $segments->implode(' '), $this->siteName())
            : 'Inventario de autos en venta | '.$this->siteName();

        $description = $segments->isNotEmpty()
            ? 'Descubre publicaciones activas de '.$segments->implode(' ').' en Costa Rica, con filtros de precio, año y equipamiento.'
            : 'Explora el inventario público de autos en Costa Rica con filtros por marca, modelo, provincia, precio, año y características.';

        $allowFilteredIndexing = (bool) $this->settings->get('seo.index_filtered_inventory', false);
        $hasComplexFilters = count(array_filter([
            $filters['features'] !== [],
            $filters['min_price'],
            $filters['max_price'],
            $filters['min_year'],
            $filters['max_year'],
            $filters['offers'],
            $filters['featured'],
        ])) > 0;

        $canonical = route('catalog.index');
        if ($segments->isNotEmpty()) {
            $query = array_filter([
                'make' => $filters['make'] ?: null,
                'model' => $filters['model'] ?: null,
                'province' => $filters['province'] ?: null,
            ]);
            $canonical = route('catalog.index', $query);
        }

        return $this->build([
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => ($hasComplexFilters && ! $allowFilteredIndexing) ? 'noindex,follow' : 'index,follow',
            'type' => 'website',
        ], $request);
    }

    public function forVehicle(Vehicle $vehicle, bool $isAvailable = true, ?Request $request = null): array
    {
        $request ??= request();
        $vehicle->loadMissing(['make', 'model', 'seo']);

        $manual = $vehicle->seo;
        $title = $manual?->title ?: trim(collect([
            $vehicle->make?->name,
            $vehicle->model?->name,
            $vehicle->year,
            'en venta',
        ])->filter()->implode(' ')).' | '.$this->siteName();

        $description = $manual?->description
            ?: $this->truncateDescription(
                trim(collect([
                    $vehicle->title,
                    $vehicle->city ? 'Disponible en '.$vehicle->city.'.' : null,
                    $vehicle->description,
                ])->filter()->implode(' '))
            );

        $canonical = $manual?->canonical_url ?: route('catalog.show', $vehicle->slug);
        $ogImage = $manual?->og_image ?: $this->vehicleImage($vehicle);
        $robots = $manual?->robots ?: ($isAvailable ? 'index,follow' : 'noindex,follow');

        return $this->build([
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'ogImage' => $ogImage,
            'robots' => $robots,
            'type' => 'product',
            'schema' => $this->vehicleSchema($vehicle, $canonical, $ogImage, $isAvailable),
        ], $request);
    }

    public function forComparisons(?Request $request = null): array
    {
        return $this->build([
            'title' => 'Comparador de autos | '.$this->siteName(),
            'description' => 'Compara varios autos publicados en '.$this->siteName().' para revisar precio, año, kilometraje y equipamiento en una sola vista.',
            'canonical' => route('buyer.comparisons.index'),
            'robots' => 'noindex,follow',
            'type' => 'website',
        ], $request);
    }

    public function forNewsIndex(?Request $request = null): array
    {
        return $this->build([
            'title' => 'Noticias y blog automotriz | '.$this->siteName(),
            'description' => 'Lee noticias, guías y contenido editorial sobre compra, venta y mercado automotriz en Costa Rica.',
            'canonical' => route('news.index'),
            'robots' => 'index,follow',
            'type' => 'website',
        ], $request);
    }

    public function forNewsPost(NewsPost $post, ?Request $request = null): array
    {
        $request ??= request();
        $post->loadMissing('seo', 'author');

        $manual = $post->seo;
        $title = $manual?->title ?: $post->meta_title ?: ($post->title.' | '.$this->siteName());
        $description = $manual?->description ?: $post->meta_description ?: $this->truncateDescription($post->excerpt ?: $post->content);
        $canonical = $manual?->canonical_url ?: route('news.show', $post->slug);
        $ogImage = $manual?->og_image ?: ($post->cover_image_url ?: $this->defaultOgImage());
        $robots = $manual?->robots ?: 'index,follow';

        return $this->build([
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'ogImage' => $ogImage,
            'robots' => $robots,
            'type' => 'article',
            'schema' => $this->newsSchema($post, $canonical, $ogImage),
        ], $request);
    }

    public function forValuation(?Request $request = null): array
    {
        return $this->build([
            'title' => 'Tasador de vehículos en Costa Rica | '.$this->siteName(),
            'description' => 'Calcula una estimación de mercado para tu vehículo en Costa Rica usando año, condición, kilometraje y configuración técnica.',
            'canonical' => route('valuation.index'),
            'robots' => 'index,follow',
            'type' => 'website',
        ], $request);
    }

    public function forSellerOnboarding(?Request $request = null): array
    {
        return $this->build([
            'title' => 'Vende tu auto en Costa Rica | '.$this->siteName(),
            'description' => 'Publica tu auto paso a paso, crea tu cuenta al final y empieza a recibir contactos desde '.$this->siteName().'.',
            'canonical' => route('seller.onboarding.create'),
            'robots' => 'index,follow',
            'type' => 'website',
        ], $request);
    }

    public function forLegal(string $title, string $description, string $canonical, ?Request $request = null): array
    {
        return $this->build([
            'title' => $title.' | '.$this->siteName(),
            'description' => $description,
            'canonical' => $canonical,
            'robots' => 'index,follow',
            'type' => 'website',
        ], $request);
    }

    public function settingsSnapshot(): array
    {
        return [
            'default_title' => (string) $this->settings->get('seo.default_title', $this->siteName()),
            'default_description' => (string) $this->settings->get('seo.default_description', 'Marketplace automotriz para comprar y vender autos en Costa Rica.'),
            'title_suffix' => (string) $this->settings->get('seo.title_suffix', $this->siteName()),
            'default_og_image' => (string) ($this->settings->get('seo.default_og_image', '') ?: asset('img/logo.png')),
            'google_site_verification' => (string) $this->settings->get('seo.google_site_verification', ''),
            'index_filtered_inventory' => (bool) $this->settings->get('seo.index_filtered_inventory', false),
            'indexnow_enabled' => (bool) $this->settings->get('seo.indexnow_enabled', false),
            'indexnow_key' => (string) $this->settings->get('seo.indexnow_key', ''),
            'indexnow_endpoint' => (string) $this->settings->get('seo.indexnow_endpoint', 'https://api.indexnow.org/indexnow'),
        ];
    }

    public function build(array $data, ?Request $request = null): array
    {
        $request ??= request();
        $siteName = $this->siteName();
        $title = trim((string) ($data['title'] ?? $siteName));
        $description = $this->truncateDescription((string) ($data['description'] ?? ''));
        $canonical = (string) ($data['canonical'] ?? $this->currentUrl($request));
        $ogImage = (string) ($data['ogImage'] ?? $this->defaultOgImage());
        $robots = (string) ($data['robots'] ?? 'index,follow');
        $type = (string) ($data['type'] ?? 'website');

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $robots,
            'og' => [
                'title' => $title,
                'description' => $description,
                'url' => $canonical,
                'image' => $ogImage,
                'type' => $type,
                'site_name' => $siteName,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $title,
                'description' => $description,
                'image' => $ogImage,
            ],
            'jsonLd' => $data['schema'] ?? null,
            'siteVerification' => [
                'google' => (string) $this->settings->get('seo.google_site_verification', ''),
            ],
        ];
    }

    private function currentUrl(?Request $request = null): string
    {
        $request ??= request();

        return $request->fullUrl();
    }

    private function siteName(): string
    {
        return (string) $this->settings->get('seo.title_suffix', config('app.name', 'Movikaa'));
    }

    private function defaultOgImage(): string
    {
        return (string) ($this->settings->get('seo.default_og_image', '') ?: asset('img/logo.png'));
    }

    private function truncateDescription(string $value, int $limit = 160): string
    {
        $value = trim(preg_replace('/\s+/', ' ', strip_tags($value)) ?? '');

        if ($value === '') {
            return (string) $this->settings->get('seo.default_description', 'Marketplace automotriz en Costa Rica.');
        }

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit - 1)).'…';
    }

    private function vehicleImage(Vehicle $vehicle): string
    {
        $primary = $vehicle->media
            ->sortBy([['is_primary', 'desc'], ['sort_order', 'asc']])
            ->first();

        if ($primary?->path) {
            return \Storage::disk($primary->disk ?: 'public')->url($primary->path);
        }

        return $this->defaultOgImage();
    }

    private function organizationSchema(?Request $request = null): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->siteName(),
            'url' => route('home'),
            'logo' => asset('img/logo.png'),
        ];
    }

    private function vehicleSchema(Vehicle $vehicle, string $canonical, string $image, bool $isAvailable): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Car',
            'name' => $vehicle->title,
            'brand' => $vehicle->make?->name,
            'model' => $vehicle->model?->name,
            'vehicleModelDate' => (string) $vehicle->year,
            'fuelType' => $vehicle->fuel_type,
            'vehicleTransmission' => $vehicle->transmission,
            'mileageFromOdometer' => $vehicle->mileage ? [
                '@type' => 'QuantitativeValue',
                'value' => $vehicle->mileage,
                'unitCode' => strtoupper((string) ($vehicle->mileage_unit ?: 'KM')),
            ] : null,
            'image' => $image,
            'description' => $this->truncateDescription($vehicle->description ?: $vehicle->title, 220),
            'url' => $canonical,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => strtoupper((string) $vehicle->currency),
                'price' => (float) $vehicle->price,
                'availability' => $isAvailable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => $canonical,
            ],
        ];
    }

    private function newsSchema(NewsPost $post, string $canonical, string $image): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $this->truncateDescription($post->excerpt ?: $post->content, 220),
            'image' => $image,
            'datePublished' => optional($post->published_at)?->toIso8601String(),
            'dateModified' => optional($post->updated_at)?->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author?->name ?: 'Equipo '.$this->siteName(),
            ],
            'mainEntityOfPage' => $canonical,
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->siteName(),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('img/logo.png'),
                ],
            ],
        ];
    }
}
