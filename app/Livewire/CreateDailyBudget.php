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
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class CreateDailyBudget extends Component implements HasForms
{
    use InteractsWithForms;
    use HasUuids;

    public ?array $data = [];

    public function render(): View
    {
        return view('livewire.form-base');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(Budget::class)
            ->schema([
                DatePicker::make('date')
                    ->default(now())
                    ->required(),
                Select::make('location')
                    ->relationship(titleAttribute: 'name')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                    ]),
                Select::make('frequency')
                    ->required()
                    ->default(Frequency::REGULAR)
                    ->options(Frequency::class),
                Select::make('currency')
                    ->required()
                    ->columnSpan(1)
                    ->relationship(titleAttribute: 'name'),
                Repeater::make('budgets')
                    ->minItems(1)
                    ->defaultItems(1)
                    ->columns(2)
                    ->schema([
                        TextInput::make('description')
                        ->columnSpanFull(),
                        TextInput::make('amount')
                            ->required()
                            ->numeric(),
                        SelectTree::make('categories')
                            ->statePath('categories')
                            ->live()
                            ->relationship('categories', 'name', 'parent_category_id')
                            ->afterStateUpdated(function (array $state, Set $set) {
                                $set('categories',Category::calcRightCategories($state));
                            })
                            ->enableBranchNode()
                            ->searchable()
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $state = $this->form->getState();
        $origin = Str::uuid();

        foreach ($state['budgets'] as $budget) {
            $budgetModel = Budget::create([
                'description' => $budget['description'],
                'amount' => $budget['amount'],
                'currency_id' => $state['currency'],
                'frequency' => $state['frequency'],
                'location_id' => $state['location'],
                'date' => $state['date'],
                'origin' => $origin
            ]);
            $budgetModel->categories()->attach($budget['categories']);
        }
        $this->form->fill();
    }
}
