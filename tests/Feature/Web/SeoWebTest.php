<?php

namespace Tests\Feature\Web;

use App\Models\NewsPost;
use App\Models\Redirect;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_includes_seo_meta_tags(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('home').'"/>', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('window.SEO_DATA', false);
    }

    public function test_legal_pages_render_with_canonical_and_content(): void
    {
        $this->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('legal.terms').'"/>', false)
            ->assertSee('Terminos y condiciones');

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('legal.privacy').'"/>', false)
            ->assertSee('Politica de privacidad');

        $this->get(route('legal.cookies'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('legal.cookies').'"/>', false)
            ->assertSee('Politica de cookies');
    }

    public function test_sitemap_routes_are_not_blocked_by_user_agent_filters(): void
    {
        config()->set('security.blocked_user_agents', ['curl/']);

        $this->withHeaders([
            'User-Agent' => 'curl/8.0.1',
        ])->get(route('sitemap.index'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<sitemapindex', false);

        $this->withHeaders([
            'User-Agent' => 'curl/8.0.1',
        ])->get(route('sitemap.vehicles'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<urlset', false);

        $this->withHeaders([
            'User-Agent' => 'curl/8.0.1',
        ])->get(route('sitemap.news'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<urlset', false);
    }

    public function test_filtered_inventory_defaults_to_noindex_follow(): void
    {
        $response = $this->get(route('catalog.index', [
            'min_price' => 1000000,
            'max_price' => 5000000,
        ]));

        $response->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow"/>', false);
    }

    public function test_vehicle_detail_includes_canonical_and_schema(): void
    {
        $vehicle = Vehicle::query()->where('status', 'published')->firstOrFail();

        $response = $this->get(route('catalog.show', $vehicle->slug));

        $response->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('catalog.show', $vehicle->slug).'"/>', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Car"', false);
    }

    public function test_news_detail_uses_article_schema(): void
    {
        $author = User::where('email', 'admin@movikaa.local')->firstOrFail();
        $post = NewsPost::query()->create([
            'user_id' => $author->id,
            'title' => 'Guía SEO para autos usados',
            'slug' => 'guia-seo-autos-usados',
            'excerpt' => 'Resumen para buscadores.',
            'content' => 'Contenido editorial de prueba para validar schema Article.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('news.show', $post->slug));

        $response->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Article"', false);
    }

    public function test_sitemaps_are_available(): void
    {
        $this->get(route('sitemap.index'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<sitemapindex', false);

        $this->get(route('sitemap.vehicles'))
            ->assertOk()
            ->assertSee('<urlset', false);

        $this->get(route('sitemap.news'))
            ->assertOk()
            ->assertSee('<urlset', false);
    }

    public function test_redirects_table_can_drive_public_redirects(): void
    {
        Redirect::query()->create([
            'from_path' => '/auto-usado',
            'to_url' => route('catalog.index'),
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/auto-usado')
            ->assertRedirect(route('catalog.index'));
    }

    public function test_admin_can_save_global_seo_settings(): void
    {
        $admin = User::where('email', 'admin@movikaa.local')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.seo-settings.update'), [
                'default_title' => 'Movikaa Costa Rica',
                'default_description' => 'Inventario automotriz optimizado.',
                'title_suffix' => 'Movikaa',
                'default_og_image' => 'https://example.com/og.jpg',
                'google_site_verification' => 'test-verification',
                'index_filtered_inventory' => '1',
            ])
            ->assertRedirect(route('admin.settings').'#seo-settings');

        $this->assertDatabaseHas('app_settings', [
            'key' => 'seo.default_title',
            'value' => 'Movikaa Costa Rica',
        ]);
    }
}
