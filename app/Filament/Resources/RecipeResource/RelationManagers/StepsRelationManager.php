<?php

namespace App\Filament\Resources\RecipeResource\RelationManagers;

use App\Models\Step;
use App\Utils\DataFormatter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make("step_id")
                    ->label("Step")
                    ->options(Step::query()->pluck("description", "id")->toArray())
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make("step")
                    ->numeric()
                    ->required()
                    ->label("Step Number")
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Steps')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                TextColumn::make("time")->sortable()
                    ->getStateUsing(fn($record) => DataFormatter::formatDurationShort($record->time)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
//                ->mutateFormDataUsing(function (array $data))
                ,
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
