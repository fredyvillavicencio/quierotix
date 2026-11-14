<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el teléfono de contacto del participante.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('participants', 'telefono')) {
            Schema::table('participants', function (Blueprint $table) {
                $table->string('telefono')->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('participants', 'telefono')) {
            Schema::table('participants', function (Blueprint $table) {
                $table->dropColumn('telefono');
            });
        }
    }
};
