<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_id')
                ->constrained('games')
                ->cascadeOnDelete();

            $table->string('version');
            $table->date('release_date')->nullable();
            $table->string('download_size')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_versions');
    }
};