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

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
