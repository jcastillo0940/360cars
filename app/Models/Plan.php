<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'audience',
        'price',
        'currency',
        'billing_interval',
        'duration_days',
        'max_active_listings',
        'photo_limit',
        'allows_video',
        'allows_360',
        'supports_credit_leads',
        'supports_trade_in',
        'priority_weight',
        'is_featured',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'allows_video' => 'boolean',
            'allows_360' => 'boolean',
            'supports_credit_leads' => 'boolean',
            'supports_trade_in' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
