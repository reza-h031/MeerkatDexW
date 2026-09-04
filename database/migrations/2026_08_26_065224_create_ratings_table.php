<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_id')
                ->constrained('games')
                ->cascadeOnDelete();

            $table->string('source');
            $table->string('logo_source')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->unsignedBigInteger('rating_count')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};