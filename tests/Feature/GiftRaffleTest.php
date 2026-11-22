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

    private function createEvent(string $nombre = 'Evento de Sorteo'): Event
    {
        return Event::create([
            'codigo_evento' => 'evento-'.Str::lower(Str::random(10)),
            'nombre' => $nombre,
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

    public function test_gift_selection_page_lists_only_events_with_tickets(): void
    {
        $eventWithTickets = $this->createEvent('Evento con tickets');
        $participant = $this->createParticipant($eventWithTickets);
        $this->createTicket($eventWithTickets, $participant);

        $eventWithoutTickets = $this->createEvent('Evento sin tickets');

        $this->get(route('gifts.select'))
            ->assertOk()
            ->assertSee('Elige un evento')
            ->assertSee($eventWithTickets->nombre)
            ->assertDontSee($eventWithoutTickets->nombre)
            ->assertSee('name="event_id"', false)
            ->assertSee('value="'.$eventWithTickets->id.'"', false)
            ->assertSee('Continuar');
    }

    public function test_gift_selection_page_shows_empty_state_when_there_are_no_tickets(): void
    {
        $this->createEvent('Evento sin tickets');

        $this->get(route('gifts.select'))
            ->assertOk()
            ->assertSee('No hay eventos con tickets');
    }

    public function test_gift_raffle_page_is_public_for_the_selected_event(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $this->createTicket($event, $participant);

        $this->post(route('gifts.index'), ['event_id' => $event->id])
            ->assertOk()
            ->assertSee($event->nombre)
            ->assertSee('Iniciar sorteo')
            ->assertDontSee('Cambiar evento')
            ->assertSee('public/images/gift-ticket.svg')
            ->assertDontSee('Pulsa el botón para iniciar el sorteo.')
            ->assertDontSee('gift-status');
    }

    public function test_gift_raffle_page_redirects_to_selection_without_an_event(): void
    {
        $this->get(route('gifts.index'))
            ->assertRedirect(route('gifts.select'));
    }

    public function test_gift_raffle_page_redirects_when_the_event_has_no_tickets(): void
    {
        $event = $this->createEvent('Evento sin tickets');

        $this->post(route('gifts.index'), ['event_id' => $event->id])
            ->assertRedirect(route('gifts.select'))
            ->assertSessionHas('error', 'El evento seleccionado no tiene tickets disponibles para el sorteo.');
    }

    public function test_gift_raffle_page_redirects_when_the_event_does_not_exist(): void
    {
        $this->post(route('gifts.index'), ['event_id' => 999999])
            ->assertRedirect(route('gifts.select'))
            ->assertSessionHas('error', 'El evento seleccionado no tiene tickets disponibles para el sorteo.');
    }

    public function test_random_gift_returns_the_numero_from_the_selected_event(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $ticket = $this->createTicket($event, $participant);

        $this->getJson(route('gifts.random', ['event_id' => $event->id]))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertExactJson(['numero' => $ticket->numero]);
    }

    public function test_random_gift_does_not_use_tickets_from_another_event(): void
    {
        $selectedEvent = $this->createEvent('Evento seleccionado');
        $selectedParticipant = $this->createParticipant($selectedEvent);
        $selectedTicket = $this->createTicket($selectedEvent, $selectedParticipant);

        $otherEvent = $this->createEvent('Otro evento');
        $otherParticipant = $this->createParticipant($otherEvent);
        $this->createTicket($otherEvent, $otherParticipant);

        $this->getJson(route('gifts.random', ['event_id' => $selectedEvent->id]))
            ->assertOk()
            ->assertExactJson(['numero' => $selectedTicket->numero]);
    }

    public function test_random_gift_requires_an_event(): void
    {
        $this->getJson(route('gifts.random'))
            ->assertStatus(422)
            ->assertExactJson([
                'message' => 'Selecciona un evento para realizar el sorteo.',
            ]);
    }

    public function test_random_gift_returns_not_found_when_the_event_has_no_tickets(): void
    {
        $event = $this->createEvent('Evento sin tickets');

        $this->getJson(route('gifts.random', ['event_id' => $event->id]))
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'No hay tickets registrados para sortear.',
            ]);
    }
}
