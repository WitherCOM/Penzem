<?php

namespace App\Livewire;

use App\Enums\Frequency;
use App\Enums\Type;
use App\Models\Budget;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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

    public function form(Form $form): Form
    {
        return $form
            ->model(Budget::class)
            ->columns(5)
            ->schema([
                        Textarea::make('description')
                            ->columnSpanFull(),
                        DatePicker::make('date')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('location')
                            ->relationship(titleAttribute: 'name')
                            ->columnSpanFull()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                            ]),
                        TextInput::make('amount')
                            ->required(fn(Get $get) => is_null($get('child_budgets')))
                            ->disabled(fn(Get $get) => !is_null($get('child_budgets')))
                            ->columnSpan(3)
                            ->numeric(),
                        Select::make('currency')
                            ->required()
                            ->columnSpan(1)
                            ->relationship(titleAttribute: 'name'),
                        Select::make('category')
                            ->relationship(titleAttribute: 'name')
                            ->columnSpan(1)
                            ->required()
                            ->preload(),
                        Repeater::make('child_budgets')
                            ->columnSpanFull()
                            ->live()
                            ->columns(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->required()
                                    ->columnSpan(2)
                                    ->numeric(),
                                Select::make('category')
                                    ->relationship(titleAttribute: 'name')
                                    ->searchable()
                                    ->columnSpan(1)
                                    ->preload(),
                                Textarea::make('description')
                                    ->columnSpanFull(),
                                Select::make('location')
                                    ->relationship(titleAttribute: 'name')
                                    ->columnSpanFull()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                    ]),
                            ])
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
            'category_id' => $state['category'],
            'location_id' => $state['location'],
            'date' => $state['date']
        ]);
        foreach($state['child_budgets'] as $child_budget)
        {
            Budget::create([
                'parent_budget_id' => $budget->id,
                'description' => $child_budget['description'],
                'amount' => $child_budget['amount'],
                'frequency' => Frequency::REGULAR,
                'currency_id' => $budget->currency_id,
                'location_id' => is_null($child_budget['location']) ? $budget->currency_id : $child_budget['location'],
                'category_id' => is_null($child_budget['category']) ? $budget->currency_id : $child_budget['category'],
                'date' => $state['date']
            ]);
        }
        $this->form->fill();
    }
}
