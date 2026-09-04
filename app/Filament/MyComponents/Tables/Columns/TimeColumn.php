<?php

namespace App\Filament\MyComponents\Tables\Columns;

use App\Utils\DataFormatter;
use Filament\Tables\Columns\TextColumn;

class TimeColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->sortable()
            ->getStateUsing(fn($record) => DataFormatter::formatDurationShort($record->time));
    }
}
