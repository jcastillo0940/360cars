<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_path',
        'to_url',
        'status_code',
        'is_active',
        'hit_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'hit_count' => 'integer',
            'status_code' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
