<?php

namespace App\Models\filters;

use Illuminate\Http\Request;

class GameFilter
{
    private string $name;

    public function __construct(
        string $name = null
    ) {
        $this->name = $name ?? "";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }

    public static function fromArray(Request $request): GameFilter
    {
        $request->validate([
            'name' => 'max:100',
        ]);

        return new GameFilter(
            $request->input('name')
        );
    }
}