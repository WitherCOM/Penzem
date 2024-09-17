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

    public function budgets(): BelongsToMany
    {
        return $this->belongsToMany(Budget::class,'category_budget');
    }

    public function allBudgets($date_from, $date_to): Collection
    {
        $budgets = collect([]);
        if (is_null($date_from) || is_null($date_to))
        {
            $budgets = $budgets->merge($this->budgets()->get());
        }
        else
        {
            $budgets = $budgets->merge($this->budgets()->whereBetween('date',[$date_from, $date_to])->get());
        }
        foreach ($this->categories as $category)
        {
            $budgets = $budgets->merge($category->allBudgets($date_from, $date_to));
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

    public function getChildIds()
    {
        $childCategories = $this->categories;
        $returnIds = $childCategories->pluck('id');
        foreach($childCategories as $category)
        {
            $returnIds = $returnIds->merge($category->getChildIds());
        }
        return $returnIds;
    }

    public function determineParentColumnName(): string
    {
        return "parent_category_id";
    }

    public static function calcRightCategories(array $categories): array
    {
        $categoryIds = collect([]);
        foreach($categories as $category_id)
        {
            $categoryModel = Category::find($category_id);
            if (collect($categories)->intersect($categoryModel->getChildIds())->count() == 0)
            {
                $categoryIds->push($category_id);
            }
        }
        return $categoryIds->toArray();
    }
}
