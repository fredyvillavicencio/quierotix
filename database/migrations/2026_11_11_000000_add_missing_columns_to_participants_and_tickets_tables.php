<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alinea el esquema de las migraciones con columnas que ya existen en
     * producción y que el código utiliza (carne, email_sent_at y validado_por).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('participants', 'carne')) {
            Schema::table('participants', function (Blueprint $table) {
                $table->string('carne')->nullable()->after('event_id');
            });
        }

        if (! Schema::hasColumn('participants', 'email_sent_at')) {
            Schema::table('participants', function (Blueprint $table) {
                $table->timestamp('email_sent_at')->nullable()->after('email_sent');
            });
        }

        if (! Schema::hasColumn('tickets', 'validado_por')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->unsignedBigInteger('validado_por')->nullable()->after('contador_validaciones');
            });
        }
    }

    /**
     * No eliminamos las columnas en el rollback porque pueden haber existido
     * antes de esta migración en bases de datos de producción.
     */
    public function down(): void
    {
        //
    }
};
