<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventAddressTest extends TestCase
{
    use RefreshDatabase;

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'codigo_evento' => 'evento-sin-direccion',
            'nombre' => 'Evento sin dirección',
            'fecha' => '2032-03-15',
            'hora' => '18:00',
            'lugar' => 'Salón Principal',
            'precio' => '100.00',
        ], $overrides);
    }

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'codigo_evento' => 'evento-con-direccion',
            'nombre' => 'Evento con dirección',
            'fecha' => '2032-04-20',
            'hora' => '19:00:00',
            'lugar' => 'Centro de Convenciones',
            'direccion' => 'Avenida Principal 100',
            'precio' => 100,
        ], $overrides));
    }

    public function test_admin_can_create_an_event_without_an_address(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), $this->eventPayload())
            ->assertRedirect();

        $event = Event::where('codigo_evento', 'evento-sin-direccion')->firstOrFail();

        $this->assertNull($event->direccion);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'direccion' => null,
        ]);
    }

    public function test_admin_can_clear_an_existing_address_when_updating_an_event(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent();

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), $this->eventPayload([
                'codigo_evento' => $event->codigo_evento,
                'nombre' => $event->nombre,
                'fecha' => '2032-04-20',
                'hora' => '19:00',
                'lugar' => $event->lugar,
                'direccion' => '',
                'precio' => '100.00',
            ]))
            ->assertRedirect(route('admin.events.show', $event));

        $this->assertNull($event->fresh()->direccion);
    }
}
