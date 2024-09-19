<?php

namespace App\Livewire;

use App\Enums\Frequency;
use App\Enums\Type;
use App\Models\Budget;
use App\Models\Category;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\View\View;
use Livewire\Component;

class CreateDailyBudget extends Component implements HasForms
{
    use InteractsWithForms;
    use HasUuids;

    public ?array $data = [
        'budgets' => []
    ];

    public function render(): View
    {
        return view('livewire.form-base');
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(Budget::class)
            ->schema([
                Repeater::make('budgets')
                    ->minItems(1)
                    ->defaultItems(1)
                    ->schema([
                        Textarea::make('description'),
                        DatePicker::make('date')
                            ->default(now())
                            ->required(),
                        Select::make('location')
                            ->relationship(titleAttribute: 'name')
                            ->live()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                            ]),
                        Select::make('frequency')
                            ->required()
                            ->options(Frequency::class),
                        TextInput::make('amount')
                            ->required(fn(Get $get) => is_null($get('child_budgets')))
                            ->disabled(fn(Get $get) => !is_null($get('child_budgets')))
                            ->numeric(),
                        Select::make('currency')
                            ->required()
                            ->columnSpan(1)
                            ->relationship(titleAttribute: 'name'),
                        SelectTree::make('categories')
                            ->statePath('categories')
                            ->live()
                            ->relationship('categories', 'name', 'parent_category_id')
                            ->afterStateUpdated(function (array $state, Set $set) {
                                $categoryIds = collect([]);
                                foreach ($state as $category_id) {
                                    $categoryModel = Category::find($category_id);
                                    $validId = true;
                                    foreach ($state as $category_id2) {
                                        if ($categoryModel->getChildIds()->contains($category_id2)) {
                                            $validId = false;
                                            break;
                                        }
                                    }
                                    if ($validId) {
                                        $categoryIds->push($category_id);
                                    }
                                }
                                $set('categories', $categoryIds->toArray());
                            })
                            ->enableBranchNode()
                            ->searchable()
                            ->columnSpan(1)
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $state = $this->form->getState();
        $budget = Budget::create([
            'description' => $state['description'],
            'amount' => count($state['child_budgets']) > 0 ? 0 : $state['amount'],
            'frequency' => Frequency::REGULAR,
            'currency_id' => $state['currency'],
            'location_id' => $state['location'],
            'date' => $state['date']
        ]);
        $budget->categories()->attach($state['categories']);
        foreach ($state['child_budgets'] as $child_budget) {
            $cBudget = Budget::create([
                'parent_budget_id' => $budget->id,
                'description' => $child_budget['description'],
                'amount' => $child_budget['amount'],
                'frequency' => Frequency::REGULAR,
                'currency_id' => $budget->currency_id,
                'location_id' => is_null($child_budget['location']) ? $budget->location_id : $child_budget['location'],
                'date' => $state['date']
            ]);
            if (is_null($child_budget['categories'])) {
                $categories = $budget->categories()->pluck('id')->merge(collect($state['categories']))->unique();
                $cBudget->categories()->attach($categories);
            }
        }
        $this->form->fill();
    }
}
