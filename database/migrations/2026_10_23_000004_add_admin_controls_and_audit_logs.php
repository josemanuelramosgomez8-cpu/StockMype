<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table): void {
            $table->boolean('activo')->default(true)->index();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('activo')->default(true)->index();
        });

        Schema::create('registros_auditoria_admin', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_nombre');
            $table->string('evento', 100);
            $table->string('entidad', 100)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->text('descripcion');
            $table->json('detalles')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['evento', 'created_at']);
            $table->index(['entidad', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_auditoria_admin');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['activo']);
            $table->dropColumn('activo');
        });

        Schema::table('negocios', function (Blueprint $table): void {
            $table->dropIndex(['activo']);
            $table->dropColumn('activo');
        });
    }
};
