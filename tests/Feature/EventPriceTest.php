<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventLocation;
use App\Models\Participant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventPriceTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'codigo_evento' => 'evento-precio-001',
            'nombre' => 'Evento con Precio',
            'fecha' => '2031-01-15',
            'hora' => '20:00:00',
            'lugar' => 'Salón de Eventos',
            'direccion' => 'Avenida Principal 100',
        ], $overrides));
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'codigo_evento' => 'evento-precio-admin',
            'nombre' => 'Evento Administrado',
            'fecha' => '2031-02-20',
            'hora' => '19:30',
            'lugar' => 'Centro de Convenciones',
            'direccion' => 'Calle Principal 200',
            'precio' => '150.50',
        ], $overrides);
    }

    public function test_admin_can_create_an_event_with_a_price(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), $this->eventPayload())
            ->assertRedirect();

        $event = Event::where('codigo_evento', 'evento-precio-admin')->firstOrFail();

        $this->assertSame('150.50', $event->precio);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'precio' => 150.50,
        ]);
    }

    public function test_admin_can_update_an_event_price(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent(['precio' => 100]);

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), $this->eventPayload([
                'codigo_evento' => $event->codigo_evento,
                'precio' => '275.25',
            ]))
            ->assertRedirect(route('admin.events.show', $event));

        $this->assertSame('275.25', $event->fresh()->precio);
    }

    public function test_admin_cannot_create_an_event_with_a_negative_price(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.events.create'))
            ->post(route('admin.events.store'), $this->eventPayload(['precio' => '-10']))
            ->assertRedirect(route('admin.events.create'))
            ->assertSessionHasErrors('precio');

        $this->assertDatabaseMissing('events', [
            'codigo_evento' => 'evento-precio-admin',
        ]);
    }

    public function test_purchase_page_shows_unit_price_and_total_for_selected_quantity(): void
    {
        $event = $this->createEvent(['precio' => 125.50]);

        $response = $this->withSession([
            '_old_input' => ['cantidad' => 3],
        ])->get(route('events.public.purchase.create', $event));

        $response->assertOk();
        $response->assertSeeText('Precio por ticket');
        $response->assertSee('Q 125.50');
        $response->assertSee('Q 376.50');
        $response->assertSee('data-unit-price="125.50"', false);
        $response->assertSee('id="purchase-total"', false);
    }

    public function test_purchase_page_shows_zero_total_when_event_has_no_price(): void
    {
        $event = $this->createEvent();

        $this->get(route('events.public.purchase.create', $event))
            ->assertOk()
            ->assertSee('Q 0.00');
    }

    public function test_admin_can_create_an_event_with_locations(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), $this->eventPayload([
                'codigo_evento' => 'evento-con-locaciones',
                'locaciones' => [
                    ['nombre' => 'General', 'precio' => '100.00', 'total_asientos' => 100],
                    ['nombre' => 'VIP', 'precio' => '250.50', 'total_asientos' => 20],
                ],
            ]))
            ->assertRedirect();

        $event = Event::where('codigo_evento', 'evento-con-locaciones')->firstOrFail();

        $this->assertDatabaseHas('event_locations', [
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 100,
        ]);
        $this->assertDatabaseHas('event_locations', [
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 250.50,
            'total_asientos' => 20,
        ]);
    }

    public function test_admin_can_open_create_and_edit_forms_with_locations(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent(['precio' => 100]);

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 250,
            'total_asientos' => 20,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.create'))
            ->assertOk()
            ->assertSee('Locaciones y precios')
            ->assertSee('Agregar locación');

        $this->actingAs($admin)
            ->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSee('Locaciones y precios')
            ->assertSee('VIP')
            ->assertSee('value="250.00"', false);
    }

    public function test_admin_cannot_create_an_event_with_duplicate_location_names(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.events.create'))
            ->post(route('admin.events.store'), $this->eventPayload([
                'codigo_evento' => 'evento-locaciones-duplicadas',
                'locaciones' => [
                    ['nombre' => 'General', 'precio' => '100', 'total_asientos' => 100],
                    ['nombre' => 'general', 'precio' => '200', 'total_asientos' => 50],
                ],
            ]))
            ->assertRedirect(route('admin.events.create'))
            ->assertSessionHasErrors('locaciones.1.nombre');

        $this->assertDatabaseMissing('events', [
            'codigo_evento' => 'evento-locaciones-duplicadas',
        ]);
    }

    public function test_create_form_displays_location_validation_errors(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.events.create'))
            ->followingRedirects()
            ->post(route('admin.events.store'), $this->eventPayload([
                'codigo_evento' => 'evento-locaciones-duplicadas',
                'locaciones' => [
                    ['nombre' => 'General', 'precio' => '100', 'total_asientos' => 100],
                    ['nombre' => 'general', 'precio' => '200', 'total_asientos' => 50],
                ],
            ]))
            ->assertOk()
            ->assertSee('No puedes repetir el nombre de una locación.');
    }

    public function test_admin_cannot_reduce_capacity_or_delete_a_location_with_sold_tickets(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent(['precio' => 100]);
        $location = EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 250,
            'total_asientos' => 20,
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
        ]);

        foreach ([1, 2] as $seat) {
            Ticket::create([
                'participant_id' => $participant->id,
                'event_id' => $event->id,
                'codigo_qr' => (string) Str::uuid(),
                'locacion' => 'VIP',
                'asiento' => $seat,
                'archivo_pdf' => '',
            ]);
        }

        $this->actingAs($admin)
            ->from(route('admin.events.edit', $event))
            ->put(route('admin.events.update', $event), $this->eventPayload([
                'codigo_evento' => $event->codigo_evento,
                'locaciones' => [
                    ['id' => $location->id, 'nombre' => 'VIP', 'precio' => '300', 'total_asientos' => 1],
                ],
            ]))
            ->assertRedirect(route('admin.events.edit', $event))
            ->assertSessionHasErrors('locaciones.0.total_asientos');

        $this->assertSame(20, $location->fresh()->total_asientos);

        $this->actingAs($admin)
            ->from(route('admin.events.edit', $event))
            ->put(route('admin.events.update', $event), $this->eventPayload([
                'codigo_evento' => $event->codigo_evento,
                'locaciones' => [],
            ]))
            ->assertRedirect(route('admin.events.edit', $event))
            ->assertSessionHasErrors('locaciones');

        $this->assertDatabaseHas('event_locations', ['id' => $location->id]);
    }

    public function test_admin_can_update_location_price_and_capacity(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent(['precio' => 100]);
        $location = EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 50,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), $this->eventPayload([
                'codigo_evento' => $event->codigo_evento,
                'locaciones' => [
                    ['id' => $location->id, 'nombre' => 'General', 'precio' => '175.50', 'total_asientos' => 80],
                    ['nombre' => 'VIP', 'precio' => '300', 'total_asientos' => 10],
                ],
            ]))
            ->assertRedirect(route('admin.events.show', $event));

        $this->assertDatabaseHas('event_locations', [
            'id' => $location->id,
            'precio' => 175.50,
            'total_asientos' => 80,
        ]);
        $this->assertDatabaseHas('event_locations', [
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 300,
            'total_asientos' => 10,
        ]);
    }

    public function test_purchase_page_shows_location_prices(): void
    {
        $event = $this->createEvent(['precio' => 50]);

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 100,
        ]);
        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 250.50,
            'total_asientos' => 20,
        ]);

        $this->get(route('events.public.purchase.create', $event))
            ->assertOk()
            ->assertSee('VIP')
            ->assertSee('data-precio="250.50"', false)
            ->assertSee('data-precio="100.00"', false);
    }

    public function test_seat_map_page_shows_location_summary_and_occupancy_chart(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent(['precio' => 50]);

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 100,
        ]);
        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 250,
            'total_asientos' => 20,
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana-seat-map@example.com',
        ]);

        foreach ([
            ['General', 1, true],
            ['General', 2, true],
            ['General', 3, false],
            ['VIP', 1, true],
        ] as [$location, $seat, $validated]) {
            Ticket::create([
                'participant_id' => $participant->id,
                'event_id' => $event->id,
                'codigo_qr' => (string) Str::uuid(),
                'locacion' => $location,
                'asiento' => $seat,
                'archivo_pdf' => '',
                'validado' => $validated,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.events.seat.map', $event))
            ->assertOk()
            ->assertSeeText('Resumen de Locaciones')
            ->assertSeeText('Total de tickets')
            ->assertSeeText('Tickets vendidos')
            ->assertSeeText('Porcentaje ocupado')
            ->assertSeeText('Porcentaje validados')
            ->assertSeeTextInOrder([
                'General',
                'Total de tickets',
                '100',
                'Tickets vendidos',
                '3',
                'Porcentaje ocupado',
                '3.00%',
                'Porcentaje validados',
                '66.67%',
                'VIP',
                'Total de tickets',
                '20',
                'Tickets vendidos',
                '1',
                'Porcentaje ocupado',
                '5.00%',
                'Porcentaje validados',
                '100.00%',
            ])
            ->assertSee('style="width: 3%"', false)
            ->assertSee('style="width: 5%"', false)
            ->assertSee('style="width: 66.67%"', false)
            ->assertSee('style="width: 100%"', false)
            ->assertDontSeeText('Distribución de Asientos')
            ->assertDontSeeText('ESCENARIO');
    }

    public function test_seat_map_page_shows_zero_validated_percentage_when_location_has_no_sales(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent(['precio' => 50]);

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 50,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.seat.map', $event))
            ->assertOk()
            ->assertSeeText('Tickets vendidos')
            ->assertSeeText('Porcentaje ocupado')
            ->assertSeeText('Porcentaje validados')
            ->assertSeeTextInOrder([
                'Tickets vendidos',
                '0',
                'Porcentaje ocupado',
                '0.00%',
                'Porcentaje validados',
                '0.00%',
            ])
            ->assertSee('style="width: 0%"', false);
    }

    public function test_admin_dashboard_shows_total_sales_by_location(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent(['precio' => 50]);

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 100,
        ]);
        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 250.50,
            'total_asientos' => 20,
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'nombre' => 'Carlos',
            'apellido' => 'Méndez',
            'email' => 'carlos-dashboard@example.com',
        ]);

        foreach ([
            ['General', 1],
            ['General', 2],
            ['General', 3],
            ['VIP', 1],
        ] as [$location, $seat]) {
            Ticket::create([
                'participant_id' => $participant->id,
                'event_id' => $event->id,
                'codigo_qr' => (string) Str::uuid(),
                'locacion' => $location,
                'asiento' => $seat,
                'archivo_pdf' => '',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total de Venta</th>', false)
            ->assertDontSee('PDFs Generados</th>', false)
            ->assertDontSeeText('PDFs Generados')
            ->assertSeeText('Total de Ventas')
            ->assertSeeTextInOrder([
                'Total de Ventas',
                'Q 550.50',
            ])
            ->assertDontSee('Emails Enviados</th>', false)
            ->assertDontSee('Validaciónes</th>', false)
            ->assertSeeText('Q 550.50')
            ->assertSeeTextInOrder([
                'Evento con Precio',
                '4 TOTAL',
                '3 General',
                '1 VIP',
                'Q 550.50 TOTAL',
                'Q 300.00 General',
                'Q 250.50 VIP',
                'Totales',
                'Q 550.50',
            ]);
    }
}
