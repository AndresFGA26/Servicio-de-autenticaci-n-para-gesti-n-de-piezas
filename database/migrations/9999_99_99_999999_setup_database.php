<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Esta migración asegura que todas las tablas necesarias existan
     * y configura la base de datos correctamente para ambos servicios.
     */
    public function up(): void
    {
        // Tabla users (si no existe)
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Tabla refresh_tokens (si no existe)
        if (!Schema::hasTable('refresh_tokens')) {
            Schema::create('refresh_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('token')->unique();
                $table->timestamp('expires_at');
                $table->timestamps();
                
                $table->index(['user_id', 'expires_at']);
            });
            
            // Agregar foreign key después de crear la tabla
            Schema::table('refresh_tokens', function (Blueprint $table) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            });
        }

        // Tabla projects (si no existe)
        if (!Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        // Tabla blocks (si no existe)
        if (!Schema::hasTable('blocks')) {
            Schema::create('blocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
                $table->string('name');
                $table->timestamps();
            });
        }

        // Tabla pieces (si no existe)
        if (!Schema::hasTable('pieces')) {
            Schema::create('pieces', function (Blueprint $table) {
                $table->id();
                $table->foreignId('block_id')->constrained('blocks')->onDelete('cascade');
                $table->decimal('peso_teorico', 10, 2);
                $table->decimal('peso_real', 10, 2)->nullable();
                $table->decimal('diferencia_peso', 10, 2)->nullable();
                $table->enum('estado', ['Pendiente', 'Fabricada'])->default('Pendiente');
                $table->timestamp('fecha_fabricacion')->nullable();
                $table->timestamps();
                
                $table->index(['block_id', 'estado']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pieces');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('users');
    }
};
