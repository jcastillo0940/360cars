<?php

namespace App\Models;

use App\Enums\AccountType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'account_type',
    'phone',
    'whatsapp_phone',
    'country_code',
    'avatar_path',
    'bio',
    'agency_name',
    'company_name',
    'tax_id',
    'google_id',
    'apple_id',
    'facebook_id',
    'is_verified',
    'verified_at',
    'rating_average',
    'rating_count',
    'last_seen_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    private const COUNTRY_DIAL_CODES = [
        'CR' => '506',
        'PA' => '507',
        'NI' => '505',
        'HN' => '504',
        'SV' => '503',
        'GT' => '502',
        'MX' => '52',
        'CO' => '57',
        'US' => '1',
        'ES' => '34',
    ];
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'account_type' => AccountType::class,
        ];
    }

    public function hasRole(AccountType|string ...$roles): bool
    {
        $allowed = array_map(
            fn (AccountType|string $role) => $role instanceof AccountType ? $role->value : $role,
            $roles,
        );

        return in_array($this->account_type->value, $allowed, true);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(VehicleFavorite::class);
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function comparisons(): HasMany
    {
        return $this->hasMany(Comparison::class);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['role', 'last_read_at'])
            ->withTimestamps();
    }

    public function dialingCode(): string
    {
        $country = strtoupper((string) ($this->country_code ?? 'CR'));

        return self::COUNTRY_DIAL_CODES[$country] ?? self::COUNTRY_DIAL_CODES['CR'];
    }

    public static function dialingCodes(): array
    {
        return self::COUNTRY_DIAL_CODES;
    }

    public static function countryOptions(): array
    {
        return [
            ['code' => 'CR', 'dial' => '+506', 'label' => 'Costa Rica', 'flag' => 'CR'],
            ['code' => 'PA', 'dial' => '+507', 'label' => 'Panama', 'flag' => 'PA'],
            ['code' => 'NI', 'dial' => '+505', 'label' => 'Nicaragua', 'flag' => 'NI'],
            ['code' => 'HN', 'dial' => '+504', 'label' => 'Honduras', 'flag' => 'HN'],
            ['code' => 'SV', 'dial' => '+503', 'label' => 'El Salvador', 'flag' => 'SV'],
            ['code' => 'GT', 'dial' => '+502', 'label' => 'Guatemala', 'flag' => 'GT'],
            ['code' => 'MX', 'dial' => '+52', 'label' => 'Mexico', 'flag' => 'MX'],
            ['code' => 'CO', 'dial' => '+57', 'label' => 'Colombia', 'flag' => 'CO'],
            ['code' => 'US', 'dial' => '+1', 'label' => 'Estados Unidos', 'flag' => 'US'],
            ['code' => 'ES', 'dial' => '+34', 'label' => 'Espana', 'flag' => 'ES'],
        ];
    }

    private static function detectDialCodeFromDigits(string $digits): ?string
    {
        foreach (collect(self::COUNTRY_DIAL_CODES)->sortByDesc(fn (string $code) => strlen($code)) as $dial) {
            if (str_starts_with($digits, $dial)) {
                return $dial;
            }
        }

        return null;
    }

    public function formatPhone(?string $phone = null): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($phone ?? $this->phone));
        if (! $digits) {
            return null;
        }

        $dial = self::detectDialCodeFromDigits($digits) ?: $this->dialingCode();
        if (str_starts_with($digits, $dial)) {
            return '+'.$digits;
        }

        return '+'.$dial.' '.$digits;
    }

    public function whatsappDestination(?string $phone = null): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($phone ?? $this->whatsapp_phone ?? $this->phone));
        if (! $digits) {
            return null;
        }

        $dial = self::detectDialCodeFromDigits($digits) ?: $this->dialingCode();
        if (str_starts_with($digits, $dial)) {
            return $digits;
        }

        return $dial.$digits;
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
