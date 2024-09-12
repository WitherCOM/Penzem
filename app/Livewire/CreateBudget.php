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
use Filament\Forms\Components\Textarea;
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
                            Select::make('product')
                                ->relationship(titleAttribute: 'name')
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required(),
                                ])
                                ->columnSpanFull(),
                            Textarea::make('description')
                                ->columnSpanFull(),
                            TextInput::make('amount')
                                ->required(fn(Get $get) => is_null($get('child_budgets')))
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
                                    Select::make('product')
                                        ->relationship(titleAttribute: 'name')
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->required(),
                                        ])
                                        ->columnSpanFull(),
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
                                Textarea::make('description')
                                    ->columnSpanFull(),
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
                        'description' => $state['description'],
                        'amount' => count($state['child_budgets']) > 0 ? 0 : $state['amount'],
                        'type' => Type::SPENDING,
                        'frequency' => Frequency::REGULAR,
                        'currency_id' => $state['currency'],
                        'product_id' => $state['product'],
                        'category_id' => $state['category_id'],
                        'date' => $today
                    ]);
                    foreach($state['child_budgets'] as $child_budget)
                    {
                        Budget::create([
                            'parent_budget_id' => $budget->id,
                            'amount' => $child_budget['amount'],
                            'type' => Type::SPENDING,
                            'frequency' => Frequency::REGULAR,
                            'currency_id' => $budget->currency_id,
                            'product_id' => $child_budget['product'],
                            'category_id' => $child_budget['category_id'],
                            'date' => $today
                        ]);
                    }
                    break;
                }
            case 1:
            {
                Budget::create([
                    'description' => $state['description'],
                    'amount' => $state['from_amount'],
                    'type' => Type::SPENDING,
                    'frequency' => Frequency::REGULAR,
                    'currency_id' => $state['from_currency'],
                    'category_id' => null,
                    'date' => $today
                ]);
                Budget::create([
                    'description' => $state['description'],
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
