<?php

namespace App\Models;

use Akaunting\Money\Money;
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
        return intval(doubleval($amount) / doubleval(static::where('name', $currency)->first()->rate) * doubleval($this->rate));
    }

    public static function formatValue($value, $currency): string
    {
        return (new Money($value,new \Akaunting\Money\Currency($currency)))->format();
    }
}
