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

            $table->foreignId('game_id')
                ->constrained('games')
                ->cascadeOnDelete();

            $table->foreignId('platform_id')
                ->constrained('platforms')
                ->cascadeOnDelete();

            $table->text('minimum_requirements')->nullable();
            $table->text('recommended_requirements')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_requirements');
    }
};