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
        Schema::table('events', function (Blueprint $table) {
            $table->string('lugar')->nullable()->after('hora');
            $table->text('direccion')->nullable()->after('lugar');
            $table->string('imagen')->nullable()->after('direccion');
            $table->string('imagen_ticket')->nullable()->after('imagen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['lugar', 'direccion', 'imagen', 'imagen_ticket']);
        });
    }
};
