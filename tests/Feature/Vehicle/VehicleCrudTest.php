<?php

namespace Tests\Feature\Vehicle;

use App\Models\LifestyleCategory;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMake;
use App\Models\VehicleMedia;
use App\Models\VehicleModel;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehicleCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('paypal.client_id', 'test-client');
        config()->set('paypal.client_secret', 'test-secret');
        config()->set('paypal.webhook_id', 'WH-TEST-ID');
        config()->set('paypal.return_url', 'https://example.com/paypal/return');
        config()->set('paypal.cancel_url', 'https://example.com/paypal/cancel');
        Storage::fake('public');
        $this->seed([CatalogSeeder::class, PlanSeeder::class]);
    }

    public function test_public_plans_are_listed(): void
    {
        $this->getJson('/api/v1/plans')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'basico'])
            ->assertJsonFragment(['slug' => 'agencia-pro']);
    }

    public function test_seller_can_subscribe_and_capabilities_update(): void
    {
        $seller = User::factory()->create(['account_type' => 'seller']);
        $token = $seller->createToken('seller-web')->plainTextToken;

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/my/subscription', [
            'plan_slug' => 'premium',
            'provider' => 'sandbox',
            'payment_method' => 'sandbox',
            'activate_now' => true,
        ])->assertCreated()
            ->assertJsonPath('subscription.plan.slug', 'premium')
            ->assertJsonPath('transaction.status', 'paid');

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/my/publication-capabilities')
            ->assertOk()
            ->assertJsonPath('plan.slug', 'premium');
    }

    public function test_seller_can_create_and_capture_paypal_order_for_plan(): void
    {
        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'paypal-access-token',
                'token_type' => 'Bearer',
            ], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPALORDER123',
                'status' => 'CREATED',
                'links' => [
                    ['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPALORDER123'],
                ],
            ], 201),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/PAYPALORDER123/capture' => Http::response([
                'id' => 'PAYPALORDER123',
                'status' => 'COMPLETED',
                'purchase_units' => [
                    ['payments' => ['captures' => [['id' => 'CAPTURE123', 'status' => 'COMPLETED']]]],
                ],
            ], 201),
        ]);

        $seller = User::factory()->create(['account_type' => 'seller']);
        $token = $seller->createToken('seller-web')->plainTextToken;

        $create = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/my/subscription/paypal/create-order', [
            'plan_slug' => 'premium',
        ]);

        $create->assertCreated()
            ->assertJsonPath('paypal_order_id', 'PAYPALORDER123')
            ->assertJsonPath('transaction.status', 'pending');

        $this->assertDatabaseHas('transactions', [
            'provider' => 'paypal',
            'external_reference' => 'PAYPALORDER123',
            'status' => 'pending',
        ]);

        $capture = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/my/subscription/paypal/capture-order', [
            'paypal_order_id' => 'PAYPALORDER123',
        ]);

        $capture->assertOk()
            ->assertJsonPath('transaction.status', 'paid')
            ->assertJsonPath('subscription.plan.slug', 'premium');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $seller->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('transactions', [
            'provider' => 'paypal',
            'external_reference' => 'PAYPALORDER123',
            'status' => 'paid',
        ]);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/v1/oauth2/token'));
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/v2/checkout/orders'));
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/capture'));
    }

    public function test_paypal_webhook_completes_pending_transaction_after_signature_verification(): void
    {
        $seller = User::factory()->create(['account_type' => 'seller']);
        $plan = Plan::where('slug', 'premium')->firstOrFail();

        Transaction::create([
            'user_id' => $seller->id,
            'plan_id' => $plan->id,
            'provider' => 'paypal',
            'payment_method' => 'paypal',
            'status' => 'pending',
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'external_reference' => 'ORDER-WEBHOOK-1',
            'payload' => ['plan_slug' => $plan->slug],
        ]);

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'paypal-access-token',
                'token_type' => 'Bearer',
            ], 200),
            'https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'SUCCESS',
            ], 200),
        ]);

        $payload = [
            'id' => 'WH-EVENT-1',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE-WEBHOOK-1',
                'status' => 'COMPLETED',
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => 'ORDER-WEBHOOK-1',
                    ],
                ],
            ],
        ];

        $this->withHeaders([
            'PayPal-Transmission-Id' => 'transmission-id',
            'PayPal-Transmission-Time' => now()->toIso8601String(),
            'PayPal-Transmission-Sig' => 'signature',
            'PayPal-Cert-Url' => 'https://api-m.sandbox.paypal.com/certs/cert.pem',
            'PayPal-Auth-Algo' => 'SHA256withRSA',
        ])->postJson('/api/v1/paypal/webhook', $payload)
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'provider' => 'paypal',
            'external_reference' => 'ORDER-WEBHOOK-1',
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $seller->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    public function test_seller_can_create_vehicle_listing_with_optimized_images(): void
    {
        $seller = $this->sellerWithPlan('premium');
        $token = $seller->createToken('seller-web')->plainTextToken;
        $make = VehicleMake::where('slug', 'toyota')->firstOrFail();
        $model = VehicleModel::where('vehicle_make_id', $make->id)->where('slug', 'rav4')->firstOrFail();
        $categories = LifestyleCategory::query()->pluck('id')->take(2)->all();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/v1/my/vehicles', [
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'title' => 'Toyota RAV4 Adventure',
            'condition' => 'used',
            'year' => 2023,
            'body_type' => 'SUV',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Automatica',
            'mileage' => 12000,
            'price' => 31500,
            'description' => 'SUV impecable con mantenimiento al dia y lista para venta inmediata.',
            'status' => 'published',
            'publication_tier' => 'premium',
            'features' => ['camara', 'sunroof'],
            'lifestyle_category_ids' => $categories,
            'images' => [
                UploadedFile::fake()->image('front.jpg', 2400, 1600),
                UploadedFile::fake()->image('rear.png', 1800, 1200),
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Toyota RAV4 Adventure')
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.is_featured', true);

        $vehicle = Vehicle::firstOrFail();
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'user_id' => $seller->id,
            'slug' => 'toyota-rav4-adventure-2023',
            'is_featured' => true,
        ]);
        $this->assertNotNull($vehicle->expires_at);
        $this->assertDatabaseCount('vehicle_media', 2);

        $paths = VehicleMedia::query()->pluck('path')->all();
        foreach ($paths as $path) {
            Storage::disk('public')->assertExists($path);
            $this->assertStringEndsWith('.webp', $path);
        }

        $this->assertNotNull(VehicleMedia::query()->where('is_primary', true)->first());
    }

    public function test_basic_plan_blocks_tier_upgrade_and_photo_limit_excess(): void
    {
        $seller = User::factory()->create(['account_type' => 'seller']);
        $token = $seller->createToken('seller-web')->plainTextToken;
        $make = VehicleMake::where('slug', 'toyota')->firstOrFail();
        $model = VehicleModel::where('vehicle_make_id', $make->id)->where('slug', 'rav4')->firstOrFail();

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/v1/my/vehicles', [
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'title' => 'Toyota RAV4 Adventure',
            'condition' => 'used',
            'year' => 2023,
            'body_type' => 'SUV',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Automatica',
            'price' => 31500,
            'description' => 'SUV impecable con mantenimiento al dia y lista para venta inmediata.',
            'status' => 'draft',
            'publication_tier' => 'premium',
        ])->assertUnprocessable();

        $images = [];
        for ($i = 1; $i <= 6; $i++) {
            $images[] = UploadedFile::fake()->image("img{$i}.jpg", 1200, 800);
        }

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/v1/my/vehicles', [
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'title' => 'Toyota Yaris Basic',
            'condition' => 'used',
            'year' => 2021,
            'body_type' => 'Sedan',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Manual',
            'price' => 10500,
            'description' => 'Vehiculo economico y cuidado con excelente consumo de combustible.',
            'status' => 'draft',
            'publication_tier' => 'basic',
            'images' => $images,
        ])->assertUnprocessable();
    }

    public function test_basic_plan_blocks_more_than_one_active_listing(): void
    {
        $seller = User::factory()->create(['account_type' => 'seller']);
        $token = $seller->createToken('seller-web')->plainTextToken;
        $this->createVehicle($seller, 'published');

        $make = VehicleMake::where('slug', 'toyota')->firstOrFail();
        $model = VehicleModel::where('vehicle_make_id', $make->id)->where('slug', 'rav4')->firstOrFail();

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/v1/my/vehicles', [
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'title' => 'Segundo activo',
            'condition' => 'used',
            'year' => 2020,
            'body_type' => 'SUV',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Automatica',
            'price' => 15000,
            'description' => 'Segunda publicacion que no deberia activarse con plan basico.',
            'status' => 'published',
            'publication_tier' => 'basic',
        ])->assertUnprocessable();
    }

    public function test_plan_restrictions_block_unsupported_360_and_video_flags(): void
    {
        $seller = User::factory()->create(['account_type' => 'seller']);
        $token = $seller->createToken('seller-web')->plainTextToken;
        $make = VehicleMake::where('slug', 'toyota')->firstOrFail();
        $model = VehicleModel::where('vehicle_make_id', $make->id)->where('slug', 'rav4')->firstOrFail();

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/v1/my/vehicles', [
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'title' => 'Intento 360',
            'condition' => 'used',
            'year' => 2022,
            'body_type' => 'SUV',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Automatica',
            'price' => 22000,
            'description' => 'Intento de publicar con capacidades no cubiertas por el plan.',
            'supports_360' => true,
            'status' => 'draft',
        ])->assertUnprocessable();
    }

    public function test_seller_can_update_own_listing_add_media_and_reorder_gallery(): void
    {
        $seller = $this->sellerWithPlan('premium');
        $token = $seller->createToken('seller-web')->plainTextToken;
        $vehicle = $this->createVehicle($seller, 'draft');

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->patch('/api/v1/my/vehicles/'.$vehicle->id, [
            'price' => 19900,
            'status' => 'published',
            'images' => [
                UploadedFile::fake()->image('extra.jpg', 2200, 1400),
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.price', 19900)
            ->assertJsonPath('data.status', 'published');

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/v1/my/vehicles/'.$vehicle->id.'/media', [
            'images' => [UploadedFile::fake()->image('extra-two.jpg', 1400, 900)],
        ])->assertCreated();

        $mediaIds = $vehicle->fresh()->media()->orderBy('sort_order')->pluck('id')->all();
        $reordered = array_reverse($mediaIds);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->patchJson('/api/v1/my/vehicles/'.$vehicle->id.'/media/reorder', [
            'media_ids' => $reordered,
        ])->assertOk();

        $this->assertSame($reordered, $vehicle->fresh()->media()->orderBy('sort_order')->pluck('id')->all());
    }

    public function test_user_cannot_manage_listing_that_does_not_belong_to_them(): void
    {
        $owner = $this->sellerWithPlan('premium');
        $intruder = $this->sellerWithPlan('premium');
        $token = $intruder->createToken('intruder')->plainTextToken;
        $vehicle = $this->createVehicle($owner, 'draft');

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->patchJson('/api/v1/my/vehicles/'.$vehicle->id, [
            'price' => 12345,
        ])->assertForbidden();
    }

    public function test_public_catalog_only_lists_published_vehicles_and_can_show_slug(): void
    {
        $seller = $this->sellerWithPlan('premium');
        $published = $this->createVehicle($seller, 'published', 'Toyota Corolla LE');
        $draft = $this->createVehicle($seller, 'draft', 'Nissan Frontier XE');

        $this->getJson('/api/v1/vehicles')
            ->assertOk()
            ->assertJsonFragment(['title' => $published->title])
            ->assertJsonMissing(['title' => $draft->title]);

        $this->getJson('/api/v1/vehicles/'.$published->slug)
            ->assertOk()
            ->assertJsonPath('data.title', $published->title);

        $this->getJson('/api/v1/vehicles/'.$draft->slug)->assertNotFound();
    }

    public function test_capabilities_endpoint_returns_effective_plan(): void
    {
        $seller = $this->sellerWithPlan('premium');
        $token = $seller->createToken('seller-web')->plainTextToken;
        $this->createVehicle($seller, 'published');

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/my/publication-capabilities')
            ->assertOk()
            ->assertJsonPath('plan.slug', 'premium')
            ->assertJsonPath('active_listings', 1);
    }

    public function test_publish_and_pause_actions_work(): void
    {
        $seller = $this->sellerWithPlan('premium');
        $token = $seller->createToken('seller-web')->plainTextToken;
        $vehicle = $this->createVehicle($seller, 'draft');

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->patchJson('/api/v1/my/vehicles/'.$vehicle->id.'/publish')
            ->assertOk()
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.is_featured', false);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->patchJson('/api/v1/my/vehicles/'.$vehicle->id.'/pause')
            ->assertOk()
            ->assertJsonPath('data.status', 'paused');
    }

    public function test_seller_can_delete_listing_and_media_files_are_removed(): void
    {
        $seller = $this->sellerWithPlan('premium');
        $token = $seller->createToken('seller-web')->plainTextToken;
        $vehicle = $this->createVehicle($seller, 'published');

        $media = VehicleMedia::create([
            'vehicle_id' => $vehicle->id,
            'type' => 'image',
            'disk' => 'public',
            'path' => 'vehicles/'.$vehicle->id.'/images/test.webp',
            'extension' => 'webp',
            'mime_type' => 'image/webp',
            'size_bytes' => 10,
            'width' => 100,
            'height' => 100,
            'sort_order' => 1,
            'is_primary' => true,
            'processing_status' => 'complete',
            'conversions' => ['thumb' => 'vehicles/'.$vehicle->id.'/images/test_thumb.webp'],
        ]);

        Storage::disk('public')->put($media->path, 'fake-image');
        Storage::disk('public')->put('vehicles/'.$vehicle->id.'/images/test_thumb.webp', 'fake-thumb');

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->deleteJson('/api/v1/my/vehicles/'.$vehicle->id)
            ->assertOk();

        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
        Storage::disk('public')->assertMissing($media->path);
        Storage::disk('public')->assertMissing('vehicles/'.$vehicle->id.'/images/test_thumb.webp');
    }

    public function test_buyer_cannot_create_vehicle_listing(): void
    {
        $buyer = User::factory()->create(['account_type' => 'buyer']);
        $token = $buyer->createToken('buyer-web')->plainTextToken;

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/my/vehicles', [
            'title' => 'Should fail',
        ])->assertForbidden();
    }

    private function createVehicle(User $owner, string $status, string $title = 'Honda Civic EX'): Vehicle
    {
        $make = VehicleMake::firstOrFail();
        $model = VehicleModel::where('vehicle_make_id', $make->id)->firstOrFail();

        return Vehicle::create([
            'user_id' => $owner->id,
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'title' => $title,
            'slug' => str($title)->slug().'-2022',
            'condition' => 'used',
            'year' => 2022,
            'body_type' => 'Sedan',
            'fuel_type' => 'Gasolina',
            'transmission' => 'Automatica',
            'mileage' => 45000,
            'mileage_unit' => 'km',
            'price' => 21500,
            'currency' => 'USD',
            'country_code' => 'CR',
            'description' => 'Vehiculo demo con excelente estado general y listo para entrega inmediata.',
            'features' => ['android-auto'],
            'status' => $status,
            'publication_tier' => 'basic',
            'published_at' => $status === 'published' ? now() : null,
        ]);
    }

    private function sellerWithPlan(string $planSlug): User
    {
        $seller = User::factory()->create(['account_type' => 'seller']);
        $plan = Plan::where('slug', $planSlug)->firstOrFail();

        Subscription::create([
            'user_id' => $seller->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'auto_renews' => false,
            'amount' => $plan->price,
            'currency' => $plan->currency,
        ]);

        return $seller;
    }
}
