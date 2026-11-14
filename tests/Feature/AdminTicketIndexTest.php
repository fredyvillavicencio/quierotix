<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminTicketIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ticket_index_shows_location_label_with_initial_participant_and_ticket_ids(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = Event::create([
            'codigo_evento' => 'evento-admin-tickets',
            'nombre' => 'Evento de Tickets',
            'fecha' => '2031-03-10',
            'hora' => '18:00:00',
            'lugar' => 'Salón Principal',
            'direccion' => 'Avenida Principal 100',
        ]);
        $participant = Participant::create([
            'event_id' => $event->id,
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana-tickets@example.com',
        ]);
        $ticket = Ticket::create([
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'codigo_qr' => (string) Str::uuid(),
            'locacion' => 'Backstage',
            'asiento' => 115,
            'archivo_pdf' => '',
            'validado' => false,
            'contador_validaciones' => 0,
        ]);

        $expectedLabel = "Backstage B{$participant->id}{$ticket->id}";

        $this->assertSame($expectedLabel, $ticket->location_label);

        $this->actingAs($admin)
            ->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertSeeText($expectedLabel)
            ->assertDontSeeText('Locación Backstage')
            ->assertDontSeeText('Asiento 115');
    }
}
