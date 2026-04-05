<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleValuation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'source',
        'currency',
        'suggested_price',
        'min_price',
        'max_price',
        'confidence_score',
        'input_snapshot',
        'share_token',
        'ai_enabled',
        'ai_summary',
        'market_insights',
        'algorithm_payload',
    ];

    protected function casts(): array
    {
        return [
            'input_snapshot' => 'array',
            'market_insights' => 'array',
            'algorithm_payload' => 'array',
            'ai_enabled' => 'boolean',
            'suggested_price' => 'decimal:2',
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',
            'confidence_score' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
