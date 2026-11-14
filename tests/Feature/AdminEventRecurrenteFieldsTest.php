<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminEventRecurrenteFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'codigo_evento' => 'recurrente-'.Str::lower(Str::random(8)),
            'nombre' => 'Evento con pago en línea',
            'fecha' => '2032-09-10',
            'hora' => '19:00',
            'lugar' => 'Salón Principal',
            'precio' => '150.00',
            'recurrente_product_id' => 'prod_'.Str::lower(Str::random(10)),
            'recurrente_product_url' => 'https://app.recurrente.com/p/prod-demo',
        ], $overrides);
    }

    public function test_admin_can_create_an_event_with_recurrente_fields(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = $this->eventPayload([
            'recurrente_product_id' => 'prod_creado',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), $payload)
            ->assertRedirect();

        $event = Event::where('codigo_evento', $payload['codigo_evento'])->firstOrFail();

        $this->assertSame('prod_creado', $event->recurrente_product_id);
        $this->assertSame('https://app.recurrente.com/p/prod-demo', $event->recurrente_product_url);
    }

    public function test_admin_can_update_recurrente_fields(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = Event::create([
            'codigo_evento' => 'recurrente-edit',
            'nombre' => 'Evento original',
            'fecha' => '2032-10-10',
            'hora' => '18:00:00',
            'lugar' => 'Salón',
            'precio' => 100,
            'recurrente_product_id' => 'prod_original',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), $this->eventPayload([
                'codigo_evento' => $event->codigo_evento,
                'nombre' => $event->nombre,
                'recurrente_product_id' => 'prod_actualizado',
                'recurrente_product_url' => 'https://app.recurrente.com/p/actualizado',
            ]))
            ->assertRedirect(route('admin.events.show', $event));

        $event->refresh();

        $this->assertSame('prod_actualizado', $event->recurrente_product_id);
        $this->assertSame('https://app.recurrente.com/p/actualizado', $event->recurrente_product_url);
    }

    public function test_recurrente_product_id_must_be_unique(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Event::create([
            'codigo_evento' => 'recurrente-existente',
            'nombre' => 'Evento existente',
            'fecha' => '2032-11-10',
            'hora' => '18:00:00',
            'lugar' => 'Salón',
            'precio' => 100,
            'recurrente_product_id' => 'prod_duplicado',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.events.create'))
            ->post(route('admin.events.store'), $this->eventPayload([
                'recurrente_product_id' => 'prod_duplicado',
            ]))
            ->assertRedirect(route('admin.events.create'))
            ->assertSessionHasErrors('recurrente_product_id');
    }

    public function test_recurrente_product_url_must_be_a_valid_url(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.events.create'))
            ->post(route('admin.events.store'), $this->eventPayload([
                'recurrente_product_url' => 'no-es-una-url',
            ]))
            ->assertRedirect(route('admin.events.create'))
            ->assertSessionHasErrors('recurrente_product_url');
    }
}
