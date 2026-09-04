<?php

namespace App\Filament\Resources\RecipeTagResource\Pages;

use App\Filament\Resources\RecipeTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecipeTag extends EditRecord
{
    protected static string $resource = RecipeTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl("index");
    }
}
