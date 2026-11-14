<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventLocation;
use App\Models\Participant;
use App\Models\RecurrenteWebhookEvent;
use App\Models\Ticket;
use App\Services\EmailService;
use App\Services\PdfGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class RecurrenteWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'codigo_evento' => 'recurrente-'.Str::lower(Str::random(8)),
            'nombre' => 'Evento Recurrente',
            'fecha' => '2031-08-20',
            'hora' => '18:00:00',
            'lugar' => 'Centro de Convenciones',
            'precio' => 100,
            'recurrente_product_id' => 'prod_'.Str::lower(Str::random(10)),
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Event $event, array $overrides = []): array
    {
        $payload = [
            'type' => 'payment',
            'event_type' => 'intent.succeeded',
            'status' => 'succeeded',
            'legacy_id' => 'legacy_'.Str::lower(Str::random(10)),
            'receipt_number' => 98765,
            'amount_in_cents' => 20000,
            'currency' => 'GTQ',
            'customer' => [
                'email' => 'juan@example.com',
                'full_name' => 'Juan Pérez',
                'phone' => '5555-1234',
                'nit' => '1234567-8',
            ],
            'product' => [
                'id' => $event->recurrente_product_id,
            ],
            'details' => [
                'products' => [
                    [
                        'id' => $event->recurrente_product_id,
                        'quantity' => 2,
                    ],
                ],
            ],
            'checkout' => [
                'id' => 'ch_'.Str::lower(Str::random(10)),
            ],
            'id' => 'evt_'.Str::lower(Str::random(10)),
        ];

        return array_replace_recursive($payload, $overrides);
    }

    private function mockDelivery(): void
    {
        $this->mock(PdfGenerationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generateParticipantPdfs')
                ->once()
                ->andReturnUsing(function (Participant $participant): array {
                    $paths = $participant->tickets->map(function (Ticket $ticket) use ($participant): string {
                        $path = "pdfs/event_{$participant->event_id}/participant_{$participant->id}/ticket_{$ticket->id}.pdf";
                        $ticket->update(['archivo_pdf' => $path]);

                        return $path;
                    })->all();

                    $participant->update(['pdf_paths' => $paths]);

                    return $paths;
                });
        });

        $this->mock(EmailService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendParticipantEmail')
                ->once()
                ->andReturnUsing(function (Participant $participant): bool {
                    $participant->update(['email_sent' => true]);

                    return true;
                });
        });
    }

    public function test_successful_payment_creates_participant_tickets_and_marks_webhook_processed(): void
    {
        $event = $this->createEvent();
        $this->mockDelivery();
        $payload = $this->payload($event, ['legacy_id' => 'legacy_success']);

        $this->postJson(route('recurrente.pagos'), $payload, ['svix-id' => 'msg_success'])
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'tickets' => 2,
                'email_sent' => true,
            ]);

        $participant = Participant::where('email', 'juan@example.com')->firstOrFail();

        $this->assertSame('Juan', $participant->nombre);
        $this->assertSame('Pérez', $participant->apellido);
        $this->assertSame('5555-1234', $participant->telefono);
        $this->assertSame('1234567-8', $participant->carne);
        $this->assertTrue($participant->email_sent);
        $this->assertCount(2, $participant->pdf_paths);
        $this->assertCount(2, $participant->tickets);

        $this->assertSame(
            ['Ticket-1', 'Ticket-2'],
            $participant->tickets->sortBy('id')->map(fn (Ticket $ticket) => $ticket->seat_code)->values()->all()
        );

        $webhook = RecurrenteWebhookEvent::firstOrFail();

        $this->assertSame('legacy_success', $webhook->payment_key);
        $this->assertSame('msg_success', $webhook->svix_id);
        $this->assertSame($event->id, $webhook->event_id);
        $this->assertSame($participant->id, $webhook->participant_id);
        $this->assertNotNull($webhook->processed_at);
        $this->assertNull($webhook->last_error);
        $this->assertSame('intent.succeeded', $webhook->event_type);
        $this->assertSame(20000, $webhook->amount_in_cents);
    }

    public function test_duplicate_webhook_does_not_create_duplicate_tickets_or_resend_email(): void
    {
        $event = $this->createEvent();
        $this->mockDelivery();
        $payload = $this->payload($event, ['legacy_id' => 'legacy_duplicate']);

        $this->postJson(route('recurrente.pagos'), $payload, ['svix-id' => 'msg_duplicate_1'])
            ->assertOk();

        $this->postJson(route('recurrente.pagos'), $payload, ['svix-id' => 'msg_duplicate_2'])
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'duplicate' => true,
            ]);

        $this->assertSame(1, Participant::count());
        $this->assertSame(2, Ticket::count());
        $this->assertSame(1, RecurrenteWebhookEvent::count());
        $this->assertSame('msg_duplicate_1', RecurrenteWebhookEvent::firstOrFail()->svix_id);
    }

    public function test_non_successful_payment_is_ignored_without_creating_records(): void
    {
        $event = $this->createEvent();
        $payload = $this->payload($event, [
            'event_type' => 'intent.failed',
            'status' => 'failed',
        ]);

        $this->postJson(route('recurrente.pagos'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'ignored']);

        $this->assertSame(0, Participant::count());
        $this->assertSame(0, RecurrenteWebhookEvent::count());
    }

    public function test_payment_for_unknown_product_returns_server_error_and_is_recorded(): void
    {
        $event = $this->createEvent(['recurrente_product_id' => null]);
        $payload = $this->payload($event, [
            'product' => ['id' => 'prod_missing'],
            'details' => ['products' => [['id' => 'prod_missing', 'quantity' => 2]]],
            'legacy_id' => 'legacy_unknown_product',
        ]);

        $this->postJson(route('recurrente.pagos'), $payload)
            ->assertStatus(500);

        $this->assertSame(0, Participant::count());

        $webhook = RecurrenteWebhookEvent::firstOrFail();

        $this->assertNull($webhook->processed_at);
        $this->assertNull($webhook->participant_id);
        $this->assertStringContainsString('prod_missing', $webhook->last_error);
    }

    public function test_invalid_json_returns_bad_request(): void
    {
        $this->call(
            'POST',
            '/recurrente/pagos',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            '{invalid-json'
        )->assertStatus(400);

        $this->assertSame(0, RecurrenteWebhookEvent::count());
    }


    public function test_payment_selects_the_location_matching_the_unit_price(): void
    {
        $event = $this->createEvent();

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 10,
        ]);
        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'VIP',
            'precio' => 250,
            'total_asientos' => 10,
        ]);

        $this->mockDelivery();
        $payload = $this->payload($event, ['amount_in_cents' => 50000]);

        $this->postJson(route('recurrente.pagos'), $payload)->assertOk();

        $participant = Participant::where('email', 'juan@example.com')->firstOrFail();

        $this->assertSame(
            ['VIP', 'VIP'],
            $participant->tickets->sortBy('id')->pluck('locacion')->values()->all()
        );
    }

    public function test_payment_fails_when_the_selected_location_does_not_have_enough_seats(): void
    {
        $event = $this->createEvent();

        EventLocation::create([
            'event_id' => $event->id,
            'nombre' => 'General',
            'precio' => 100,
            'total_asientos' => 1,
        ]);

        $payload = $this->payload($event);

        $this->postJson(route('recurrente.pagos'), $payload)->assertStatus(500);

        $this->assertSame(0, Participant::count());

        $webhook = RecurrenteWebhookEvent::firstOrFail();

        $this->assertNull($webhook->processed_at);
        $this->assertStringContainsString('asientos', $webhook->last_error);
    }

    public function test_retry_after_pdf_failure_reuses_the_participant_and_completes_delivery(): void
    {
        $event = $this->createEvent();
        $attempts = 0;

        $this->mock(PdfGenerationService::class, function (MockInterface $mock) use (&$attempts): void {
            $mock->shouldReceive('generateParticipantPdfs')
                ->twice()
                ->andReturnUsing(function (Participant $participant) use (&$attempts): array {
                    $attempts++;

                    if ($attempts === 1) {
                        throw new \RuntimeException('Fallo temporal de PDF');
                    }

                    $paths = $participant->tickets->map(function (Ticket $ticket) use ($participant): string {
                        $path = "pdfs/event_{$participant->event_id}/participant_{$participant->id}/ticket_{$ticket->id}.pdf";
                        $ticket->update(['archivo_pdf' => $path]);

                        return $path;
                    })->all();

                    $participant->update(['pdf_paths' => $paths]);

                    return $paths;
                });
        });

        $this->mock(EmailService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendParticipantEmail')
                ->once()
                ->andReturnUsing(function (Participant $participant): bool {
                    $participant->update(['email_sent' => true]);

                    return true;
                });
        });

        $payload = $this->payload($event, ['legacy_id' => 'legacy_retry']);

        $this->postJson(route('recurrente.pagos'), $payload, ['svix-id' => 'msg_retry_original'])
            ->assertStatus(500);

        $this->assertSame(1, Participant::count());
        $this->assertSame(2, Ticket::count());
        $this->assertNull(RecurrenteWebhookEvent::firstOrFail()->processed_at);
        $this->assertSame('msg_retry_original', RecurrenteWebhookEvent::firstOrFail()->svix_id);

        $this->postJson(route('recurrente.pagos'), $payload, ['svix-id' => 'msg_retry_reintento'])
            ->assertOk()
            ->assertJson(['status' => 'ok', 'email_sent' => true]);

        $this->assertSame(1, Participant::count());
        $this->assertSame(2, Ticket::count());
        $this->assertSame(1, RecurrenteWebhookEvent::count());

        $webhook = RecurrenteWebhookEvent::firstOrFail();

        $this->assertSame('msg_retry_original', $webhook->svix_id);
        $this->assertNotNull($webhook->processed_at);
        $this->assertNull($webhook->last_error);
        $this->assertTrue(Participant::firstOrFail()->email_sent);
    }

    public function test_email_failure_keeps_tickets_and_marks_the_payment_as_processed(): void
    {
        $event = $this->createEvent();

        $this->mock(PdfGenerationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generateParticipantPdfs')
                ->once()
                ->andReturnUsing(function (Participant $participant): array {
                    $paths = $participant->tickets->map(function (Ticket $ticket) use ($participant): string {
                        $path = "pdfs/event_{$participant->event_id}/participant_{$participant->id}/ticket_{$ticket->id}.pdf";
                        $ticket->update(['archivo_pdf' => $path]);

                        return $path;
                    })->all();

                    $participant->update(['pdf_paths' => $paths]);

                    return $paths;
                });
        });

        $this->mock(EmailService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendParticipantEmail')
                ->once()
                ->andThrow(new \RuntimeException('SMTP no disponible'));
        });

        $payload = $this->payload($event, ['legacy_id' => 'legacy_email_failure']);

        $this->postJson(route('recurrente.pagos'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'ok', 'email_sent' => false]);

        $participant = Participant::firstOrFail();
        $webhook = RecurrenteWebhookEvent::firstOrFail();

        $this->assertFalse((bool) $participant->email_sent);
        $this->assertCount(2, $participant->tickets);
        $this->assertNotNull($webhook->processed_at);
        $this->assertSame('No se pudo enviar el correo de confirmación.', $webhook->last_error);
    }

    public function test_payment_intent_succeeded_event_type_is_supported(): void
    {
        $event = $this->createEvent();
        $this->mockDelivery();
        $payload = $this->payload($event, [
            'type' => 'payment_intent.succeeded',
            'event_type' => null,
            'legacy_id' => 'legacy_payment_intent',
        ]);

        $this->postJson(route('recurrente.pagos'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'ok']);
    }
}

