<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use App\Services\CsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ParticipantEmailUniquenessTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'codigo_evento' => 'evento-'.Str::lower(Str::random(10)),
            'nombre' => 'Evento de Prueba',
            'fecha' => '2030-06-15',
            'hora' => '19:00:00',
            'lugar' => 'Salón Principal',
            'direccion' => 'Avenida Siempre Viva 742',
        ], $overrides));
    }

    public function test_participants_table_has_no_unique_index_on_event_and_email(): void
    {
        $hasUniqueEventEmailIndex = collect(Schema::getIndexes('participants'))
            ->contains(function (array $index): bool {
                if (! ($index['unique'] ?? false)) {
                    return false;
                }

                $columns = array_map('strtolower', $index['columns'] ?? []);

                return count($columns) === 2
                    && in_array('event_id', $columns, true)
                    && in_array('email', $columns, true);
            });

        $this->assertFalse(
            $hasUniqueEventEmailIndex,
            'La tabla participants no debe tener un índice único sobre event_id y email.'
        );
    }

    public function test_csv_import_allows_duplicate_email(): void
    {
        $event = $this->createEvent();

        Participant::create([
            'event_id' => $event->id,
            'nombre' => 'María',
            'apellido' => 'López',
            'email' => 'duplicado@example.com',
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'participantes.csv',
            "nombre,apellido,email\nAna,Pérez,duplicado@example.com\n"
        );

        $results = app(CsvImportService::class)->importParticipants($event, $file);

        $this->assertSame(1, $results['success']);
        $this->assertSame(0, $results['failed']);
        $this->assertSame(
            2,
            Participant::where('event_id', $event->id)
                ->where('email', 'duplicado@example.com')
                ->count()
        );
    }

    public function test_admin_can_create_and_update_participants_with_duplicate_email(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent();

        Participant::create([
            'event_id' => $event->id,
            'nombre' => 'María',
            'apellido' => 'López',
            'email' => 'duplicado@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.participants.store'), [
                'event_id' => $event->id,
                'nombre' => 'Ana',
                'apellido' => 'Pérez',
                'email' => 'duplicado@example.com',
            ])
            ->assertRedirect(route('admin.participants.index'));

        $duplicateParticipants = Participant::where('event_id', $event->id)
            ->where('email', 'duplicado@example.com');

        $this->assertSame(2, $duplicateParticipants->count());

        $participant = $duplicateParticipants->latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.participants.update', $participant), [
                'event_id' => $event->id,
                'nombre' => 'Ana María',
                'apellido' => 'Pérez',
                'email' => 'duplicado@example.com',
            ])
            ->assertRedirect(route('admin.participants.index'));

        $this->assertSame('Ana María', $participant->fresh()->nombre);
        $this->assertSame(
            2,
            Participant::where('event_id', $event->id)
                ->where('email', 'duplicado@example.com')
                ->count()
        );
    }
}
