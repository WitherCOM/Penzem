<?php

namespace App\Livewire;

use App\Enums\Frequency;
use App\Enums\Type;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Currency;
use Carbon\Carbon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\View\View;
use Livewire\Attributes\Js;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateBudget extends Component implements HasForms
{
    use InteractsWithForms;
    use HasUuids;

    public ?array $data = [];

    public function render(): View
    {
        return view('livewire.create-budget');
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(Budget::class)
            ->schema([
                Tabs::make('Scenarios')
                    ->tabs([
                        Tab::make('Daily spend')
                        ->schema([
                            Hidden::make('scenario')
                                ->dehydrateStateUsing(fn () => 0),
                            TextInput::make('name')
                                ->columnSpanFull()
                                ->required(),
                            TextInput::make('amount')
                                ->required()
                                ->default(0)
                                ->disabled(fn(Get $get) => !is_null($get('child_budgets')))
                                ->columnSpan(4)
                                ->numeric(),
                            Select::make('currency')
                                ->required()
                                ->columnSpan(1)
                                ->relationship(titleAttribute: 'name'),
                            Select::make('category')
                                ->relationship(titleAttribute: 'name')
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required(),
                                ])
                                ->columnSpanFull()
                                ->required()
                                ->preload(),
                            Repeater::make('child_budgets')
                                ->columnSpanFull()
                                ->live()
                                ->columns(3)
                                ->schema([
                                    TextInput::make('name')
                                        ->required(),
                                    TextInput::make('amount')
                                        ->required()
                                        ->numeric(),
                                    Select::make('category')
                                        ->relationship(titleAttribute: 'name')
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->required(),
                                        ])
                                        ->required()
                                        ->preload(),
                                ])
                        ])
                        ->columns(5),
                        Tab::make('Exchange')
                            ->columns(5)
                            ->schema([
                                Hidden::make('scenario')
                                    ->dehydrateStateUsing(fn () => 1),
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required(),
                                TextInput::make('from_amount')
                                    ->required()
                                    ->columnSpan(4)
                                    ->numeric(),
                                Select::make('from_currency')
                                    ->required()
                                    ->columnSpan(1)
                                    ->relationship('currency', 'name'),
                                TextInput::make('to_amount')
                                    ->required()
                                    ->columnSpan(4)
                                    ->numeric(),
                                Select::make('to_currency')
                                    ->required()
                                    ->columnSpan(1)
                                    ->relationship('currency', 'name'),
                            ])
                    ])
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $state = $this->form->getState();
        $today = Carbon::today();
        switch ($state['scenario'])
        {
            case 0:
                {
                    $budget = Budget::create([
                        'name' => $state['name'],
                        'amount' => count($state['child_budgets']) > 0 ? 0 : $state['amount'],
                        'type' => Type::SPENDING,
                        'frequency' => Frequency::REGULAR,
                        'currency_id' => $state['currency'],
                        'category_id' => $state['category_id'],
                        'date' => $today
                    ]);
                    foreach($state['child_budgets'] as $child_budget)
                    {
                        Budget::create([
                            'parent_budget_id' => $budget->id,
                            'name' => $child_budget['name'],
                            'amount' => $child_budget['amount'],
                            'type' => Type::SPENDING,
                            'frequency' => Frequency::REGULAR,
                            'currency_id' => $budget->currency_id,
                            'category_id' => $child_budget['category_id'],
                            'date' => $today
                        ]);
                    }
                    break;
                }
            case 1:
            {
                Budget::create([
                    'name' => $state['name'],
                    'amount' => $state['from_amount'],
                    'type' => Type::SPENDING,
                    'frequency' => Frequency::REGULAR,
                    'currency_id' => $state['from_currency'],
                    'category_id' => null,
                    'date' => $today
                ]);
                Budget::create([
                    'name' => $state['name'],
                    'amount' => $state['to_amount'],
                    'type' => Type::INCOME,
                    'frequency' => Frequency::REGULAR,
                    'currency_id' => $state['to_currency'],
                    'category_id' => null,
                    'date' => $today
                ]);
                break;
            }

        }
    }
}
