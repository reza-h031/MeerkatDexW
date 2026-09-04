<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_platform', function (Blueprint $table) {
            $table->string('version')->nullable();
            $table->date('release_date')->nullable();
            $table->string('download_size')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('game_platform', function (Blueprint $table) {
            $table->dropColumn([
                'version',
                'release_date',
                'download_size',
            ]);
        });
    }
};