<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Filament\Resources\RecipeResource\RelationManagers;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeTag;
use App\Models\Step;
use App\Utils\DataFormatter;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Faker\Provider\Text;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isEmpty;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationGroup = "Recipes";
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make("name")->required(),
                Select::make("recipe_category_id")
                    ->relationship("category", "name")
                    ->preload()->required()
                    ->createOptionForm([
                        TextInput::make("name")->required(),
                        CuratorPicker::make("image_id")
                            ->directory("media/images")
                            ->disk("public")
                            ->visibility("public")->required()
                    ]),
                Select::make("rate")->required()->options([
                    '1' => '1',
                    '1.5' => '1.5',
                    '2' => '2',
                    '2.5' => '2.5',
                    '3' => '3',
                    '3.5' => '3.5',
                    '4' => '4',
                    '4.5' => '4.5',
                    '5' => '5',
                ]),
                TextInput::make('time')
                    ->mask('99:99:99')
                    ->placeholder('HH:MM:SS')
                    // Show value in HH:MM:SS when editing
                    ->afterStateHydrated(function (TextInput $component, $state) {
                        if ($state) {
                            $hours = floor($state / 3600);
                            $minutes = floor(($state % 3600) / 60);
                            $seconds = $state % 60;
                            $component->state(sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds));
                        }
                    })
                    // Convert HH:MM:SS back into seconds before saving
                    ->dehydrateStateUsing(function ($state) {
                        if (!$state) {
                            return 0;
                        }

                        [$hours, $minutes, $seconds] = array_pad(explode(':', $state), 3, 0);
                        return ((int)$hours * 3600) + ((int)$minutes * 60) + (int)$seconds;
                    })
                    ->dehydrated(),
                TextInput::make("calories")->numeric()->required(),
                CuratorPicker::make("image_id")
                    ->relationship("imageAddress", "path")
                    ->directory("media/images")
                    ->disk("public")->visibility("public")->required(),
                Select::make("tags")->relationship("tags", "name")
                    ->multiple()->preload()->searchable()
                    ->createOptionForm([
                        TextInput::make("name")->required(),
                    ]),
                Section::make("Nutrition Facts")
                    ->columns(3)
                    ->schema([
                        Placeholder::make('fat')
                            ->content(fn($record): string => $record?->nutrition?->fat ?? '-'),
                        Placeholder::make('carbs')
                            ->content(fn($record): string => $record?->nutrition?->carbs ?? '-'),
                        Placeholder::make('protein')
                            ->content(fn($record): string => $record?->nutrition?->protein ?? '-'),
                        Placeholder::make('cholesterol')
                            ->content(fn($record): string => $record?->nutrition?->cholesterol ?? '-'),
                        Placeholder::make('fiber')
                            ->content(fn($record): string => $record?->nutrition?->fiber ?? '-'),
                        Placeholder::make('saturated_fat')
                            ->content(fn($record): string => $record?->nutrition?->saturated_fat ?? '-'),
                        Placeholder::make('sugar')
                            ->content(fn($record): string => $record?->nutrition?->sugar ?? '-'),
                    ]),
                Section::make("Ingredients")
                    ->schema([
                        Repeater::make("recipeIngredients")
                            ->relationship()
                            ->hiddenLabel()
                            ->schema([
                                Select::make("ingredient_id")
                                ->options(Ingredient::all()->pluck("name","id"))
//                                    ->relationship("ingredient", "name")
                                    ->searchable()
                                    ->required(),
                                TextInput::make("amount")
                                    ->numeric()
                                    ->required(),
                                TextInput::make("unit")
                                    ->required()
                            ])->columns(3)
                    ])

                /*Section::make("Steps")->schema([
                    View::make("filament.partials.add-step-button")
                        ->columnSpanFull(),

                    Repeater::make("recipeAndSteps")
//                        ->hiddenLabel()
                        ->relationship("recipeAndSteps")
                        ->schema([
                            Select::make("step_id")
                                ->relationship("stepObj", "description")
                                ->label("Step")
                                ->allowHtml()
                                ->required(),
//                            TextInput::make("step"),

//                            Hidden::make("step")
//                                ->default(fn($get, $set, ?string $operation, ?int $index) => $index + 1),
                        ])
//                        ->extraItemActions([
//                            \Filament\Forms\Components\Actions\Action::make("createStep")
//                                ->label("Create new step")
//                                ->url(route("filament.admin.resources.steps.create"))
//                                ->openUrlInNewTab()
//                        ])
                        ->reorderable("step")
                        ->defaultItems(1)
                        ->minItems(1)
                        ->columnSpanFull()
                ])*/


