<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('game_requirements', function (Blueprint $table) {
        $table->dropForeign(['game_id']);
        $table->dropForeign(['platform_id']);

        $table->dropColumn([
            'game_id',
            'platform_id',
        ]);
    });
}

public function down(): void
{
    Schema::table('game_requirements', function (Blueprint $table) {
        $table->foreignId('game_id')
            ->constrained('games')
            ->cascadeOnDelete();

        $table->foreignId('platform_id')
            ->constrained('platforms')
            ->cascadeOnDelete();
    });
}
};
