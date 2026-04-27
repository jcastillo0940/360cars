<?php

namespace Tests\Feature\Auth;

use App\Mail\Auth\WelcomeUserMail;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class MailFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_web_registration_sends_welcome_email(): void
    {
        Mail::fake();

        $this->post(route('register.store'), [
            'name' => 'Maria Seller',
            'email' => 'maria@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'country_code' => 'CR',
        ])->assertRedirect();

        Mail::assertSent(WelcomeUserMail::class, function (WelcomeUserMail $mail): bool {
            return $mail->hasTo('maria@example.com');
        });
    }

    public function test_password_reset_uses_custom_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'Password123',
        ]);

        $this->post(route('password.email'), [
            'email' => 'reset@example.com',
        ])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_password_reset_mail_failure_returns_controlled_error(): void
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andThrow(new \RuntimeException('smtp down'));

        $this->post(route('password.email'), [
            'email' => 'reset@example.com',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');
    }

    public function test_password_reset_page_renders_with_token_and_email(): void
    {
        $this->get(route('password.reset', [
            'token' => 'reset-token-123',
            'email' => 'reset@example.com',
        ]))
            ->assertOk()
            ->assertSee('reset-token-123')
            ->assertSee('reset@example.com');
    }

    public function test_seller_onboarding_with_real_email_sends_welcome_email(): void
    {
        Mail::fake();

        $this->post(route('seller.onboarding.store'), [
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
            'contact_email' => 'carlos@example.com',
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
        ])->assertRedirect(route('seller.dashboard'));

        Mail::assertSent(WelcomeUserMail::class, function (WelcomeUserMail $mail): bool {
            return $mail->hasTo('carlos@example.com');
        });
    }

    public function test_api_registration_sends_welcome_email(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ana API',
            'email' => 'ana@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'account_type' => 'seller',
            'device_name' => 'iphone-15',
        ])->assertCreated();

        Mail::assertSent(WelcomeUserMail::class, function (WelcomeUserMail $mail): bool {
            return $mail->hasTo('ana@example.com');
        });
    }
}
