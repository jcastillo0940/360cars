<?php

namespace Database\Seeders;

use App\Models\LifestyleCategory;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFeatureOption;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            CostaRicaLocationsSeeder::class,
            PlanSeeder::class,
            ExtrasSeeder::class,
            NewsPostSeeder::class,
        ]);

        $seller = User::updateOrCreate(
            ['email' => 'seller@movikaa.local'],
            [
                'name' => 'Vendedor Demo',
                'password' => 'password',
                'account_type' => 'seller',
                'phone' => '6000-0000',
                'whatsapp_phone' => '6000-0000',
                'country_code' => 'CR',
                'is_verified' => true,
                'verified_at' => now(),
            ],
        );

        $dealer = User::updateOrCreate(
            ['email' => 'dealer@movikaa.local'],
            [
                'name' => 'Agencia Demo',
                'password' => 'password',
                'account_type' => 'dealer',
                'agency_name' => 'Movikaa Motors',
                'company_name' => 'Movikaa Motors S.A.',
                'phone' => '7000-0000',
                'whatsapp_phone' => '7000-0000',
                'country_code' => 'CR',
                'is_verified' => true,
                'verified_at' => now(),
            ],
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@movikaa.local'],
            [
                'name' => 'Admin Demo',
                'password' => 'password',
                'account_type' => 'admin',
                'phone' => '9000-0000',
                'country_code' => 'CR',
                'is_verified' => true,
                'verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'buyer@movikaa.local'],
            [
                'name' => 'Comprador Demo',
                'password' => 'password',
                'account_type' => 'buyer',
                'phone' => '8000-0000',
                'country_code' => 'CR',
            ],
        );

        $premiumPlan = Plan::where('slug', 'premium')->first();
        $agencyProPlan = Plan::where('slug', 'agencia-pro')->first();

        if ($premiumPlan) {
            Subscription::updateOrCreate(
                ['user_id' => $seller->id, 'plan_id' => $premiumPlan->id],
                [
                    'status' => 'active',
                    'starts_at' => now()->subDays(10),
                    'ends_at' => now()->addDays(20),
                    'auto_renews' => false,
                    'amount' => $premiumPlan->price,
                    'currency' => $premiumPlan->currency,
                ],
            );

            Subscription::updateOrCreate(
                ['user_id' => $admin->id, 'plan_id' => $premiumPlan->id],
                [
                    'status' => 'active',
                    'starts_at' => now()->subDays(3),
                    'ends_at' => now()->addDays(27),
                    'auto_renews' => false,
                    'amount' => $premiumPlan->price,
                    'currency' => $premiumPlan->currency,
                ],
            );
        }

        if ($agencyProPlan) {
            Subscription::updateOrCreate(
                ['user_id' => $dealer->id, 'plan_id' => $agencyProPlan->id],
                [
                    'status' => 'active',
                    'starts_at' => now()->subDays(5),
                    'ends_at' => now()->addDays(25),
                    'auto_renews' => true,
                    'amount' => $agencyProPlan->price,
                    'currency' => $agencyProPlan->currency,
                ],
            );
        }

        $this->seedDemoInventory($seller, $dealer, $admin);
    }

    private function seedDemoInventory(User $seller, User $dealer, User $admin): void
    {
        $toyota = VehicleMake::where('slug', 'toyota')->first();
        $rav4 = VehicleModel::where('slug', 'rav4')->where('vehicle_make_id', $toyota?->id)->first();
        $corolla = VehicleModel::where('slug', 'corolla')->where('vehicle_make_id', $toyota?->id)->first();
        $hyundai = VehicleMake::where('slug', 'hyundai')->first();
        $tucson = VehicleModel::where('slug', 'tucson')->where('vehicle_make_id', $hyundai?->id)->first();

        if (! $toyota || ! $rav4 || ! $corolla || ! $hyundai || ! $tucson) {
            return;
        }

        $inventory = [
            [
                'owner_id' => $seller->id,
                'make_id' => $toyota->id,
                'model_id' => $rav4->id,
                'slug' => 'toyota-rav4-2022-ex-demo',
                'title' => 'Toyota RAV4 2022 EX',
                'trim' => 'EX',
                'year' => 2022,
                'mileage' => 42000,
                'price' => 14600000,
                'city' => 'San Jose',
                'state' => 'San Jose',
                'body_type' => 'SUV',
                'fuel_type' => 'Gasolina',
                'transmission' => 'Automatica',
                'drivetrain' => 'AWD',
                'engine' => '2.5L',
                'engine_size' => 2.5,
                'is_featured' => true,
                'has_video' => true,
                'price_badge' => 'Precio Justo',
                'metadata' => [
                    'source' => 'seed',
                    'cross_sell_budget_band' => '14000000-15500000',
                    'plan_name' => 'Premium',
                    'plan_slug' => 'premium',
                    'plan_is_paid' => true,
                    'plan_priority_weight' => 80,
                    'visibility_bucket' => 'featured',
                ],
                'features' => ['camara-de-retroceso', 'sensores-de-retroceso', 'bluetooth', 'tapiceria-de-cuero'],
            ],
            [
                'owner_id' => $dealer->id,
                'make_id' => $toyota->id,
                'model_id' => $corolla->id,
                'slug' => 'toyota-corolla-2022-xei-demo',
                'title' => 'Toyota Corolla 2022 XEi',
                'trim' => 'XEi',
                'year' => 2022,
                'mileage' => 38000,
                'price' => 14250000,
                'city' => 'San Jose',
                'state' => 'San Jose',
                'body_type' => 'SUV',
                'fuel_type' => 'Gasolina',
                'transmission' => 'Automatica',
                'drivetrain' => 'AWD',
                'engine' => '2.0L',
                'engine_size' => 2.0,
                'is_featured' => false,
                'has_video' => false,
                'price_badge' => 'Excelente Precio',
                'metadata' => [
                    'source' => 'seed',
                    'plan_name' => 'Agencia PRO',
                    'plan_slug' => 'agencia-pro',
                    'plan_is_paid' => true,
                    'plan_priority_weight' => 90,
                    'visibility_bucket' => 'priority',
                ],
                'features' => ['camara-de-retroceso', 'bluetooth', 'frenos-abs'],
            ],
            [
                'owner_id' => $seller->id,
                'make_id' => $toyota->id,
                'model_id' => $corolla->id,
                'slug' => 'toyota-corolla-2021-se-demo',
                'title' => 'Toyota Corolla 2021 SE',
                'trim' => 'SE',
                'year' => 2021,
                'mileage' => 51000,
                'price' => 13450000,
                'city' => 'Heredia',
                'state' => 'Heredia',
                'body_type' => 'SUV',
                'fuel_type' => 'Gasolina',
                'transmission' => 'Automatica',
                'drivetrain' => 'AWD',
                'engine' => '2.0L',
                'engine_size' => 2.0,
                'is_featured' => false,
                'has_video' => false,
                'price_badge' => 'Precio Justo',
                'metadata' => [
                    'source' => 'seed',
                    'plan_name' => 'Premium',
                    'plan_slug' => 'premium',
                    'plan_is_paid' => true,
                    'plan_priority_weight' => 70,
                    'visibility_bucket' => 'priority',
                ],
                'features' => ['camara-de-retroceso', 'bluetooth', 'cruise-control'],
            ],
            [
                'owner_id' => $admin->id,
                'make_id' => $toyota->id,
                'model_id' => $corolla->id,
                'slug' => 'toyota-corolla-2023-xle-demo',
                'title' => 'Toyota Corolla 2023 XLE',
                'trim' => 'XLE',
                'year' => 2023,
                'mileage' => 22000,
                'price' => 15350000,
                'city' => 'Escazu',
                'state' => 'San Jose',
                'body_type' => 'SUV',
                'fuel_type' => 'Gasolina',
                'transmission' => 'Automatica',
                'drivetrain' => 'AWD',
                'engine' => '2.0L',
                'engine_size' => 2.0,
                'is_featured' => true,
                'has_video' => true,
                'price_badge' => 'Precio Alto',
                'metadata' => [
                    'source' => 'seed',
                    'plan_name' => 'Premium',
                    'plan_slug' => 'premium',
                    'plan_is_paid' => true,
                    'plan_priority_weight' => 78,
                    'visibility_bucket' => 'featured',
                ],
                'features' => ['camara-de-retroceso', 'bluetooth', 'tapiceria-de-cuero', 'sunroof-techo-panoramico'],
            ],
            [
                'owner_id' => $dealer->id,
                'make_id' => $hyundai->id,
                'model_id' => $tucson->id,
                'slug' => 'hyundai-tucson-2022-limited-demo',
                'title' => 'Hyundai Tucson 2022 Limited',
                'trim' => 'Limited',
                'year' => 2022,
                'mileage' => 36000,
                'price' => 15800000,
                'city' => 'Alajuela',
                'state' => 'Alajuela',
                'body_type' => 'SUV',
                'fuel_type' => 'Gasolina',
                'transmission' => 'Automatica',
                'drivetrain' => 'FWD',
                'engine' => '2.0L',
                'engine_size' => 2.0,
                'is_featured' => false,
                'has_video' => false,
                'price_badge' => 'Precio Justo',
                'metadata' => [
                    'source' => 'seed',
                    'plan_name' => 'Agencia PRO',
                    'plan_slug' => 'agencia-pro',
                    'plan_is_paid' => true,
                    'plan_priority_weight' => 88,
                    'visibility_bucket' => 'priority',
                ],
                'features' => ['camara-de-retroceso', 'llave-inteligente-boton-de-arranque', 'tapiceria-de-cuero'],
            ],
        ];

        foreach ($inventory as $listing) {
            $vehicle = Vehicle::updateOrCreate(
                ['slug' => $listing['slug']],
                [
                    'user_id' => $listing['owner_id'],
                    'vehicle_make_id' => $listing['make_id'],
                    'vehicle_model_id' => $listing['model_id'],
                    'title' => $listing['title'],
                    'vin' => strtoupper(Str::random(17)),
                    'plate_number' => strtoupper(Str::random(3)).rand(100, 999),
                    'condition' => 'used',
                    'year' => $listing['year'],
                    'trim' => $listing['trim'],
                    'body_type' => $listing['body_type'],
                    'fuel_type' => $listing['fuel_type'],
                    'transmission' => $listing['transmission'],
                    'drivetrain' => $listing['drivetrain'],
                    'mileage' => $listing['mileage'],
                    'mileage_unit' => 'km',
                    'engine' => $listing['engine'],
                    'engine_size' => $listing['engine_size'],
                    'exterior_color' => 'Blanco',
                    'interior_color' => 'Negro',
                    'doors' => 5,
                    'seats' => 5,
                    'price' => $listing['price'],
                    'currency' => 'CRC',
                    'original_price' => $listing['price'] + 350000,
                    'market_price' => $listing['price'] - 150000,
                    'price_badge' => $listing['price_badge'],
                    'city' => $listing['city'],
                    'state' => $listing['state'],
                    'country_code' => 'CR',
                    'description' => 'Vehiculo demo para pruebas del marketplace y del tasador con enfoque en mercado de Costa Rica.',
                    'features' => $listing['features'],
                    'status' => 'published',
                    'publication_tier' => 'premium',
                    'is_featured' => $listing['is_featured'],
                    'is_verified_plate' => true,
                    'supports_360' => false,
                    'has_video' => $listing['has_video'],
                    'published_at' => now()->subDays(rand(0, 9)),
                    'expires_at' => now()->addDays(90),
                    'metadata' => $listing['metadata'],
                ],
            );

            if ($vehicle->id && $vehicle->slug === 'toyota-rav4-2022-ex-demo') {
                $categoryIds = LifestyleCategory::whereIn('slug', [
                    Str::slug('Familiar'),
                    Str::slug('Aventurero'),
                ])->pluck('id');

                $vehicle->lifestyleCategories()->sync($categoryIds->mapWithKeys(
                    fn ($id) => [$id => ['score' => 100]]
                ));
            }
        }
    }
}


