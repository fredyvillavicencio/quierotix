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
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedSmallInteger('numero')->nullable()->after('codigo_qr');
            $table->string('codigo', 64)->nullable()->after('numero');
        });

        $usedNumbers = [];

        foreach (DB::table('tickets')->orderBy('id')->get(['id', 'event_id', 'participant_id', 'locacion']) as $ticket) {
            $eventId = (int) $ticket->event_id;
            $used = $usedNumbers[$eventId] ?? [];
            $available = array_values(array_diff(range(0, 999), $used));

            if ($available === []) {
                throw new RuntimeException(
                    "No hay números disponibles para el evento {$eventId}."
                );
            }

            $numero = $available[random_int(0, count($available) - 1)];
            $usedNumbers[$eventId][] = $numero;

            $location = trim((string) $ticket->locacion);
            $initial = $location === ''
                ? 'G'
                : mb_strtoupper(mb_substr($location, 0, 1));

            DB::table('tickets')
                ->where('id', $ticket->id)
                ->update([
                    'numero' => $numero,
                    'codigo' => $initial.$ticket->participant_id.$ticket->id,
                ]);
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->unique(['event_id', 'numero']);
            $table->unique('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'numero']);
            $table->dropUnique('codigo');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['numero', 'codigo']);
        });
    }
};
