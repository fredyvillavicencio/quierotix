<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminListingEventFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Event $eventA;

    private Event $eventB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->eventA = $this->createEvent('evento-filtro-a', 'Evento Alfa');
        $this->eventB = $this->createEvent('evento-filtro-b', 'Evento Beta');
    }

    public function test_participants_index_filters_by_event(): void
    {
        $this->createParticipant($this->eventA, 'Ana', 'Alfa', 'ana-alfa@example.com');
        $this->createParticipant($this->eventB, 'Beto', 'Beta', 'beto-beta@example.com');

        $response = $this->actingAs($this->admin)->get(route('admin.participants.index', [
            'event_id' => $this->eventA->id,
        ]));

        $response->assertOk()
            ->assertSeeText('Ana Alfa')
            ->assertDontSeeText('Beto Beta')
            ->assertSeeText('Encontrados: 1 participante(s)');
    }

    public function test_participants_index_combines_event_and_search_filters(): void
    {
        $this->createParticipant($this->eventA, 'Carla', 'Comun', 'carla-comun@example.com');
        $this->createParticipant($this->eventA, 'Carlos', 'Comun', 'carlos-comun@example.com');
        $this->createParticipant($this->eventB, 'Carla', 'Externa', 'carla-externa@example.com');

        $response = $this->actingAs($this->admin)->get(route('admin.participants.index', [
            'event_id' => $this->eventA->id,
            'search' => 'Carla',
        ]));

        $response->assertOk()
            ->assertSeeText('Carla Comun')
            ->assertDontSeeText('Carlos Comun')
            ->assertDontSeeText('Carla Externa');
    }

    public function test_participants_index_without_filters_shows_all_events(): void
    {
        $this->createParticipant($this->eventA, 'Ana', 'Alfa', 'ana-alfa@example.com');
        $this->createParticipant($this->eventB, 'Beto', 'Beta', 'beto-beta@example.com');

        $response = $this->actingAs($this->admin)->get(route('admin.participants.index'));

        $response->assertOk()
            ->assertSeeText('Ana Alfa')
            ->assertSeeText('Beto Beta');
    }

    public function test_participants_pagination_preserves_event_filter(): void
    {
        for ($i = 1; $i <= 26; $i++) {
            $this->createParticipant(
                $this->eventA,
                "Participante{$i}",
                'Alfa',
                "participante-{$i}@example.com"
            );
        }

        $this->createParticipant($this->eventB, 'Beto', 'Beta', 'beto-beta@example.com');

        $response = $this->actingAs($this->admin)->get(route('admin.participants.index', [
            'event_id' => $this->eventA->id,
            'page' => 2,
        ]));

        $response->assertOk();

        $participants = $response->viewData('participants');
        $this->assertSame(26, $participants->total());
        $this->assertCount(1, $participants->items());

        $this->assertStringContainsString(
            'event_id='.$this->eventA->id,
            $response->getContent()
        );
    }

    public function test_tickets_index_filters_by_event(): void
    {
        $participantA = $this->createParticipant($this->eventA, 'Ana', 'Alfa', 'ana-alfa@example.com');
        $participantB = $this->createParticipant($this->eventB, 'Beto', 'Beta', 'beto-beta@example.com');

        $this->createTicket($participantA);
        $this->createTicket($participantB);

        $response = $this->actingAs($this->admin)->get(route('admin.tickets.index', [
            'event_id' => $this->eventA->id,
        ]));

        $response->assertOk()
            ->assertSeeText('Ana Alfa')
            ->assertDontSeeText('Beto Beta')
            ->assertSeeText('Total: 1 ticket(s)');
    }

    public function test_tickets_index_combines_event_and_search_filters(): void
    {
        $anaEventoA = $this->createParticipant($this->eventA, 'Ana', 'Alfa', 'ana-alfa@example.com');
        $betoEventoA = $this->createParticipant($this->eventA, 'Beto', 'Alfa', 'beto-alfa@example.com');
        $anaEventoB = $this->createParticipant($this->eventB, 'Ana', 'Beta', 'ana-beta@example.com');

        $this->createTicket($anaEventoA);
        $this->createTicket($betoEventoA);
        $this->createTicket($anaEventoB);

        $response = $this->actingAs($this->admin)->get(route('admin.tickets.index', [
            'event_id' => $this->eventA->id,
            'search' => 'Ana',
        ]));

        $response->assertOk()
            ->assertSeeText('Ana Alfa')
            ->assertDontSeeText('Beto Alfa')
            ->assertDontSeeText('Ana Beta');
    }

    public function test_both_indexes_render_event_filter_with_selected_event(): void
    {
        foreach (['admin.participants.index', 'admin.tickets.index'] as $routeName) {
            $response = $this->actingAs($this->admin)->get(route($routeName, [
                'event_id' => $this->eventA->id,
            ]));

            $response->assertOk()
                ->assertSee('id="eventFilter"', false)
                ->assertSeeText('Todos los eventos')
                ->assertSee('value="'.$this->eventA->id.'" selected', false);
        }
    }

    private function createEvent(string $codigo, string $nombre): Event
    {
        return Event::create([
            'codigo_evento' => $codigo,
            'nombre' => $nombre,
            'fecha' => '2031-03-10',
            'hora' => '18:00:00',
            'lugar' => 'Salón Principal',
            'direccion' => 'Avenida Principal 100',
        ]);
    }

    private function createParticipant(Event $event, string $nombre, string $apellido, string $email): Participant
    {
        return Participant::create([
            'event_id' => $event->id,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
        ]);
    }

    private function createTicket(Participant $participant): Ticket
    {
        return Ticket::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'codigo_qr' => (string) Str::uuid(),
            'locacion' => 'General',
            'asiento' => $participant->id,
            'archivo_pdf' => '',
            'validado' => false,
            'contador_validaciones' => 0,
        ]);
    }
}
