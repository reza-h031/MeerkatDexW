<?php

namespace App\Filament\MyComponents\Forms\Components;

use Filament\Forms\Components\TextInput;

class TimeInput extends TextInput
{
    public function setUp(): void
    {
        parent::setUp();

        $this
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
            ->dehydrated();
    }
}
