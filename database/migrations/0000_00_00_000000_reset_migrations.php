<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Esta migración limpia todas las tablas para empezar fresco.
     */
    public function up(): void
    {
        // Eliminar tablas en orden inverso para evitar conflictos de foreign keys
        Schema::dropIfExists('pieces');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('failed_jobs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Esta migración no se puede revertir
        throw new Exception('This migration cannot be rolled back.');
    }
};
