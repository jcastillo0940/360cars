<?php

namespace Tests\Feature\Web;

use App\Models\Plan;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PortalWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }


    public function test_public_valuation_page_renders(): void
    {
        $this->get(route('valuation.index'))
            ->assertOk()
            ->assertSee('valuation-react', false)
            ->assertSee('submitUrl', false);
    }

    public function test_guest_can_generate_and_view_a_vehicle_valuation(): void
    {
        $response = $this->post(route('valuation.store'), [
            'vehicle_make_id' => 1,
            'vehicle_model_id' => 1,
            'year' => 2022,
            'condition' => 'used',
            'body_type' => 'SUV',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Automatica',
            'drivetrain' => 'AWD',
            'mileage' => 42000,
            'engine_size' => 2.5,
            'city' => 'San Jose',
            'price_reference' => 14500000,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('vehicle_valuations', 1);

        $valuation = \App\Models\VehicleValuation::query()->firstOrFail();

        $this->get(route('valuation.show', $valuation->share_token))
            ->assertOk()
            ->assertSee($valuation->share_token)
            ->assertSee('Toyota Corolla 2022');
    }

    public function test_admin_can_toggle_valuation_ai_setting(): void
    {
        $admin = User::where('email', 'admin@360cars.local')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.valuation-ai.update'), [
                'valuation_ai_enabled' => '1',
            ])
            ->assertRedirect(route('admin.dashboard').'#valuation-ai');

        $this->assertDatabaseHas('app_settings', [
            'key' => 'valuation.ai_enabled',
            'value' => '1',
            'type' => 'boolean',
        ]);
    }
    public function test_guest_is_redirected_to_login_for_seller_dashboard(): void
    {
        $this->get('/seller')->assertRedirect('/login');
    }

    public function test_public_seller_onboarding_page_renders(): void
    {
        $this->get(route('seller.onboarding.create'))
            ->assertOk()
            ->assertSee('Registra tu auto primero. Tu cuenta se crea al final.')
            ->assertSee('Ubica tu auto en Costa Rica');
    }

    public function test_guest_can_complete_seller_onboarding_with_phone_only(): void
    {
        $response = $this->post(route('seller.onboarding.store'), [
            'vehicle_make_id' => 1,
            'vehicle_model_id' => 1,
            'year' => 2022,
            'trim' => 'EX',
            'condition' => 'used',
            'body_type' => 'SUV',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Automatica',
            'drivetrain' => 'AWD',
            'mileage' => 42000,
            'engine' => '2.5L',
            'engine_size' => 2.5,
            'exterior_color' => 'Blanco',
            'interior_color' => 'Negro',
            'doors' => 5,
            'seats' => 5,
            'price' => 28900,
            'currency' => 'CRC',
            'city' => 'San Jose',
            'state' => 'San Jose',
            'country_code' => 'CR',
            'latitude' => '9.9325',
            'longitude' => '-84.0796',
            'location_label' => 'San Jose, Costa Rica',
            'description' => 'SUV familiar en excelente estado, lista para uso diario y viajes largos por Costa Rica.',
            'features_list' => 'Camara, CarPlay, Sensores',
            'seller_name' => 'Carlos Vendedor',
            'contact_phone' => '60001234',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'accept_terms' => '1',
            'photo_front' => UploadedFile::fake()->image('front.jpg'),
            'photo_rear' => UploadedFile::fake()->image('rear.jpg'),
            'photo_left' => UploadedFile::fake()->image('left.jpg'),
            'photo_right' => UploadedFile::fake()->image('right.jpg'),
            'photo_driver_interior' => UploadedFile::fake()->image('driver.jpg'),
            'photo_passenger_interior' => UploadedFile::fake()->image('passenger.jpg'),
        ]);

        $response->assertRedirect(route('seller.dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'Carlos Vendedor',
            'phone' => '60001234',
            'account_type' => 'seller',
        ]);

        $this->assertDatabaseHas('vehicles', [
            'year' => 2022,
            'city' => 'San Jose',
            'status' => 'published',
            'publication_tier' => 'basic',
        ]);
    }

    public function test_user_can_login_through_web_and_reach_seller_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'seller@360cars.local',
            'password' => 'password',
        ]);

        $response->assertRedirect('/seller');
        $this->followRedirects($response)->assertSee('Visibilidad y monetizacion');
    }

    public function test_admin_dashboard_renders_real_sections(): void
    {
        $admin = User::where('email', 'admin@360cars.local')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Pagos recientes')
            ->assertSee('Ultimos listings')
            ->assertSee('Planes disponibles');
    }

    public function test_seller_can_create_vehicle_from_web_form(): void
    {
        $seller = User::where('email', 'seller@360cars.local')->firstOrFail();

        $this->actingAs($seller)
            ->post('/seller/vehicles', [
                'vehicle_make_id' => 1,
                'vehicle_model_id' => 1,
                'title' => 'Demo Web Listing',
                'condition' => 'used',
                'year' => 2023,
                'body_type' => 'SUV',
                'fuel_type' => 'Gasolina',
                'transmission' => 'Automatica',
                'price' => 25500,
                'description' => 'Esta publicacion fue creada desde el formulario web del seller portal.',
                'publication_tier' => 'basic',
                'status' => 'draft',
            ])
            ->assertRedirect('/seller');

        $this->assertDatabaseHas('vehicles', [
            'title' => 'Demo Web Listing',
            'user_id' => $seller->id,
        ]);
    }

    public function test_seller_can_activate_plan_in_sandbox_from_web(): void
    {
        $seller = User::where('email', 'seller@360cars.local')->firstOrFail();
        $plan = Plan::where('slug', 'estandar')->firstOrFail();

        $this->actingAs($seller)
            ->post('/seller/billing/subscribe', [
                'plan_slug' => $plan->slug,
            ])
            ->assertRedirect(route('seller.dashboard').'#billing');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $seller->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    public function test_public_catalog_pages_render(): void
    {
        $vehicle = Vehicle::query()->where('status', 'published')->firstOrFail();

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('catalog-react', false)
            ->assertSee($vehicle->title);

        $this->get(route('catalog.show', $vehicle->slug))
            ->assertOk()
            ->assertSee('vehicle-show-react', false)
            ->assertSee($vehicle->title);
    }

    public function test_home_receives_paid_featured_inventory(): void
    {
        $vehicle = Vehicle::query()->where('status', 'published')->where('is_featured', true)->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('featuredPaidVehicles', false)
            ->assertSee($vehicle->title);
    }

    public function test_basic_seller_can_refresh_expired_listing_for_30_more_days(): void
    {
        $seller = User::query()->create([
            'name' => 'Seller Basico',
            'email' => 'seller-basic@360cars.local',
            'password' => 'password',
            'account_type' => 'seller',
            'country_code' => 'CR',
        ]);

        $vehicle = Vehicle::query()->create([
            'user_id' => $seller->id,
            'vehicle_make_id' => 1,
            'vehicle_model_id' => 1,
            'title' => 'Basico Vencido',
            'slug' => 'basico-vencido',
            'condition' => 'used',
            'year' => 2021,
            'body_type' => 'SUV',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Automatica',
            'price' => 19900,
            'currency' => 'CRC',
            'city' => 'San Jose',
            'state' => 'San Jose',
            'country_code' => 'CR',
            'description' => 'Anuncio basico que debe poder renovarse desde el seller portal.',
            'status' => 'published',
            'publication_tier' => 'basic',
            'published_at' => now()->subDays(40),
            'expires_at' => now()->subDay(),
            'metadata' => ['plan_slug' => 'basico', 'plan_name' => 'Basico', 'plan_is_paid' => false],
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.vehicles.refresh-basic', $vehicle))
            ->assertRedirect();

        $vehicle->refresh();

        $this->assertSame('published', $vehicle->status);
        $this->assertNotNull($vehicle->published_at);
        $this->assertTrue($vehicle->expires_at->isFuture());
        $this->assertGreaterThanOrEqual(29, now()->diffInDays($vehicle->expires_at));
        $this->assertSame(1, (int) data_get($vehicle->metadata, 'basic_refresh_count', 0));
        $this->assertNotEmpty(data_get($vehicle->metadata, 'last_basic_refresh_at'));
    }

    public function test_buyer_can_favorite_compare_and_message_from_web_routes(): void
    {
        $buyer = User::where('email', 'buyer@360cars.local')->firstOrFail();
        $vehicle = Vehicle::query()->where('status', 'published')->firstOrFail();

        $this->actingAs($buyer)
            ->postJson(route('buyer.favorites.store', $vehicle))
            ->assertOk()
            ->assertJson(['favorited' => true]);

        $this->actingAs($buyer)
            ->postJson(route('buyer.comparisons.store', $vehicle))
            ->assertOk()
            ->assertJson(['compared' => true]);

        $this->actingAs($buyer)
            ->postJson(route('buyer.saved-searches.store'), [
                'name' => 'SUV en San Jose',
                'filters' => ['make' => 'Toyota', 'city' => 'San Jose'],
                'notification_frequency' => 'instant',
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $this->actingAs($buyer)
            ->postJson(route('buyer.conversations.store', $vehicle), [
                'body' => 'Hola, me interesa este vehiculo y quisiera coordinar una visita.',
            ])
            ->assertOk()
            ->assertJson(['sent' => true]);

        $this->actingAs($buyer)
            ->get('/buyer')
            ->assertOk()
            ->assertSee('Autos que te interesan')
            ->assertSee($vehicle->title);
    }
}


