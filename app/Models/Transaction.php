<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'payable_type',
        'payable_id',
        'provider',
        'payment_method',
        'status',
        'amount',
        'currency',
        'external_reference',
        'payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
