<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LifestyleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
    ];

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'vehicle_lifestyle_category')
            ->withPivot('score')
            ->withTimestamps();
    }
}
