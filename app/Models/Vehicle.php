<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'vehicle_make_id',
        'vehicle_model_id',
        'title',
        'slug',
        'vin',
        'plate_number',
        'condition',
        'year',
        'trim',
        'body_type',
        'fuel_type',
        'transmission',
        'drivetrain',
        'mileage',
        'mileage_unit',
        'engine',
        'engine_size',
        'exterior_color',
        'interior_color',
        'doors',
        'seats',
        'price',
        'currency',
        'original_price',
        'market_price',
        'price_badge',
        'city',
        'state',
        'province',
        'canton',
        'district',
        'country_code',
        'postal_code',
        'latitude',
        'longitude',
        'description',
        'features',
        'status',
        'publication_tier',
        'is_featured',
        'is_verified_plate',
        'supports_360',
        'has_video',
        'published_at',
        'expires_at',
        'view_count',
        'lead_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'metadata' => 'array',
            'is_featured' => 'boolean',
            'is_verified_plate' => 'boolean',
            'supports_360' => 'boolean',
            'has_video' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'vehicle_make_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_model_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(VehicleMedia::class);
    }

    public function lifestyleCategories(): BelongsToMany
    {
        return $this->belongsToMany(LifestyleCategory::class, 'vehicle_lifestyle_category')
            ->withPivot('score')
            ->withTimestamps();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(VehicleFavorite::class);
    }

    public function comparisons(): BelongsToMany
    {
        return $this->belongsToMany(Comparison::class, 'comparison_vehicle')->withTimestamps();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
