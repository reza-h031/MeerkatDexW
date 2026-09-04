<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientCategoryResource\Pages;
use App\Filament\Resources\IngredientCategoryResource\RelationManagers;
use App\Models\IngredientCategory;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IngredientCategoryResource extends Resource
{
    protected static ?string $model = IngredientCategory::class;

    protected static ?string $navigationGroup = "Ingredients";
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make("name")->required(),
                CuratorPicker::make("image_id")
                    ->relationship("imageAddress", "path")
                    ->directory("media/images")
                    ->disk("public")->visibility("public")->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("id"),
                TextColumn::make("name"),
                ImageColumn::make("Thumbnail")
                    ->getStateUsing(function ($record) {
                        return asset("/storage/" . $record->imageAddress?->variants?->where("variant", "thumb")->value("path"));
                    })
//                    ->size(32)
                    ->extraAttributes(["class" => "cursor-pointer"])
                    ->action(
                        Action::make("viewImage")
                            ->modalHeading("Preview Image")
                            ->modalContent(fn($record) => view("filament.tables.image-preview", [
                                "image" => asset("storage/" . $record->imageAddress?->path)
                            ]))
                            ->closeModalByClickingAway()
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                    )
                ,
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredientCategories::route('/'),
            'create' => Pages\CreateIngredientCategory::route('/create'),
            'edit' => Pages\EditIngredientCategory::route('/{record}/edit'),
        ];
    }
}
