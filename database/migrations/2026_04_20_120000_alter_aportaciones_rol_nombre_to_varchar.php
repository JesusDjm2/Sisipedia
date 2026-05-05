<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El enum original no incluía «Equipo Puklla»; pasamos a VARCHAR para todos los roles.
     */
    public function up(): void
    {
        if (! Schema::hasTable('aportaciones')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE aportaciones MODIFY rol_nombre VARCHAR(64) NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite no usa ENUM igual; si hiciera falta, recrear tabla en un entorno concreto.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('aportaciones')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE aportaciones MODIFY rol_nombre ENUM('Equipo Puklla','Docente','Líder','Niño/Estudiante') NOT NULL");
        }
    }
};
