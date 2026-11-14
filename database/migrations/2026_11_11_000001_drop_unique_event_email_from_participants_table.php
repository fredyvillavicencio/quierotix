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
        if (! Schema::hasTable('participants')) {
            return;
        }

        $indexes = Schema::getIndexes('participants');
        $uniqueIndex = null;

        foreach ($indexes as $index) {
            if (! ($index['unique'] ?? false)) {
                continue;
            }

            if ($this->matchesEventEmailColumns($index['columns'] ?? [])) {
                $uniqueIndex = $index;
                break;
            }
        }

        if ($uniqueIndex === null || empty($uniqueIndex['name'])) {
            return;
        }

        $hasEventIndex = false;

        foreach ($indexes as $index) {
            if (($index['name'] ?? null) === $uniqueIndex['name']) {
                continue;
            }

            $columns = $index['columns'] ?? [];

            if (($columns[0] ?? null) === 'event_id') {
                $hasEventIndex = true;
                break;
            }
        }

        // MySQL uses the composite unique index to satisfy the event_id foreign
        // key. Add a regular index first so dropping the unique index is safe.
        if (! $hasEventIndex) {
            Schema::table('participants', function (Blueprint $table) {
                $table->index('event_id');
            });
        }

        Schema::table('participants', function (Blueprint $table) use ($uniqueIndex) {
            $table->dropUnique($uniqueIndex['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se restaura la restricción única porque ya podrían existir
        // participantes con el mismo correo en un evento.
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function matchesEventEmailColumns(array $columns): bool
    {
        $columns = array_map('strtolower', $columns);

        return count($columns) === 2
            && in_array('event_id', $columns, true)
            && in_array('email', $columns, true);
    }
};
