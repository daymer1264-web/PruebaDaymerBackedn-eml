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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('email', 150)->unique();
            $table->string('telefono', 20);
            $table->string('password');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_ultima_modificacion')->useCurrent()->useCurrentOnUpdate();
            $table->rememberToken();
            $table->timestamps();
            
            // Índices para optimizar búsquedas
            $table->index('email');
            $table->index('estado');
            $table->index(['apellidos', 'nombres']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
