<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Maria Seller',
            'email' => 'maria@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'account_type' => 'seller',
            'phone' => '6000-1234',
            'device_name' => 'iphone-15',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'maria@example.com')
            ->assertJsonPath('user.account_type', 'seller');

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_user_can_login_and_fetch_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => 'Password123',
            'account_type' => 'buyer',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'buyer@example.com',
            'password' => 'Password123',
            'device_name' => 'web-chrome',
        ]);

        $token = $login->json('token');

        $login
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'buyer@example.com')
            ->assertJsonPath('user.account_type', 'buyer');
    }

    public function test_user_can_list_and_revoke_tokens(): void
    {
        $user = User::factory()->create([
            'account_type' => 'seller',
        ]);

        $firstToken = $user->createToken('iphone')->plainTextToken;
        $user->createToken('ipad');

        $listResponse = $this->withHeader('Authorization', 'Bearer '.$firstToken)
            ->getJson('/api/v1/auth/tokens');

        $tokenId = $listResponse->json('data.0.id');

        $listResponse
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->withHeader('Authorization', 'Bearer '.$firstToken)
            ->deleteJson('/api/v1/auth/tokens/'.$tokenId)
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_user_can_logout_current_session_and_logout_all(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('web')->plainTextToken;
        $user->createToken('mobile');

        $this->withHeader('Authorization', 'Bearer '.$currentToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $freshToken = $user->fresh()->createToken('desktop')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$freshToken)
            ->deleteJson('/api/v1/auth/logout-all')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
