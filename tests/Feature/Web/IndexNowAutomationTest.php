<?php

namespace Tests\Feature\Web;

use App\Jobs\SubmitIndexNowUrls;
use App\Models\NewsPost;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IndexNowAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_indexnow_key_endpoint_returns_404_when_key_is_not_configured(): void
    {
        $this->get(route('indexnow.key'))->assertNotFound();
    }

    public function test_indexnow_key_endpoint_returns_key_when_enabled(): void
    {
        $admin = User::where('email', 'admin@movikaa.local')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.seo-settings.update'), [
            'default_title' => 'Movikaa',
            'default_description' => 'SEO',
            'title_suffix' => 'Movikaa',
            'default_og_image' => '',
            'google_site_verification' => '',
            'indexnow_enabled' => '1',
            'indexnow_key' => 'abc123',
            'indexnow_endpoint' => 'https://api.indexnow.org/indexnow',
        ]);

        $this->get(route('indexnow.key'))
            ->assertOk()
            ->assertSee('abc123');
    }

    public function test_published_vehicle_triggers_indexnow_submission_when_enabled(): void
    {
        Bus::fake();

        $admin = User::where('email', 'admin@movikaa.local')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.seo-settings.update'), [
            'default_title' => 'Movikaa',
            'default_description' => 'SEO',
            'title_suffix' => 'Movikaa',
            'default_og_image' => '',
            'google_site_verification' => '',
            'indexnow_enabled' => '1',
            'indexnow_key' => 'abc123',
            'indexnow_endpoint' => 'https://api.indexnow.org/indexnow',
        ]);

        $seller = User::where('email', 'seller@movikaa.local')->firstOrFail();

        Vehicle::query()->create([
            'user_id' => $seller->id,
            'vehicle_make_id' => 1,
            'vehicle_model_id' => 1,
            'title' => 'Auto SEO IndexNow',
            'slug' => 'auto-seo-indexnow',
            'condition' => 'used',
            'year' => 2024,
            'body_type' => 'SUV',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Automatica',
            'price' => 22000,
            'currency' => 'CRC',
            'city' => 'San Jose',
            'description' => 'Vehículo de prueba para IndexNow.',
            'status' => 'published',
            'publication_tier' => 'basic',
            'published_at' => now(),
        ]);

        Bus::assertDispatched(SubmitIndexNowUrls::class, fn (SubmitIndexNowUrls $job) => in_array(
            route('catalog.show', 'auto-seo-indexnow'),
            $job->urls,
            true
        ));
    }

    public function test_published_news_triggers_indexnow_submission_when_enabled(): void
    {
        Bus::fake();

        $admin = User::where('email', 'admin@movikaa.local')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.seo-settings.update'), [
            'default_title' => 'Movikaa',
            'default_description' => 'SEO',
            'title_suffix' => 'Movikaa',
            'default_og_image' => '',
            'google_site_verification' => '',
            'indexnow_enabled' => '1',
            'indexnow_key' => 'abc123',
            'indexnow_endpoint' => 'https://api.indexnow.org/indexnow',
        ]);

        NewsPost::query()->create([
            'user_id' => $admin->id,
            'title' => 'Noticia IndexNow',
            'slug' => 'noticia-indexnow',
            'excerpt' => 'Resumen.',
            'content' => 'Contenido.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Bus::assertDispatched(SubmitIndexNowUrls::class, fn (SubmitIndexNowUrls $job) => in_array(
            route('news.show', 'noticia-indexnow'),
            $job->urls,
            true
        ));
    }
}
