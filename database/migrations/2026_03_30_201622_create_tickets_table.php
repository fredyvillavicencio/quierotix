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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('codigo_qr')->unique();
            $table->integer('bloque'); // 1-5
            $table->string('fila'); // A-Ñ
            $table->integer('asiento'); // 1-35
            $table->string('archivo_pdf');
            $table->boolean('validado')->default(false);
            $table->integer('contador_validaciones')->default(0);
            $table->timestamps();
            
            $table->unique(['event_id', 'fila', 'asiento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
