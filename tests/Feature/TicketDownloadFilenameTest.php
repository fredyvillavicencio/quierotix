<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicketDownloadFilenameTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(): Event
    {
        return Event::create([
            'codigo_evento' => 'evento-'.Str::lower(Str::random(10)),
            'nombre' => 'Evento de Descarga',
            'fecha' => '2030-08-20',
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

    private function createTicket(Event $event, Participant $participant, string $locacion = 'Backstage', int $asiento = 115): Ticket
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

    private function storePdf(Ticket $ticket): string
    {
        $path = "pdfs/event_{$ticket->event_id}/participant_{$ticket->participant_id}/ticket_{$ticket->id}.pdf";

        Storage::disk('local')->put($path, '%PDF-1.4 prueba');
        $ticket->update(['archivo_pdf' => $path]);

        return $path;
    }

    public function test_ticket_download_uses_location_initial_participant_and_ticket_ids(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $ticket = $this->createTicket($event, $participant);
        $pdfPath = $this->storePdf($ticket);

        $expectedFilename = "ticket_B{$participant->id}{$ticket->id}.pdf";

        $this->assertSame($expectedFilename, $ticket->download_file_name);

        $this->get(route('admin.tickets.download.pdf', $ticket))
            ->assertOk()
            ->assertDownload($expectedFilename);

        Storage::disk('local')->delete($pdfPath);
    }

    public function test_photographer_ticket_download_uses_f_initial(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $ticket = $this->createTicket($event, $participant, Ticket::PHOTOGRAPHER_LOCATION, 1);
        $pdfPath = $this->storePdf($ticket);

        $expectedFilename = "ticket_F{$participant->id}{$ticket->id}.pdf";

        $this->get(route('admin.tickets.download.pdf', $ticket))
            ->assertOk()
            ->assertDownload($expectedFilename);

        Storage::disk('local')->delete($pdfPath);
    }

    public function test_participant_zip_uses_ticket_download_filenames(): void
    {
        $event = $this->createEvent();
        $participant = $this->createParticipant($event);
        $backstageTicket = $this->createTicket($event, $participant, 'Backstage', 115);
        $vipTicket = $this->createTicket($event, $participant, 'VIP', 1);

        $pdfPaths = [
            $this->storePdf($backstageTicket),
            $this->storePdf($vipTicket),
        ];

        $participant->update(['pdf_paths' => $pdfPaths]);

        $expectedEntries = [
            "ticket_B{$participant->id}{$backstageTicket->id}.pdf",
            "ticket_V{$participant->id}{$vipTicket->id}.pdf",
        ];

        $response = $this->get(route('admin.participants.download.pdfs', $participant));

        $response->assertOk();

        $zipPath = $response->baseResponse->getFile()->getPathname();
        $zip = new \ZipArchive;

        $this->assertTrue($zip->open($zipPath));

        $entries = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entries[] = $zip->getNameIndex($index);
        }

        $zip->close();

        $this->assertEqualsCanonicalizing($expectedEntries, $entries);

        Storage::disk('local')->delete($pdfPaths);
        @unlink($zipPath);
    }
}
