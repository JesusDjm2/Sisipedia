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
        Schema::create('aportaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('nombre_ol');
            $table->string('institucion')->nullable();
            $table->string('ubicacion')->nullable();
            $table->text('detalle')->nullable();
            $table->string('titulo')->nullable()->after('nombre_ol');
            $table->string('lugar_trabajo')->nullable()->after('institucion');
            $table->string('imagen')->nullable()->after('detalle');
            $table->string('pdf')->nullable();
            $table->string('doc')->nullable();
            $table->string('audio')->nullable();
            $table->string('video')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aportaciones');
    }
};
