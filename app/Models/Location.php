<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
      'name',
      'address'
    ];

    protected static function boot()
    {
        static::creating(function(Location $location) {
            $location->address = '';
        });
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}
