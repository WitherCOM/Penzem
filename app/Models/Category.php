<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use SolutionForest\FilamentTree\Concern\ModelTree;

class Category extends Model
{
    use HasFactory;
    use HasUuids;
    use ModelTree;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'parent_category_id'
    ];

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function allBudgets(): Collection
    {
        $budgets = collect([]);
        $budgets = $budgets->merge($this->budgets);
        foreach ($this->categories as $category)
        {
            $budgets = $budgets->merge($category->allBudgets());
        }
        return $budgets;
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }
    public function parent_category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function determineParentColumnName(): string
    {
        return "parent_category_id";
    }
}
