<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla de archivos
        Schema::create('category_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['pdf', 'doc', 'audio', 'video']);
            $table->string('drive_id');
            $table->string('nombre_original')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'tipo']);
        });

        // 2. Migrar archivos existentes de las columnas antiguas
        DB::table('categories')->where(function ($q) {
            $q->whereNotNull('pdf')->orWhereNotNull('audio')->orWhereNotNull('video');
        })->orderBy('id')->each(function ($cat) {
            if ($cat->pdf) {
                DB::table('category_files')->insert([
                    'category_id'     => $cat->id,
                    'tipo'            => 'pdf',
                    'drive_id'        => $cat->pdf,
                    'nombre_original' => null,
                    'orden'           => 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
            if ($cat->audio) {
                DB::table('category_files')->insert([
                    'category_id'     => $cat->id,
                    'tipo'            => 'audio',
                    'drive_id'        => $cat->audio,
                    'nombre_original' => null,
                    'orden'           => 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
            if ($cat->video) {
                DB::table('category_files')->insert([
                    'category_id'     => $cat->id,
                    'tipo'            => 'video',
                    'drive_id'        => $cat->video,
                    'nombre_original' => null,
                    'orden'           => 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        });

        // 3. Eliminar columnas antiguas
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['pdf', 'audio', 'video']);
        });
    }

    public function down(): void
    {
        // Restaurar columnas antiguas
        Schema::table('categories', function (Blueprint $table) {
            $table->string('pdf')->nullable();
            $table->string('audio')->nullable();
            $table->string('video')->nullable();
        });

        Schema::dropIfExists('category_files');
    }
};
