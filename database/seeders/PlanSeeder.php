<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basico',
                'slug' => 'basico',
                'description' => 'Hasta 5 fotos, 30 dias de publicacion y estadisticas basicas.',
                'audience' => 'private_seller',
                'price' => 0,
                'currency' => 'USD',
                'billing_interval' => null,
                'duration_days' => 30,
                'max_active_listings' => 1,
                'photo_limit' => 5,
                'allows_video' => false,
                'allows_360' => false,
                'supports_credit_leads' => false,
                'supports_trade_in' => false,
                'priority_weight' => 10,
                'is_featured' => false,
                'is_active' => true,
                'metadata' => ['chat_enabled' => true],
            ],
            [
                'name' => 'Estandar',
                'slug' => 'estandar',
                'description' => 'Hasta 15 fotos, 60 dias y posicionamiento medio.',
                'audience' => 'private_seller',
                'price' => 12,
                'currency' => 'USD',
                'billing_interval' => null,
                'duration_days' => 60,
                'max_active_listings' => 2,
                'photo_limit' => 15,
                'allows_video' => false,
                'allows_360' => false,
                'supports_credit_leads' => false,
                'supports_trade_in' => false,
                'priority_weight' => 30,
                'is_featured' => false,
                'is_active' => true,
                'metadata' => ['verification_badge' => true],
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Fotos ilimitadas, video y exposicion destacada.',
                'audience' => 'private_seller',
                'price' => 29,
                'currency' => 'USD',
                'billing_interval' => null,
                'duration_days' => 90,
                'max_active_listings' => 3,
                'photo_limit' => null,
                'allows_video' => true,
                'allows_360' => false,
                'supports_credit_leads' => false,
                'supports_trade_in' => false,
                'priority_weight' => 60,
                'is_featured' => true,
                'is_active' => true,
                'metadata' => ['push_alerts' => true],
            ],
            [
                'name' => 'Agencia',
                'slug' => 'agencia',
                'description' => 'Suscripcion mensual para inventario de agencias medianas.',
                'audience' => 'dealer',
                'price' => 99,
                'currency' => 'USD',
                'billing_interval' => 'monthly',
                'duration_days' => 30,
                'max_active_listings' => 30,
                'photo_limit' => null,
                'allows_video' => true,
                'allows_360' => false,
                'supports_credit_leads' => true,
                'supports_trade_in' => true,
                'priority_weight' => 80,
                'is_featured' => true,
                'is_active' => true,
                'metadata' => ['public_profile' => true, 'real_time_stats' => true],
            ],
            [
                'name' => 'Agencia PRO',
                'slug' => 'agencia-pro',
                'description' => 'Inventario ilimitado, fotos 360 y soporte VIP.',
                'audience' => 'dealer',
                'price' => 249,
                'currency' => 'USD',
                'billing_interval' => 'monthly',
                'duration_days' => 30,
                'max_active_listings' => null,
                'photo_limit' => null,
                'allows_video' => true,
                'allows_360' => true,
                'supports_credit_leads' => true,
                'supports_trade_in' => true,
                'priority_weight' => 100,
                'is_featured' => true,
                'is_active' => true,
                'metadata' => ['vip_whatsapp_support' => true, 'launch_offer' => '3 meses gratis para los primeros 20 concesionarios'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
