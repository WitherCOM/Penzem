<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'rate'
    ];

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function convertTo($amount, $currency): int
    {
        return $amount * static::where('name', $currency)->first()->rate / $this->rate;
    }
}
