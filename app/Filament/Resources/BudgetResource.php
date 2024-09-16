<?php

namespace App\Filament\Resources;

use App\Enums\Frequency;
use App\Enums\Type;
use App\Filament\Resources\BudgetResource\Pages;
use App\Filament\Resources\BudgetResource\RelationManagers\ChildBudgetsRelationManager;
use App\Models\Budget;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('description'),
                Forms\Components\TextInput::make('amount'),
                Forms\Components\Select::make('currency')
                    ->relationship(titleAttribute: 'name')
                    ->searchable(),
                Forms\Components\DatePicker::make('date'),
                Forms\Components\Select::make('frequency')
                    ->options(Frequency::class),
                Forms\Components\Select::make('category')
                    ->relationship(titleAttribute: 'name')
                    ->searchable(),
                Forms\Components\Select::make('location')
                    ->relationship(titleAttribute: 'name')
                    ->searchable(),
                Forms\Components\Checkbox::make('loan_owe_ok')
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('parent_budget_id');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('product.name'),
                Tables\Columns\TextColumn::make('location.name'),
                Tables\Columns\TextColumn::make('top_amount')
                    ->formatStateUsing(fn (Budget $record, $state) => $record->currency->formatAmount($state))
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ChildBudgetsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
