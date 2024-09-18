<?php

namespace App\Models;

use App\Enums\Frequency;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory;
    use HasUuids;


    protected $fillable = [
        'description',
        'amount',
        'currency_id',
        'date',
        'parent_budget_id',
        'location_id',
        'frequency'
    ];

    protected $casts = [
        'frequency' => Frequency::class
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_budget');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function child_budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'parent_budget_id', 'id');
    }

    public function topAmount(): Attribute
    {
        return Attribute::get(function (){
            if ($this->child_budgets()->count() > 0)
            {
                return $this->child_budgets->sum('amount');
            }
            else
            {
                return $this->getOriginal('amount');
            }
        } );
    }

    function checkCategory(): bool
    {
        $categories = $this->categories()->get();
        $rightCategories = Category::calcRightCategories($categories->pluck('id')->toArray());
        return $categories->intersect($rightCategories)->count() == $categories->count();
    }

    function fixCategory()
    {
        $rightCategories = Category::calcRightCategories($this->categories()->pluck('id')->toArray());
        $this->categories()->sync($rightCategories);
    }
}
