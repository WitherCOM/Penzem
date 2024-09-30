<?php

namespace App\Livewire;

use App\Enums\Frequency;
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

class CreateExchange extends Component implements HasForms
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
            ->statePath('data');
    }

    public function create(): void
    {
        $state = $this->form->getState();
        $today = Carbon::today();
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
    }
}
