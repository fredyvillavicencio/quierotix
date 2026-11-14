<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('tickets')->exists()) {
            throw new RuntimeException(
                'La migración de locaciones requiere que la tabla tickets esté vacía. '.
                'Respalda y vacía tickets/participants antes de ejecutarla.'
            );
        }

        $usesNamedForeignKeys = DB::getDriverName() !== 'sqlite';

        Schema::table('tickets', function (Blueprint $table) use ($usesNamedForeignKeys) {
            if ($usesNamedForeignKeys) {
                $table->dropForeign('tickets_event_id_foreign');
            }

            $table->dropUnique('tickets_event_id_fila_asiento_unique');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('locacion', 100)->default('General')->after('codigo_qr');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['bloque', 'fila']);
        });

        Schema::table('tickets', function (Blueprint $table) use ($usesNamedForeignKeys) {
            $table->unique(['event_id', 'locacion', 'asiento']);

            if ($usesNamedForeignKeys) {
                $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $usesNamedForeignKeys = DB::getDriverName() !== 'sqlite';

        Schema::table('tickets', function (Blueprint $table) use ($usesNamedForeignKeys) {
            if ($usesNamedForeignKeys) {
                $table->dropForeign('tickets_event_id_foreign');
            }

            $table->dropUnique('tickets_event_id_locacion_asiento_unique');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->integer('bloque')->default(0)->after('codigo_qr');
            $table->string('fila')->default('A')->after('bloque');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('locacion');
        });

        Schema::table('tickets', function (Blueprint $table) use ($usesNamedForeignKeys) {
            $table->unique(['event_id', 'fila', 'asiento']);

            if ($usesNamedForeignKeys) {
                $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            }
        });
    }
};
