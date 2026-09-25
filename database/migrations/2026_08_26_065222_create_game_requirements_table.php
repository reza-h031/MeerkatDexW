<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_requirements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('minimum_requirement_id')
                ->nullable()
                ->constrained('requirements')
                ->nullOnDelete();

            $table->foreignId('recommended_requirement_id')
                ->nullable()
                ->constrained('requirements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_requirements');
    }
};