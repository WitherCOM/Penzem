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
                        Select::make('product')
                            ->relationship(titleAttribute: 'name')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        DatePicker::make('date')
                            ->required()
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
                                    ->required()
                                    ->preload(),
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
            'type' => Type::SPENDING,
            'frequency' => Frequency::REGULAR,
            'currency_id' => $state['currency'],
            'product_id' => $state['product'],
            'category_id' => $state['category'],
            'date' => $state['date']
        ]);
        foreach($state['child_budgets'] as $child_budget)
        {
            Budget::create([
                'parent_budget_id' => $budget->id,
                'description' => '',
                'amount' => $child_budget['amount'],
                'type' => Type::SPENDING,
                'frequency' => Frequency::REGULAR,
                'currency_id' => $budget->currency_id,
                'product_id' => $child_budget['product'],
                'category_id' => $child_budget['category'],
                'date' => $state['date']
            ]);
        }
        redirect()->back();
    }
}
