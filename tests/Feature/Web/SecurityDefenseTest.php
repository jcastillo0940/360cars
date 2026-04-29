<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Honeypot\Honeypot;
use Tests\TestCase;

class SecurityDefenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_ip_is_rejected(): void
    {
        config()->set('security.blocked_ips', ['203.0.113.10']);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->get('/')
            ->assertForbidden();
    }

    public function test_honeypot_blocks_fast_form_submission(): void
    {
        config()->set('honeypot.enabled', true);
        config()->set('honeypot.randomize_name_field_name', false);
        config()->set('honeypot.amount_of_seconds', 2);
        $honeypot = app(Honeypot::class)->toArray();

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Maria Seller',
                'email' => 'maria@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'country_code' => 'CR',
                $honeypot['nameFieldName'] => '',
                $honeypot['validFromFieldName'] => $honeypot['encryptedValidFrom'],
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('form');
    }

    public function test_honeypot_allows_legitimate_submission_after_delay(): void
    {
        config()->set('honeypot.enabled', true);
        config()->set('honeypot.randomize_name_field_name', false);
        config()->set('honeypot.amount_of_seconds', 1);
        $honeypot = app(Honeypot::class)->toArray();
        sleep(2);

        $this->post(route('register.store'), [
            'name' => 'Maria Seller',
            'email' => 'maria@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'country_code' => 'CR',
            $honeypot['nameFieldName'] => '',
            $honeypot['validFromFieldName'] => $honeypot['encryptedValidFrom'],
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
        ]);
    }
}
