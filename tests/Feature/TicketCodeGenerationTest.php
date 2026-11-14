<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventLocation;
use App\Models\Participant;
use App\Models\Ticket;
use App\Services\SeatAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicketCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_receives_random_number_and_composed_code(): void
    {
        [$event, $participant] = $this->createEventAndParticipant();
        $ticket = $this->createTicket($event, $participant);

        $this->assertIsInt($ticket->numero);
        $this->assertGreaterThanOrEqual(0, $ticket->numero);
        $this->assertLessThanOrEqual(SeatAssignmentService::DEFAULT_LOCATION_CAPACITY - 1, $ticket->numero);
        $this->assertSame("B{$participant->id}{$ticket->id}", $ticket->codigo);
        $this->assertSame($ticket->codigo, $ticket->ticket_file_code);
    }

    public function test_ticket_numbers_are_unique_within_the_same_event(): void
    {
        [$event, $participant] = $this->createEventAndParticipant();
        $first = $this->createTicket($event, $participant, 'Backstage', 1);
        $second = $this->createTicket($event, $participant, 'Backstage', 2);

        $this->assertNotSame($first->numero, $second->numero);
        $this->assertSame(
            2,
            Ticket::where('event_id', $event->id)->pluck('numero')->unique()->count()
        );
    }

    public function test_ticket_numbers_use_the_sum_of_event_location_capacities(): void
    {
        [$event, $participant] = $this->createEventAndParticipant();

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 3,
        ]);

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 250,
            'total_asientos' => 2,
        ]);

        $tickets = [
            $this->createTicket($event, $participant, 'General', 1),
            $this->createTicket($event, $participant, 'General', 2),
            $this->createTicket($event, $participant, 'General', 3),
            $this->createTicket($event, $participant, 'VIP', 1),
            $this->createTicket($event, $participant, 'VIP', 2),
        ];

        $numbers = collect($tickets)
            ->pluck('numero')
            ->sort()
            ->values()
            ->all();

        $this->assertSame(range(0, 4), $numbers);
    }

    public function test_ticket_numbers_use_default_capacity_when_event_has_no_locations(): void
    {
        [$event, $participant] = $this->createEventAndParticipant();

        $tickets = [];

        for ($seat = 1; $seat <= SeatAssignmentService::DEFAULT_LOCATION_CAPACITY; $seat++) {
            $tickets[] = $this->createTicket(
                $event,
                $participant,
                SeatAssignmentService::DEFAULT_LOCATION_NAME,
                $seat
            );
        }

        $numbers = collect($tickets)
            ->pluck('numero')
            ->sort()
            ->values()
            ->all();

        $this->assertSame(
            range(0, SeatAssignmentService::DEFAULT_LOCATION_CAPACITY - 1),
            $numbers
        );
    }

    public function test_photographer_ticket_code_uses_f_initial(): void
    {
        [$event, $participant] = $this->createEventAndParticipant();
        $ticket = $this->createTicket($event, $participant, Ticket::PHOTOGRAPHER_LOCATION, 1);

        $this->assertSame("F{$participant->id}{$ticket->id}", $ticket->codigo);
    }

    public function test_ticket_code_is_recalculated_when_location_changes(): void
    {
        [$event, $participant] = $this->createEventAndParticipant();
        $ticket = $this->createTicket($event, $participant, 'Backstage', 1);

        $ticket->update(['locacion' => 'VIP']);

        $this->assertSame("V{$participant->id}{$ticket->id}", $ticket->fresh()->codigo);
    }

    public function test_ticket_number_and_code_are_reassigned_when_event_changes(): void
    {
        [$event, $participant] = $this->createEventAndParticipant();
        $ticket = $this->createTicket($event, $participant, 'Backstage', 1);

        [$otherEvent, $otherParticipant] = $this->createEventAndParticipant();

        $ticket->update([
            'event_id' => $otherEvent->id,
            'participant_id' => $otherParticipant->id,
        ]);

        $ticket->refresh();

        $this->assertSame($otherEvent->id, $ticket->event_id);
        $this->assertSame("B{$otherParticipant->id}{$ticket->id}", $ticket->codigo);
        $this->assertSame(
            0,
            Ticket::where('event_id', $otherEvent->id)
                ->where('id', '!=', $ticket->id)
                ->where('numero', $ticket->numero)
                ->count()
        );
    }

    /**
     * @return array{0: Event, 1: Participant}
     */
    private function createEventAndParticipant(): array
    {
        $event = Event::create([
            'codigo_evento' => 'evento-'.Str::lower(Str::random(10)),
            'nombre' => 'Evento de Generación de Códigos',
            'fecha' => '2031-05-20',
            'hora' => '19:00:00',
            'lugar' => 'Salón Principal',
            'direccion' => 'Avenida Principal 100',
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => Str::lower(Str::random(10)).'@example.com',
        ]);

        return [$event, $participant];
    }

    private function createTicket(Event $event, Participant $participant, string $locacion = 'Backstage', int $asiento = 1): Ticket
    {
        return Ticket::create([
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'codigo_qr' => (string) Str::uuid(),
            'locacion' => $locacion,
            'asiento' => $asiento,
            'archivo_pdf' => '',
            'validado' => false,
            'contador_validaciones' => 0,
        ]);
    }
}
