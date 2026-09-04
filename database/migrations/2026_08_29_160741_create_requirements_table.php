<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('requirements', function (Blueprint $table) {
    $table->id();
    $table->string('ram')->nullable();
    $table->string('system_version')->nullable();
    $table->string('cpu')->nullable();
    $table->string('gpu')->nullable();
    $table->string('storage')->nullable();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirements');
    }
};
