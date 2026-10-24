<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('negocio_id')
                ->nullable()
                ->after('id')
                ->constrained('negocios')
                ->nullOnDelete();
            $table->string('rol', 20)->default('empleado')->after('email');
            $table->index(['negocio_id', 'rol']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['negocio_id', 'rol']);
            $table->dropConstrainedForeignId('negocio_id');
            $table->dropColumn('rol');
        });
    }
};
