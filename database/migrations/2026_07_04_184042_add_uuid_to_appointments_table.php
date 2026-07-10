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
        Schema::table('appointments', function (Blueprint $table) {
            // Agregamos la columna uuid después del id. 
            // Le ponemos nullable() para que no de error si ya tenés turnos viejos guardados.
            $table->uuid('uuid')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Si alguna vez deshacemos la migración, borramos la columna.
            $table->dropColumn('uuid');
        });
    }
};