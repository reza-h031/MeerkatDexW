<?php

namespace App\Filament\Resources\RecipeResource\RelationManagers;

use App\Filament\MyComponents\Forms\Components\TimeInput;
use App\Models\Step;
use App\Utils\DataFormatter;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecipeAndStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipeAndSteps';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make("step_id")
                    ->relationship("stepObj","description")
                    ->label("Step")
//                    ->searchable()
                    ->required()
                    ->searchable()
//                    ->options(Step::query()->pluck("description", "id"))
                    ->suffixAction(
                        Forms\Components\Actions\Action::make("createStep")
                            ->label("New")
                            ->icon("heroicon-o-plus")
                            ->url(route("filament.admin.resources.steps.create"))
                            ->openUrlInNewTab()
                    )
                    ->live()
//                    ->createOptionForm([
//                        RichEditor::make("description")->required(),
//                        TimeInput::make("time")->required(),
//
//                    ])
                ,
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('step')
            ->columns([
                TextColumn::make("step"),
                TextColumn::make('stepObj.description')->label("Description"),
                TextColumn::make("Time")->sortable()
                    ->getStateUsing(fn($record) => DataFormatter::formatDurationShort($record->stepObj->time))
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data["step"] = ($this->ownerRecord->recipeAndSteps->max("step") ?? 0) + 1;
                        return $data;
                    })
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
