<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use App\Models\MyMedia;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\Models\Media;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationGroup = "Ingredients";
    protected static ?int $navigationSort=0;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make("name")->required(),
                Select::make("ingredient_category_id")->relationship("category", "name")
                    ->preload()->required()->preload()
                    ->createOptionForm([
                        TextInput::make("name")->required(),
                        CuratorPicker::make("image_id")
                            ->directory("media/images")
                            ->disk("public")
                            ->visibility("public")->required()
                    ]),

                CuratorPicker::make("image_id")
                    ->directory("media/images")
                    ->disk("public")->visibility("public")->required(),

                TextInput::make("calories")->numeric()->required(),

                Fieldset::make("nutrition")->relationship("nutrition")->schema([
                    TextInput::make("fat")->numeric()->required(),
                    TextInput::make("carbs")->numeric()->required(),
                    TextInput::make("protein")->numeric()->required(),
                    TextInput::make("cholesterol")->numeric()->required(),
                    TextInput::make("fiber")->numeric()->required(),
                    TextInput::make("saturated_fat")->numeric()->required(),
                    TextInput::make("sugar")->numeric()->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("id"),
//                $record->iconAddress->variants->where("variant", "thumb")->value("path")
//                CuratorColumn::make("thumbnail")
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
                TextColumn::make("name")->sortable()->searchable(),
                TextColumn::make("category.name")->sortable()->searchable(),
                TextColumn::make("calories")->sortable()->numeric()->badge(),
            ])
            ->filters([
                Filter::make("calories")->form([
                    Fieldset::make("Calories Range")->schema([
                        TextInput::make("min")->numeric()->label("Min Calories"),
                        TextInput::make("max")->numeric()->label("Max Calories"),
                    ])
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data["min"], fn($q, $min) => $q->where("calories", ">=", $min))
                        ->when($data["max"], fn($q, $max) => $q->where("calories", "<=", $max));
                }),
                SelectFilter::make("category.name")->label("Category")
                    ->relationship("category", "name"),
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
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }
}













//Fieldset::make("Nutrition")->schema([
//    TextInput::make("Fat")->numeric()
//        ->afterStateHydrated(function ($set, $record) {
//            if ($record) {
//                $set("Fat", $record->nutrition->firstWhere("name", "fat")->value);
//            }
//        })
//        ->afterStateUpdated(function ($state, callable $set, $record) {
//            if ($state) {
//                $set("nutrition.1.value", $state);
//            }
//        }),
//    TextInput::make("Carbs")->numeric()
//        ->afterStateHydrated(function ($set, $record) {
//            if ($record) {
//                $set("Carbs", $record->nutrition->firstWhere("name", "carbs")->value);
//            }
//        })->afterStateUpdated(function ($state, callable $set, $record) {
//            if ($state) {
//                $set("nutrition.2.value", $state);
//            }
//        }),
//    TextInput::make("Protein")->numeric()
//        ->afterStateHydrated(function ($set, $record) {
//            if ($record) {
//                $set("Protein", $record->nutrition->firstWhere("name", "protein")->value);
//            }
//        })->afterStateUpdated(function ($state, callable $set, $record) {
//            if ($state) {
//                $set("nutrition.3.value", $state);
//            }
//        }),
//    TextInput::make("Cholesterol")->numeric()
//        ->afterStateHydrated(function ($set, $record) {
//            if ($record) {
//                $set("Cholesterol", $record->nutrition->firstWhere("name", "cholesterol")->value);
//            }
//        })->afterStateUpdated(function ($state, callable $set, $record) {
//            if ($state) {
//                $set("nutrition.4.value", $state);
//            }
//        }),
//    TextInput::make("Fiber")->numeric()
//        ->afterStateHydrated(function ($set, $record) {
//            if ($record) {
//                $set("Fiber", $record->nutrition->firstWhere("name", "fiber")->value);
//            }
//        })->afterStateUpdated(function ($state, callable $set, $record) {
//            if ($state) {
//                $set("nutrition.5.value", $state);
//            }
//        }),
//    TextInput::make("Saturated Fat")->numeric()
//        ->afterStateHydrated(function ($set, $record) {
//            if ($record) {
//                $set("Saturated Fat", $record->nutrition->firstWhere("name", "saturated_fat")->value);
//            }
//        })->afterStateUpdated(function ($state, callable $set, $record) {
//            if ($state) {
//                $set("nutrition.6.value", $state);
//            }
//        }),
//    TextInput::make("Sugar")->numeric()
//        ->afterStateHydrated(function ($set, $record) {
//            if ($record) {
//                $set("Sugar", $record->nutrition->firstWhere("name", "sugar")->value);
//            }
//        })->afterStateUpdated(function ($state, callable $set, $record) {
//            if ($state) {
//                $set("nutrition.7.value", $state);
//            }
//        }),
//])
