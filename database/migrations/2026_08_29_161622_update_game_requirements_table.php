<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_requirements', function (Blueprint $table) {

            $table->dropColumn([
                'minimum_requirements',
                'recommended_requirements',
            ]);

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
        Schema::table('game_requirements', function (Blueprint $table) {

            $table->dropForeign([
                'minimum_requirement_id',
            ]);

            $table->dropForeign([
                'recommended_requirement_id',
            ]);

            $table->dropColumn([
                'minimum_requirement_id',
                'recommended_requirement_id',
            ]);

            $table->string('minimum_requirements')->nullable();
            $table->string('recommended_requirements')->nullable();
        });
    }
};