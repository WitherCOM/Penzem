<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;
    use HasUuids;


    public $timestamps = false;

    protected $fillable = [
        'name'
    ];

    public function budgets(): BelongsToMany
    {
        return $this->belongsToMany(Budget::class, 'budget_category');
    }

}