//                Section::make("Steps")->schema([
//                    Repeater::make("recipeAndSteps")->relationship("recipeAndSteps")
//                        ->schema([
//                            Select::make("step_id")
//                                ->relationship("step", "description")
//                                ->searchable()
//                                ->preload()
//                                ->required()
//                                ->label("Step"),
//
//                            TextInput::make("step")
//                                ->numeric()
//                                ->required()
//                                ->label("Step"),
//                        ])
//                        ->orderable("step")
//                        ->collapsible()
//                        ->defaultItems(1)
//                        ->minItems(1)
//                        ->columnSpanFull()
//                ])
            ]);
    }






//TextInput::make("description")->autocomplete()->datalist(
//Step::query()->pluck("description")->all()
//),
//TextInput::make('time')
//->mask('99:99:99')
//->placeholder('HH:MM:SS')
//    // Show value in HH:MM:SS when editing
//->afterStateHydrated(function (TextInput $component, $state) {
//    if ($state) {
//        $hours = floor($state / 3600);
//        $minutes = floor(($state % 3600) / 60);
//        $seconds = $state % 60;
//        $component->state(sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds));
//    }
//})
//    // Convert HH:MM:SS back into seconds before saving
//->dehydrateStateUsing(function ($state) {
//    if (!$state) {
//        return 0;
//    }
//
//    [$hours, $minutes, $seconds] = array_pad(explode(':', $state), 3, 0);
//    return ((int)$hours * 3600) + ((int)$minutes * 60) + (int)$seconds;
//})
//->dehydrated(),
//FileUpload::make("thumbnail")->image()->imageEditor()->directory("images")
//->disk("public")->visibility("public")->required(),


    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("id")->sortable()
                    ->getStateUsing(fn($record) => $record->id),
                TextColumn::make("status"),
                ImageColumn::make("image_id")
                    ->getStateUsing(fn($record) => asset("/storage/"
                        . $record->imageAddress?->variants?->where("variant", "thumb")->value("path")))
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
                    ),
                TextColumn::make("name")->sortable()->searchable(),
                TextColumn::make("rate")->sortable()->badge(),
                TextColumn::make("time")->sortable()
                    ->getStateUsing(fn($record) => DataFormatter::formatDurationShort($record->time)),
                TextColumn::make("category.name")->sortable()->searchable(),
                TextColumn::make("calories")->sortable()->numeric(),
                TextColumn::make("tags.name")->searchable()
                    ->getStateUsing(fn($record) => $record->tags->implode("name", ", "))
                    ->limit(13)
            ])
            ->filters([
                SelectFilter::make("category.name")->label("Category")
                    ->relationship("category", "name"),
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
                SelectFilter::make("rate")->label("Rate")
                    ->options([
//                        0=>"All",
                        1 => "1+",
                        2 => "2+",
                        3 => "3+",
                        4 => "4+",
                        5 => "5"
                    ])->default(null)
                    ->query(function ($query, $state) {
                        if ($state && isset($state["value"]) && $state["value"] != null &&
                            !isEmpty($state["value"])) {
                            $query->where("rate", ">=", $state);
                        }
                    })
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
//            RelationManagers\StepsRelationManager::class,
            RelationManagers\RecipeAndStepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }
}




/*public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make("name")->required(),
            Select::make("recipe_category_id")
                ->relationship("category", "name")
                ->preload()->required()->preload()
                ->createOptionForm([
                    TextInput::make("name")->required(),
                    FileUpload::make("icon")->image()->imageEditor()
                        ->directory("images")->disk("public")
                        ->visibility("public")->required()
                ]),
            Select::make("rate")->required()->options([
                1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5
            ]),
            TextInput::make('time')
                ->mask('99:99:99')
                ->placeholder('HH:MM:SS')
                // Show value in HH:MM:SS when editing
                ->afterStateHydrated(function (TextInput $component, $state) {
                    if ($state) {
                        $hours = floor($state / 3600);
                        $minutes = floor(($state % 3600) / 60);
                        $seconds = $state % 60;
                        $component->state(sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds));
                    }
                })
                // Convert HH:MM:SS back into seconds before saving
                ->dehydrateStateUsing(function ($state) {
                    if (!$state) {
                        return 0;
                    }

                    [$hours, $minutes, $seconds] = array_pad(explode(':', $state), 3, 0);
                    return ((int)$hours * 3600) + ((int)$minutes * 60) + (int)$seconds;
                })
                ->dehydrated(true),
            TextInput::make("calories")->numeric()->required(),
            FileUpload::make("thumbnail")->image()->imageEditor()->directory("images")
                ->disk("public")->visibility("public")->required(),
        ]);
}*/
