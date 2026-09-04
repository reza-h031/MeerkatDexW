<?php

namespace App\Filament\Resources\RecipeTagResource\Pages;

use App\Filament\Resources\RecipeTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecipeTags extends ListRecords
{
    protected static string $resource = RecipeTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
