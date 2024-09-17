<?php

namespace App\Filament\Resources\BudgetResource\RelationManagers;

use App\Enums\Frequency;
use App\Models\Budget;
use App\Models\Category;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChildBudgetsRelationManager extends RelationManager
{
    protected static string $relationship = 'child_budgets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('description'),
                Forms\Components\TextInput::make('amount'),
                Select::make('currency_id')
                    ->required()
                    ->relationship('currency','name'),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\Select::make('frequency')
                    ->required()
                    ->options(Frequency::class),
                SelectTree::make('categories')
                    ->statePath('categories')
                    ->live()
                    ->relationship('categories','name','parent_category_id')
                    ->afterStateUpdated(function (array $state, Set $set) {
                        $set('categories',Category::calcRightCategories($state));
                    })
                    ->enableBranchNode()
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('location')
                    ->relationship(titleAttribute: 'name')
                    ->searchable()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->currency(fn (Budget $record) => $record->currency->name, true),
                Tables\Columns\TextColumn::make('categories.name'),
                Tables\Columns\TextColumn::make('description'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
