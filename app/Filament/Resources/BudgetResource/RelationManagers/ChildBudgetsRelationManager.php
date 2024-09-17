<?php

namespace App\Filament\Resources\BudgetResource\RelationManagers;

use App\Models\Budget;
use App\Models\Category;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
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
                SelectTree::make('categories')
                    ->statePath('categories')
                    ->live()
                    ->relationship('categories','name','parent_category_id')
                    ->afterStateUpdated(function (array $state, Set $set) {
                        $categoryIds = collect([]);
                        foreach($state as $category_id)
                        {
                            $categoryModel = Category::find($category_id);
                            $validId = true;
                            foreach ($state as $category_id2)
                            {
                                if ($categoryModel->getChildIds()->contains($category_id2))
                                {
                                    $validId = false;
                                    break;
                                }
                            }
                            if ($validId)
                            {
                                $categoryIds->push($category_id);
                            }
                        }
                        $set('categories',$categoryIds->toArray());
                    })
                    ->enableBranchNode()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
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
