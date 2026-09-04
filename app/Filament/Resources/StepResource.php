<?php

namespace App\Filament\Resources;

use App\Filament\MyComponents\Forms\Components\TimeInput;
use App\Filament\MyComponents\Tables\Columns\TimeColumn;
use App\Filament\Resources\StepResource\Pages;
use App\Filament\Resources\StepResource\RelationManagers;
use App\Models\Step;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class StepResource extends Resource
{
    protected static ?string $model = Step::class;

    protected static ?string $navigationGroup = "Recipes";
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make("description")->required()
                    ->disableToolbarButtons(["image", "attachFiles"]),
                TimeInput::make("time")->required(),
//                TimeInput::make("video_section_time")->required(),
                Forms\Components\Section::make("Images")->schema([
                    Forms\Components\Repeater::make("images")
                        ->hiddenLabel()
                        ->relationship("images")
                        ->schema([
                            CuratorPicker::make("address")
                                ->directory("media/images")
                                ->disk("public")
                                ->visibility("public")

//                            FileUpload::make("address")->image()
//                                ->imageEditor()->directory("images")
//                                ->disk("public")->visibility("public")->required(),
                        ])
                ]),
                Forms\Components\Section::make("Videos")->schema([
                    Forms\Components\Repeater::make("videos")
                        ->hiddenLabel()
                        ->relationship("videos")
                        ->schema([
                            CuratorPicker::make("address")
                                ->directory("media/videos")
                                ->acceptedFileTypes(["video/mp4"])
                                ->maxSize(10240)
                                ->disk("public")
                                ->visibility("public")
                                ->required(),
                        ])
                ]),

                Hidden::make("recipe_id")->default(fn() => request()->get("recipe"))
            ]);
    }

    public static function table(Table $table): Table
    {
//        dump(Step::with("images")->find(6)->images()->count());
        return $table
            ->columns([
                TextColumn::make("id"),
                TextColumn::make("description")->limit(33),
                TimeColumn::make("time")
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
            'index' => Pages\ListSteps::route('/'),
            'create' => Pages\CreateStep::route('/create'),
            'edit' => Pages\EditStep::route('/{record}/edit'),
        ];
    }
}
