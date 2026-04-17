<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aportaciones', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('category_id');
        });

        DB::table('aportaciones')->whereNotNull('category_id')->update(['is_approved' => true]);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            Schema::table('aportaciones', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });
            DB::statement('ALTER TABLE aportaciones MODIFY category_id BIGINT UNSIGNED NULL');
            Schema::table('aportaciones', function (Blueprint $table) {
                $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            });
        } else {
            // SQLite / otros: recrear tabla sería necesario para FK nullable; proyecto usa MySQL en producción.
            Schema::table('aportaciones', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('aportaciones', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::table('aportaciones')->whereNull('category_id')->delete();

            Schema::table('aportaciones', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });
            DB::statement('ALTER TABLE aportaciones MODIFY category_id BIGINT UNSIGNED NOT NULL');
            Schema::table('aportaciones', function (Blueprint $table) {
                $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            });
        }
    }
};
