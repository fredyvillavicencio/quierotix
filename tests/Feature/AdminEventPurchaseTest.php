<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventLocation;
use App\Models\Participant;
use App\Models\Ticket;
use App\Models\User;
use App\Services\EmailService;
use App\Services\PdfGenerationService;
use App\Services\SeatAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class AdminEventPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'codigo_evento' => 'admin-'.Str::lower(Str::random(10)),
            'nombre' => 'Evento de Compra Administrativa',
            'fecha' => '2032-06-15',
            'hora' => '19:00:00',
            'lugar' => 'Salón Principal',
            'direccion' => 'Avenida Siempre Viva 742',
            'precio' => 100,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'telefono' => '5555-1234',
            'carne' => '0909-20-12345',
            'locacion' => SeatAssignmentService::DEFAULT_LOCATION_NAME,
            'cantidad' => 1,
        ], $overrides);
    }

    private function mockPdfGeneration(): void
    {
        $this->mock(PdfGenerationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('generateParticipantPdfs')
                ->once()
                ->andReturnUsing(function (Participant $participant) {
                    $paths = $participant->tickets->map(function (Ticket $ticket) use ($participant) {
                        $path = "pdfs/event_{$participant->event_id}/participant_{$participant->id}/ticket_{$ticket->id}.pdf";
                        $ticket->update(['archivo_pdf' => $path]);

                        return $path;
                    })->all();

                    $participant->update(['pdf_paths' => $paths]);

                    return $paths;
                });
        });
    }

    private function mockEmailService(bool $sent = true): void
    {
        $this->mock(EmailService::class, function (MockInterface $mock) use ($sent) {
            $mock->shouldReceive('sendParticipantEmail')
                ->once()
                ->andReturnUsing(function (Participant $participant) use ($sent) {
                    if ($sent) {
                        $participant->update(['email_sent' => true]);
                    }

                    return $sent;
                });
        });
    }

    public function test_event_show_replaces_pdf_button_with_create_purchase(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();

        $this->actingAs($admin)
            ->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertSee('Crear Compra')
            ->assertSee(route('admin.events.purchase.create', $event), false)
            ->assertDontSee('Generar PDFs');
    }

    public function test_admin_can_view_purchase_form_with_backup_fields(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();

        $this->actingAs($admin)
            ->get(route('admin.events.purchase.create', $event))
            ->assertOk()
            ->assertSee('name="nombre"', false)
            ->assertSee('name="apellido"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="telefono"', false)
            ->assertSee('name="carne"', false)
            ->assertSee('name="locacion"', false)
            ->assertSee('name="cantidad"', false)
            ->assertSee('value="10"', false)
            ->assertSee('Confirmar compra');
    }

    public function test_admin_purchase_creates_tickets_pdfs_and_sends_email(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();
        $this->mockPdfGeneration();
        $this->mockEmailService();

        $response = $this->actingAs($admin)->post(
            route('admin.events.purchase.store', $event),
            $this->payload(['cantidad' => 3])
        );

        $participant = Participant::where('event_id', $event->id)
            ->where('email', 'ana@example.com')
            ->firstOrFail();

        $response
            ->assertRedirect(route('admin.events.purchase.success', [$event, $participant]))
            ->assertSessionHas('success');

        $this->assertSame('Ana', $participant->nombre);
        $this->assertSame('5555-1234', $participant->telefono);
        $this->assertSame('0909-20-12345', $participant->carne);
        $this->assertTrue($participant->email_sent);
        $this->assertCount(3, $participant->pdf_paths);
        $this->assertCount(3, $participant->tickets);
        $this->assertSame(
            [
                SeatAssignmentService::DEFAULT_LOCATION_NAME.'-1',
                SeatAssignmentService::DEFAULT_LOCATION_NAME.'-2',
                SeatAssignmentService::DEFAULT_LOCATION_NAME.'-3',
            ],
            $participant->tickets->sortBy('id')->map(fn (Ticket $ticket) => $ticket->seat_code)->values()->all()
        );

        $firstTicket = $participant->tickets->sortBy('id')->first();

        $this->actingAs($admin)
            ->get(route('admin.events.purchase.success', [$event, $participant]))
            ->assertOk()
            ->assertSee('¡Los tickets están listos!')
            ->assertSee('Enviamos una copia de los tickets a ana@example.com.')
            ->assertSee('Descargar todos en ZIP')
            ->assertSee(route('tickets.download.pdf', $firstTicket->codigo), false)
            ->assertSee(route('admin.participants.download.pdfs', $participant), false)
            ->assertSee($firstTicket->download_label);
    }

    public function test_admin_purchase_assigns_the_selected_location(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 5,
        ]);
        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 300,
            'total_asientos' => 2,
        ]);

        $this->mockPdfGeneration();
        $this->mockEmailService();

        $response = $this->actingAs($admin)->post(
            route('admin.events.purchase.store', $event),
            $this->payload([
                'locacion' => 'VIP',
                'cantidad' => 2,
            ])
        );

        $participant = Participant::where('event_id', $event->id)
            ->where('email', 'ana@example.com')
            ->firstOrFail();

        $response->assertRedirect(route('admin.events.purchase.success', [$event, $participant]));

        $this->assertSame(
            ['VIP-1', 'VIP-2'],
            $participant->tickets->sortBy('id')->map(fn (Ticket $ticket) => $ticket->seat_code)->values()->all()
        );
    }

    public function test_admin_purchase_validates_required_fields_and_quantity_limit(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();

        $response = $this->actingAs($admin)
            ->from(route('admin.events.purchase.create', $event))
            ->post(route('admin.events.purchase.store', $event), []);

        $response->assertRedirect(route('admin.events.purchase.create', $event));
        $response->assertSessionHasErrors([
            'nombre',
            'apellido',
            'email',
            'telefono',
            'carne',
            'locacion',
            'cantidad',
        ]);
        $this->assertDatabaseCount('participants', 0);

        $response = $this->actingAs($admin)
            ->from(route('admin.events.purchase.create', $event))
            ->post(route('admin.events.purchase.store', $event), $this->payload([
                'cantidad' => SeatAssignmentService::MAX_TICKETS_PER_PURCHASE + 1,
            ]));

        $response->assertRedirect(route('admin.events.purchase.create', $event));
        $response->assertSessionHasErrors('cantidad');
        $this->assertDatabaseCount('participants', 0);
    }

    public function test_admin_purchase_allows_duplicate_email_for_same_event(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();

        Participant::create([
            'event_id' => $event->id,
            'nombre' => 'María',
            'apellido' => 'López',
            'email' => 'ana@example.com',
        ]);

        $this->mockPdfGeneration();
        $this->mockEmailService();

        $response = $this->actingAs($admin)->post(
            route('admin.events.purchase.store', $event),
            $this->payload()
        );

        $participant = Participant::where('event_id', $event->id)
            ->where('email', 'ana@example.com')
            ->latest('id')
            ->firstOrFail();

        $response->assertRedirect(route('admin.events.purchase.success', [$event, $participant]));

        $this->assertSame(
            2,
            Participant::where('event_id', $event->id)->where('email', 'ana@example.com')->count()
        );
    }

    public function test_admin_purchase_cleans_up_when_pdf_generation_fails(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();

        $this->mock(PdfGenerationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('generateParticipantPdfs')
                ->once()
                ->andThrow(new \RuntimeException('Fallo al generar el PDF'));
        });

        $this->mock(EmailService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('sendParticipantEmail');
        });

        $response = $this->actingAs($admin)
            ->from(route('admin.events.purchase.create', $event))
            ->post(route('admin.events.purchase.store', $event), $this->payload());

        $response
            ->assertRedirect(route('admin.events.purchase.create', $event))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('participants', 0);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_admin_purchase_keeps_tickets_when_email_fails(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();
        $this->mockPdfGeneration();
        $this->mockEmailService(false);

        $response = $this->actingAs($admin)->post(
            route('admin.events.purchase.store', $event),
            $this->payload()
        );

        $participant = Participant::where('event_id', $event->id)
            ->where('email', 'ana@example.com')
            ->firstOrFail();

        $response
            ->assertRedirect(route('admin.events.purchase.success', [$event, $participant]))
            ->assertSessionHas('success')
            ->assertSessionMissing('warning');

        $this->assertFalse($participant->email_sent);
        $this->assertCount(1, $participant->tickets);

        $this->actingAs($admin)
            ->get(route('admin.events.purchase.success', [$event, $participant]))
            ->assertOk()
            ->assertSee('Los tickets se generaron correctamente, pero no pudimos enviarlos por correo.')
            ->assertSee('Descargar todos en ZIP')
            ->assertSee(route('tickets.download.pdf', $participant->tickets->first()->codigo), false)
            ->assertDontSee('Enviamos una copia de los tickets');
    }

    public function test_admin_purchase_success_page_lists_every_ticket(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();
        $this->mockPdfGeneration();
        $this->mockEmailService();

        $this->actingAs($admin)->post(
            route('admin.events.purchase.store', $event),
            $this->payload(['cantidad' => 2])
        );

        $participant = Participant::where('event_id', $event->id)
            ->where('email', 'ana@example.com')
            ->firstOrFail();

        $tickets = $participant->tickets->sortBy('id')->values();

        $response = $this->actingAs($admin)
            ->get(route('admin.events.purchase.success', [$event, $participant]))
            ->assertOk();

        foreach ($tickets as $ticket) {
            $response
                ->assertSee($ticket->download_label)
                ->assertSee(route('tickets.download.pdf', $ticket->codigo), false);
        }
    }

    public function test_admin_purchase_success_page_returns_404_for_participant_from_another_event(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent();
        $otherEvent = $this->createEvent();

        $participant = Participant::create([
            'event_id' => $otherEvent->id,
            'nombre' => 'María',
            'apellido' => 'López',
            'email' => 'maria@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.purchase.success', [$event, $participant]))
            ->assertNotFound();
    }

    public function test_guest_cannot_view_admin_purchase_form(): void
    {
        $event = $this->createEvent();

        $this->get(route('admin.events.purchase.create', $event))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_view_admin_purchase_success_page(): void
    {
        $event = $this->createEvent();

        $participant = Participant::create([
            'event_id' => $event->id,
            'nombre' => 'María',
            'apellido' => 'López',
            'email' => 'maria@example.com',
        ]);

        $this->get(route('admin.events.purchase.success', [$event, $participant]))
            ->assertRedirect(route('login'));
    }
}
