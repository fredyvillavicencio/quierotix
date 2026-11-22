<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventLocation;
use App\Models\Participant;
use App\Models\Ticket;
use App\Services\EmailService;
use App\Services\PdfGenerationService;
use App\Services\SeatAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class PublicTicketPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'codigo_evento' => 'evento-'.Str::lower(Str::random(10)),
            'nombre' => 'Evento de Compra Pública',
            'fecha' => '2030-06-15',
            'hora' => '19:00:00',
            'lugar' => 'Salón Principal',
            'direccion' => 'Avenida Siempre Viva 742',
        ], $overrides));
    }

    private function createParticipant(Event $event, array $overrides = []): Participant
    {
        return Participant::create(array_merge([
            'event_id' => $event->id,
            'nombre' => 'María',
            'apellido' => 'López',
            'email' => 'maria.'.Str::lower(Str::random(6)).'@example.com',
        ], $overrides));
    }

    private function seedTickets(Event $event, Participant $participant, array $seats): void
    {
        $rows = [];

        foreach ($seats as [$locacion, $asiento]) {
            $rows[] = [
                'participant_id' => $participant->id,
                'event_id' => $event->id,
                'codigo_qr' => (string) Str::uuid(),
                'locacion' => $locacion,
                'asiento' => $asiento,
                'archivo_pdf' => '',
                'validado' => false,
                'contador_validaciones' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('tickets')->insert($chunk);
        }

        foreach ($participant->tickets()->get() as $ticket) {
            DB::table('tickets')
                ->where('id', $ticket->id)
                ->update(['codigo' => $ticket->buildTicketCode()]);
        }
    }

    private function fillEvent(Event $event): Participant
    {
        $participant = $this->createParticipant($event, ['email' => 'ocupado@example.com']);
        $seats = [];

        for ($asiento = 1; $asiento <= SeatAssignmentService::DEFAULT_LOCATION_CAPACITY; $asiento++) {
            $seats[] = [SeatAssignmentService::DEFAULT_LOCATION_NAME, $asiento];
        }

        $this->seedTickets($event, $participant, $seats);

        return $participant;
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

    public function test_event_detail_shows_purchase_cta(): void
    {
        $event = $this->createEvent();

        $this->get(route('events.public.show', $event))
            ->assertOk()
            ->assertSee('Comprar Ticket')
            ->assertSee(route('events.public.purchase.create', $event), false);
    }

    public function test_purchase_form_shows_participant_fields_and_quantity_options(): void
    {
        $event = $this->createEvent();

        $this->get(route('events.public.purchase.create', $event))
            ->assertOk()
            ->assertSee('name="nombre"', false)
            ->assertSee('name="apellido"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="carne"', false)
            ->assertSee('for="carne"', false)
            ->assertSee('name="telefono"', false)
            ->assertSee('for="telefono"', false)
            ->assertSee('Teléfono', false)
            ->assertSee('name="cantidad"', false)
            ->assertSee('value="7"', false)
            ->assertSee('id="purchase-form"', false)
            ->assertSee('id="purchase-submit"', false)
            ->assertSee('Procesando...', false);
    }

    public function test_purchase_page_shows_cover_image_only_on_mobile(): void
    {
        $event = $this->createEvent([
            'imagen' => 'images/events/main.jpg',
            'imagen_cover' => 'images/events/cover.jpg',
        ]);

        $this->get(route('events.public.purchase.create', $event))
            ->assertOk()
            ->assertSeeInOrder([
                'images/events/main.jpg',
                'd-none d-md-block',
                'images/events/cover.jpg',
                'd-md-none',
            ], false);
    }

    public function test_public_purchase_creates_tickets_pdfs_and_redirects_to_success(): void
    {
        $event = $this->createEvent();
        $this->mockPdfGeneration();
        $this->mockEmailService();

        $response = $this->post(route('events.public.purchase.store', $event), [
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'telefono' => '5555-1234',
            'carne' => '0909-20-12345',
            'cantidad' => 3,
        ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringContainsString('/compra/', $location);

        $participant = Participant::where('event_id', $event->id)
            ->where('email', 'ana@example.com')
            ->firstOrFail();

        $this->assertSame('Ana', $participant->nombre);
        $this->assertSame('5555-1234', $participant->telefono);
        $this->assertSame('0909-20-12345', $participant->carne);
        $this->assertTrue($participant->email_sent);
        $this->assertCount(3, $participant->pdf_paths);
        $this->assertCount(3, $participant->tickets);
        $this->assertSame(
            ['General-1', 'General-2', 'General-3'],
            $participant->tickets->sortBy('id')->map(fn (Ticket $ticket) => $ticket->seat_code)->values()->all()
        );

        $this->get($location)
            ->assertOk()
            ->assertSeeText('¡Tus tickets están listos!')
            ->assertSeeText('ana@example.com')
            ->assertSeeText('Descargar todos en ZIP');
    }

    public function test_public_purchase_assigns_selected_location_and_enforces_its_capacity(): void
    {
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

        $this->post(route('events.public.purchase.store', $event), [
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'telefono' => '5555-1234',
            'carne' => '0909-20-12345',
            'locacion' => 'VIP',
            'cantidad' => 2,
        ])->assertRedirect();

        $participant = Participant::where('event_id', $event->id)
            ->where('email', 'ana@example.com')
            ->firstOrFail();

        $this->assertSame(
            ['VIP-1', 'VIP-2'],
            $participant->tickets->sortBy('id')->map(fn (Ticket $ticket) => $ticket->seat_code)->values()->all()
        );

        $this->post(route('events.public.purchase.store', $event), [
            'nombre' => 'Luis',
            'apellido' => 'Ramírez',
            'email' => 'luis@example.com',
            'telefono' => '5555-5678',
            'carne' => '0909-20-67890',
            'locacion' => 'VIP',
            'cantidad' => 1,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('participants', [
            'event_id' => $event->id,
            'email' => 'luis@example.com',
        ]);
    }

    public function test_purchase_validates_required_fields_and_quantity_limit(): void
    {
        $event = $this->createEvent();

        $response = $this->from(route('events.public.purchase.create', $event))
            ->post(route('events.public.purchase.store', $event), []);

        $response->assertRedirect(route('events.public.purchase.create', $event));
        $response->assertSessionHasErrors(['nombre', 'apellido', 'email', 'telefono', 'carne', 'cantidad']);
        $this->assertDatabaseCount('participants', 0);

        $response = $this->from(route('events.public.purchase.create', $event))
            ->post(route('events.public.purchase.store', $event), [
                'nombre' => 'Ana',
                'apellido' => 'Pérez',
                'email' => 'ana@example.com',
                'telefono' => '5555-1234',
                'carne' => '0909-20-12345',
                'cantidad' => SeatAssignmentService::MAX_TICKETS_PER_PURCHASE + 1,
            ]);

        $response->assertRedirect(route('events.public.purchase.create', $event));
        $response->assertSessionHasErrors('cantidad');
    }

    public function test_purchase_allows_duplicate_email_for_same_event(): void
    {
        $event = $this->createEvent();
        $this->createParticipant($event, ['email' => 'ana@example.com']);
        $this->mockPdfGeneration();
        $this->mockEmailService();

        $response = $this->post(route('events.public.purchase.store', $event), [
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'telefono' => '5555-1234',
            'carne' => '0909-20-12345',
            'cantidad' => 1,
        ]);

        $response->assertRedirect();

        $this->assertSame(
            2,
            Participant::where('event_id', $event->id)->where('email', 'ana@example.com')->count()
        );

        $purchasedParticipant = Participant::where('event_id', $event->id)
            ->where('email', 'ana@example.com')
            ->latest('id')
            ->firstOrFail();

        $this->assertCount(1, $purchasedParticipant->tickets);
        $this->assertSame('General-1', $purchasedParticipant->tickets->first()->seat_code);
    }

    public function test_same_email_can_purchase_for_a_different_event(): void
    {
        $firstEvent = $this->createEvent();
        $secondEvent = $this->createEvent();
        $this->createParticipant($firstEvent, ['email' => 'ana@example.com']);

        $this->mockPdfGeneration();
        $this->mockEmailService();

        $this->post(route('events.public.purchase.store', $secondEvent), [
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'telefono' => '5555-1234',
            'carne' => '0909-20-12345',
            'cantidad' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('participants', [
            'event_id' => $secondEvent->id,
            'email' => 'ana@example.com',
        ]);
    }

    public function test_purchase_keeps_tickets_when_email_delivery_fails(): void
    {
        $event = $this->createEvent();
        $this->mockPdfGeneration();
        $this->mockEmailService(false);

        $response = $this->post(route('events.public.purchase.store', $event), [
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'telefono' => '5555-1234',
            'carne' => '0909-20-12345',
            'cantidad' => 2,
        ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');

        $participant = Participant::where('event_id', $event->id)
            ->where('email', 'ana@example.com')
            ->firstOrFail();

        $this->assertFalse($participant->email_sent);
        $this->assertCount(2, $participant->tickets);
        $this->assertNotNull($participant->pdf_paths);

        $this->get($location)
            ->assertOk()
            ->assertSeeText('no pudimos enviarlos por correo');
    }

    public function test_purchase_is_rejected_when_event_is_sold_out(): void
    {
        $event = $this->createEvent();
        $this->fillEvent($event);

        $this->get(route('events.public.purchase.create', $event))
            ->assertOk()
            ->assertSeeText('Este evento está agotado');

        $response = $this->from(route('events.public.purchase.create', $event))
            ->post(route('events.public.purchase.store', $event), [
                'nombre' => 'Ana',
                'apellido' => 'Pérez',
                'email' => 'nueva@example.com',
                'telefono' => '5555-1234',
                'carne' => '0909-20-12345',
                'cantidad' => 1,
            ]);

        $response->assertRedirect(route('events.public.purchase.create', $event));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('participants', ['email' => 'nueva@example.com']);
    }

    public function test_success_page_requires_a_valid_signature(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);

        $this->get(route('events.public.purchase.success', [
            'event' => $event,
            'participant' => $participant,
        ]))->assertForbidden();

        $signedUrl = URL::temporarySignedRoute(
            'events.public.purchase.success',
            now()->addHour(),
            ['event' => $event->id, 'participant' => $participant->id]
        );

        $this->get($signedUrl)
            ->assertOk()
            ->assertSeeText('Tus entradas')
            ->assertSeeText('No se encontraron tickets generados');
    }

    public function test_success_page_hides_zip_button_for_a_single_ticket(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $this->seedTickets($event, $participant, [['Dancefloor', 1]]);

        $signedUrl = URL::temporarySignedRoute(
            'events.public.purchase.success',
            now()->addHour(),
            ['event' => $event->id, 'participant' => $participant->id]
        );

        $this->get($signedUrl)
            ->assertOk()
            ->assertDontSeeText('Descargar todos en ZIP');
    }

    public function test_success_page_hides_zip_button_for_exactly_two_tickets(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $this->seedTickets($event, $participant, [['Dancefloor', 1], ['Dancefloor', 2]]);

        $signedUrl = URL::temporarySignedRoute(
            'events.public.purchase.success',
            now()->addHour(),
            ['event' => $event->id, 'participant' => $participant->id]
        );

        $this->get($signedUrl)
            ->assertOk()
            ->assertDontSeeText('Descargar todos en ZIP');
    }

    public function test_success_page_shows_zip_button_for_more_than_two_tickets(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $this->seedTickets($event, $participant, [['Dancefloor', 1], ['Dancefloor', 2], ['Dancefloor', 3]]);

        $signedUrl = URL::temporarySignedRoute(
            'events.public.purchase.success',
            now()->addHour(),
            ['event' => $event->id, 'participant' => $participant->id]
        );

        $this->get($signedUrl)
            ->assertOk()
            ->assertSeeText('Descargar todos en ZIP');
    }

    public function test_success_page_shows_ticket_download_label_with_location_initial_and_ids(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $this->seedTickets($event, $participant, [['Dancefloor', 1]]);

        $ticket = $participant->tickets()->firstOrFail();
        $expectedLabel = "Dancefloor - Ticket D{$participant->id}{$ticket->id}";

        $signedUrl = URL::temporarySignedRoute(
            'events.public.purchase.success',
            now()->addHour(),
            ['event' => $event->id, 'participant' => $participant->id]
        );

        $this->get($signedUrl)
            ->assertOk()
            ->assertSeeText($expectedLabel)
            ->assertDontSeeText('Dancefloor, Asiento 1');
    }

    public function test_ticket_pdf_uses_download_label_instead_of_location_and_seat(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $this->seedTickets($event, $participant, [['Dancefloor', 1]]);

        $ticket = $participant->tickets()->firstOrFail();
        $expectedLabel = "Dancefloor - Ticket D{$participant->id}{$ticket->id}";

        $html = view('pdfs.ticket', [
            'event' => $event,
            'participant' => $participant,
            'ticket' => $ticket,
            'qrCode' => 'data:image/png;base64,test',
        ])->render();

        $this->assertStringContainsString($expectedLabel, $html);
        $this->assertStringNotContainsString('Asiento', $html);
    }

    public function test_ticket_pdf_template_shows_event_name_date_and_time(): void
    {
        $event = $this->createEvent([
            'nombre' => 'Noche de Gala',
            'fecha' => '2030-06-15',
            'hora' => '19:30:00',
        ]);
        $participant = $this->createParticipant($event);
        $this->seedTickets($event, $participant, [['General', 1]]);

        $html = view('pdfs.ticket', [
            'event' => $event,
            'participant' => $participant,
            'ticket' => $participant->tickets()->firstOrFail(),
            'qrCode' => 'data:image/png;base64,test',
        ])->render();

        $this->assertStringContainsString('Noche de Gala', $html);
        $this->assertStringContainsString('15/06/2030', $html);
        $this->assertStringContainsString('07:30 PM', $html);
        $this->assertStringNotContainsString('Nombre del Evento', $html);
        $this->assertStringNotContainsString('Fecha y Hora del evento', $html);
    }

    public function test_seat_assignment_respects_requested_quantity(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $service = app(SeatAssignmentService::class);

        $tickets = $service->assignSeatsToParticipant($participant, 2);

        $this->assertCount(2, $tickets);
        $this->assertSame(
            ['General-1', 'General-2'],
            collect($tickets)->map(fn (Ticket $ticket) => $ticket->seat_code)->all()
        );
        $this->assertSame(
            SeatAssignmentService::DEFAULT_LOCATION_CAPACITY - 2,
            $service->getAvailableSeatCount($event)
        );
    }

    public function test_seat_assignment_respects_location_capacity_and_selected_location(): void
    {
        $event = $this->createEvent();

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 250,
            'total_asientos' => 3,
        ]);

        $buyer = $this->createParticipant($event);
        $service = app(SeatAssignmentService::class);

        $tickets = $service->assignSeatsToParticipant($buyer, 2, 'VIP');

        $this->assertCount(2, $tickets);
        $this->assertSame(
            ['VIP-1', 'VIP-2'],
            collect($tickets)->map(fn (Ticket $ticket) => $ticket->seat_code)->all()
        );
        $this->assertSame(1, $service->getAvailableSeatCount($event, 'VIP'));
        $this->assertTrue($service->canAssignSeats($event, 1, 'VIP'));
        $this->assertFalse($service->canAssignSeats($event, 2, 'VIP'));
    }

    public function test_pdf_generation_creates_files_when_ticket_background_is_missing(): void
    {
        Storage::fake('local');

        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        app(SeatAssignmentService::class)->assignSeatsToParticipant($participant, 2);
        $participant->load('tickets');

        $paths = app(PdfGenerationService::class)->generateParticipantPdfs($participant);

        $this->assertCount(2, $paths);

        foreach ($paths as $path) {
            Storage::disk('local')->assertExists($path);
            $this->assertGreaterThan(0, Storage::disk('local')->size($path));
        }
    }

    public function test_email_service_falls_back_to_laravel_mail_without_brevo(): void
    {
        Storage::fake('local');
        config([
            'services.brevo.key' => null,
            'mail.default' => 'array',
        ]);

        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        app(SeatAssignmentService::class)->assignSeatsToParticipant($participant, 1);
        $participant->load('tickets');

        $path = "pdfs/event_{$event->id}/participant_{$participant->id}/ticket_prueba.pdf";
        Storage::disk('local')->put($path, 'contenido de prueba');
        $participant->update(['pdf_paths' => [$path]]);

        $result = app(EmailService::class)->sendParticipantEmail($participant);

        $this->assertTrue($result);
        $this->assertTrue($participant->fresh()->email_sent);
        $this->assertNotNull($participant->fresh()->email_sent_at);
    }
}
