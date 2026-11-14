<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GiftRaffleTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(): Event
    {
        return Event::create([
            'codigo_evento' => 'evento-'.Str::lower(Str::random(10)),
            'nombre' => 'Evento de Sorteo',
            'fecha' => '2030-12-31',
            'hora' => '20:00:00',
            'lugar' => 'Salón Principal',
            'direccion' => 'Avenida Principal 100',
        ]);
    }

    private function createParticipant(Event $event): Participant
    {
        return Participant::create([
            'event_id' => $event->id,
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana.'.Str::lower(Str::random(6)).'@example.com',
        ]);
    }

    private function createTicket(Event $event, Participant $participant, string $locacion = 'Backstage'): Ticket
    {
        return Ticket::create([
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'codigo_qr' => (string) Str::uuid(),
            'locacion' => $locacion,
            'asiento' => 1,
            'archivo_pdf' => '',
            'validado' => false,
            'contador_validaciones' => 0,
        ]);
    }

    public function test_gift_raffle_page_is_public_and_contains_the_spin_button(): void
    {
        $this->get(route('gifts.index'))
            ->assertOk()
            ->assertSee('Iniciar sorteo')
            ->assertSee('public/images/gift-ticket.svg')
            ->assertDontSee('Pulsa el botón para iniciar el sorteo.')
            ->assertDontSee('gift-status');
    }

    public function test_random_gift_returns_a_ticket_code_from_the_database(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $ticket = $this->createTicket($event, $participant);

        $expectedCode = "B{$participant->id}{$ticket->id}";

        $this->getJson(route('gifts.random'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertExactJson(['code' => $expectedCode]);
    }

    public function test_random_gift_returns_not_found_when_there_are_no_tickets(): void
    {
        $this->getJson(route('gifts.random'))
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'No hay tickets registrados para sortear.',
            ]);
    }
}
