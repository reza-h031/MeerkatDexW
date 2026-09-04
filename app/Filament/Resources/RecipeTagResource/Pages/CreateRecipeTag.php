<?php

namespace App\Filament\Resources\RecipeTagResource\Pages;

use App\Filament\Resources\RecipeTagResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRecipeTag extends CreateRecord
{
    protected static string $resource = RecipeTagResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl("index");
    }
}
