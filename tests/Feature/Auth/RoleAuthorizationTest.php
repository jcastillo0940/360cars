<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_access_seller_portal(): void
    {
        $seller = User::factory()->create([
            'account_type' => 'seller',
        ]);

        $token = $seller->createToken('seller-web')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/portal/seller')
            ->assertOk()
            ->assertJsonPath('message', 'Portal de vendedor activo.');
    }

    public function test_buyer_cannot_access_dealer_portal(): void
    {
        $buyer = User::factory()->create([
            'account_type' => 'buyer',
        ]);

        $token = $buyer->createToken('buyer-web')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/portal/dealer')
            ->assertForbidden();
    }

    public function test_guest_cannot_access_protected_portal(): void
    {
        $this->getJson('/api/v1/portal/seller')->assertUnauthorized();
    }
}
