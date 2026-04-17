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
        Schema::table('aportaciones', function (Blueprint $table) {
            $table->enum('rol_nombre', ['Docente', 'Líder', 'Niño/Estudiante'])
                  ->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('aportaciones', function (Blueprint $table) {
            $table->dropColumn('rol_nombre');
        });
    }
};
