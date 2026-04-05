<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'type',
        'disk',
        'path',
        'alt_text',
        'mime_type',
        'extension',
        'size_bytes',
        'width',
        'height',
        'duration_seconds',
        'sort_order',
        'is_primary',
        'processing_status',
        'error_message',
        'original_disk',
        'original_path',
        'processed_at',
        'conversions',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'processed_at' => 'datetime',
            'conversions' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
