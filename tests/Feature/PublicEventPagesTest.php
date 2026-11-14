<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEventPagesTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'codigo_evento' => 'evento-prueba-001',
            'nombre' => 'Evento de Prueba Público',
            'fecha' => '2030-05-20',
            'hora' => '18:30:00',
            'lugar' => 'Salón de Pruebas',
            'direccion' => 'Calle Falsa 123, Ciudad de Prueba',
        ], $overrides));
    }

    public function test_homepage_is_public_and_lists_events(): void
    {
        $event = $this->createEvent();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Evento de Prueba Público');
        $response->assertSee('20/05/2030');
        $response->assertSee('18:30');
        $response->assertSee('Salón de Pruebas');
        $response->assertSee('Comprar tickets');
        $response->assertSee(route('events.public.purchase.create', $event), false);
    }

    public function test_homepage_shows_empty_state_without_events(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('No hay eventos disponibles');
    }

    public function test_event_detail_shows_all_event_data(): void
    {
        $event = $this->createEvent();

        $response = $this->get(route('events.public.show', $event));

        $response->assertOk();
        $response->assertSee($event->codigo_evento);
        $response->assertSee($event->nombre);
        $response->assertSee('20/05/2030');
        $response->assertSee('18:30');
        $response->assertSee($event->lugar);
        $response->assertSee($event->direccion);
    }

    public function test_event_detail_hides_address_when_not_provided(): void
    {
        $event = $this->createEvent(['direccion' => null]);

        $response = $this->get(route('events.public.show', $event));

        $response->assertOk();
        $response->assertSee($event->lugar);
        $response->assertDontSeeText('Dirección');
    }

    public function test_event_detail_shows_cover_image_when_present(): void
    {
        $event = $this->createEvent(['imagen' => 'images/events/portada-prueba.jpg']);

        $this->get(route('events.public.show', $event))
            ->assertOk()
            ->assertSee('images/events/portada-prueba.jpg', false);
    }

    public function test_unknown_event_returns_not_found(): void
    {
        $this->get('/eventos/999999')->assertNotFound();
    }
}
