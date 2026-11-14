<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega los identificadores de producto de Recurrente al evento.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('recurrente_product_id')->nullable()->unique()->after('codigo_evento');
            $table->string('recurrente_product_url', 2048)->nullable()->after('recurrente_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['recurrente_product_id']);
            $table->dropColumn(['recurrente_product_id', 'recurrente_product_url']);
        });
    }
};
