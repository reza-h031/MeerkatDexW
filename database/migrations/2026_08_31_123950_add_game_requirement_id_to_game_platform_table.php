<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('game_platform', function (Blueprint $table) {
        $table->foreignId('game_requirement_id')
            ->nullable()
            ->constrained('game_requirements')
            ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('game_platform', function (Blueprint $table) {
        $table->dropForeign(['game_requirement_id']);
        $table->dropColumn('game_requirement_id');
    });
}
};
