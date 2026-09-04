<?php

namespace App\Filament\Resources\StepResource\Pages;

use App\Filament\Resources\StepResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CreateStep extends CreateRecord
{
    protected static string $resource = StepResource::class;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getRedirectUrl(): string
    {
        $recipeId = request()->get("recipe");
        if ($recipeId) {
            return route("filament.resources.recipes.edit", $recipeId);
        }


        return $this->getResource()::getUrl("index");
    }
}
