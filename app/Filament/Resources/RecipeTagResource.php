<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeTagResource\Pages;
use App\Filament\Resources\RecipeTagResource\RelationManagers;
use App\Models\RecipeTag;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecipeTagResource extends Resource
{
    protected static ?string $model = RecipeTag::class;

    protected static ?string $navigationGroup = "Recipes";
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = "Tags";

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make("name")->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("id"),
                TextColumn::make("name"),
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
            'index' => Pages\ListRecipeTags::route('/'),
            'create' => Pages\CreateRecipeTag::route('/create'),
            'edit' => Pages\EditRecipeTag::route('/{record}/edit'),
        ];
    }
}
