<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditRecipe extends EditRecord
{
    protected static string $resource = RecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

//    protected function getFormActions(): array
//    {
//        return array_merge(
//            parent::getFormActions(),
//            [
//                Action::make("createStep")
//                    ->label("Create Step")
//                    ->color("primary")
//                    ->action(function ($livewire, $data) {
//                        $recipe = $livewire->form->model;
//
//                        $livewire->form->saveRelationships();
//                        $recipe->fill($data);
//                        $recipe->status = "draft";
//                        $recipe->save();
//
//                        return redirect()->route("filament.admin.resources.steps.create", [
//                            "recipe" => $recipe->id,
//                        ]);
//                    })
//            ]
//        );
//    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl("index");
    }
}
