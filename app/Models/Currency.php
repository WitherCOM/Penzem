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
        'prefix',
        'suffix'
    ];

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function formatAmount($amount): string
    {
        $amount = strval($amount);
        if (!is_null($this->prefix))
        {
            $amount = $this->prefix ." " . $amount;
        }
        if (!is_null($this->suffix))
        {
            $amount = $amount . " " . $this->suffix;
        }
        return $amount;
    }
}
