<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfGenerationService
{
    /**
     * Genera PDFs para todos los participantes de un evento
     */
    public function generateEventPdfs(Event $event): array
    {
        // Aumentar límite de memoria para generación masiva de PDFs
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 3600);

        $results = [
            'total_participants' => 0,
            'pdfs_generated' => 0,
            'errors' => [],
            'participants' => [],
        ];

        $participants = Participant::where('event_id', $event->id)
            ->with('tickets')
            ->get();

        $results['total_participants'] = $participants->count();

        foreach ($participants as $participant) {
            try {
                $pdfPaths = $this->generateParticipantPdfs($participant);
                $results['pdfs_generated'] += count($pdfPaths);
                $results['participants'][] = [
                    'id' => $participant->id,
                    'name' => $participant->full_name,
                    'pdfs' => $pdfPaths,
                ];
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'participant_id' => $participant->id,
                    'participant_name' => $participant->full_name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Genera PDFs para un participante específico
     */
    public function generateParticipantPdfs(Participant $participant): array
    {
        // Aumentar límite de memoria para generación de PDFs
        ini_set('memory_limit', '1024M');

        $pdfPaths = [];
        $event = $participant->event;

        // Crear directorio para el participante
        // NOTA: El disco 'local' está configurado con root en storage/app/private
        $directory = "pdfs/event_{$event->id}/participant_{$participant->id}";
        Storage::disk('local')->makeDirectory($directory);

        // Generar un PDF por cada ticket
        foreach ($participant->tickets as $ticket) {
            $pdfPath = $this->generateSinglePdf($ticket, $directory);
            $pdfPaths[] = $pdfPath;

            // Actualizar el ticket con la ruta del PDF
            $ticket->update(['archivo_pdf' => $pdfPath]);

            // Liberar memoria después de cada ticket
            gc_collect_cycles();
        }

        // Actualizar el participante con las rutas de los PDFs
        $participant->update(['pdf_paths' => $pdfPaths]);

        return $pdfPaths;
    }

    /**
     * Genera un PDF individual para un ticket
     */
    private function generateSinglePdf(Ticket $ticket, string $directory): string
    {
        $participant = $ticket->participant;
        $event = $ticket->event;

        // Generar código QR
        $qrCode = $this->generateQrCode($ticket);

        // Datos para la vista
        $data = [
            'event' => $event,
            'participant' => $participant,
            'ticket' => $ticket,
            'qrCode' => $qrCode,
            'seatLocation' => $ticket->seat_location,
            'seatCode' => $ticket->seat_code,
        ];

        // Generar PDF con configuración optimizada para memoria
        $pdf = Pdf::loadView('pdfs.ticket', $data);

        // Configurar el PDF para usar menos memoria
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('defaultFont', 'sans-serif');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false); // Deshabilitar carga remota para ahorrar memoria
        $pdf->setOption('isPhpEnabled', false); // Deshabilitar PHP en HTML
        $pdf->setOption('isFontSubsettingEnabled', true); // Habilitar subsetting de fuentes
        $pdf->setOption('dpi', 96); // Reducir DPI para ahorrar memoria
        $pdf->setOption('enableCssFloat', false); // Deshabilitar CSS float

        // Nombre del archivo
        $locationSlug = Str::slug($ticket->locacion ?: 'general');
        $filename = "ticket_{$locationSlug}_{$ticket->asiento}_{$ticket->codigo_qr}.pdf";
        $filePath = "{$directory}/{$filename}";

        // Generar y guardar el PDF, liberando memoria después
        $pdfContent = $pdf->output();

        // Liberar memoria del objeto PDF
        unset($pdf);

        // Guardar el contenido del PDF en el disco 'local' (que apunta a storage/app/private)
        $stored = Storage::disk('local')->put($filePath, $pdfContent);

        if (! $stored || ! Storage::disk('local')->exists($filePath)) {
            throw new \Exception("No se pudo guardar el PDF del ticket {$ticket->id}");
        }

        // Liberar memoria del contenido
        unset($pdfContent);

        // Forzar recolección de basura
        gc_collect_cycles();

        return $filePath;
    }

    /**
     * Genera un código QR para un ticket
     */
    private function generateQrCode(Ticket $ticket): string
    {
        // URL de validación (en producción sería la URL real)
        $validationUrl = route('tickets.validate', ['code' => $ticket->codigo_qr]);

        // Generar QR como base64
        $qrCode = QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($validationUrl);

        return 'data:image/png;base64,'.base64_encode($qrCode);
    }

    /**
     * Obtiene la ruta de descarga de un PDF
     */
    public function getDownloadUrl(string $filePath): string
    {
        if (Storage::exists($filePath)) {
            return Storage::url($filePath);
        }

        throw new \Exception("El archivo PDF no existe: {$filePath}");
    }

    /**
     * Elimina todos los PDFs de un evento
     */
    public function deleteEventPdfs(Event $event): bool
    {
        $directory = "pdfs/event_{$event->id}";

        if (Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->deleteDirectory($directory);

            // Actualizar todos los participantes del evento
            Participant::where('event_id', $event->id)->update(['pdf_paths' => null]);

            // Actualizar todos los tickets del evento
            Ticket::where('event_id', $event->id)->update(['archivo_pdf' => '']);

            return true;
        }

        return false;
    }

    /**
     * Elimina los PDFs de un participante específico
     */
    public function deleteParticipantPdfs(Participant $participant): bool
    {
        $directory = "pdfs/event_{$participant->event_id}/participant_{$participant->id}";

        if (Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->deleteDirectory($directory);

            // Actualizar el participante
            $participant->update(['pdf_paths' => null]);

            // Actualizar los tickets del participante
            $participant->tickets()->update(['archivo_pdf' => '']);

            return true;
        }

        return false;
    }

    /**
     * Verifica si los PDFs de un participante existen
     */
    public function checkParticipantPdfs(Participant $participant): array
    {
        $results = [
            'all_exist' => true,
            'missing_files' => [],
            'existing_files' => [],
        ];

        $pdfPaths = $participant->pdf_paths ?? [];

        foreach ($pdfPaths as $pdfPath) {
            // Verificar en disco 'local' primero (storage/app/private)
            if (Storage::disk('local')->exists($pdfPath)) {
                $results['existing_files'][] = [
                    'path' => $pdfPath,
                    'url' => Storage::disk('local')->url($pdfPath),
                    'size' => Storage::disk('local')->size($pdfPath),
                ];
            } elseif (Storage::exists($pdfPath)) {
                // Verificar en disco por defecto como fallback
                $results['existing_files'][] = [
                    'path' => $pdfPath,
                    'url' => Storage::url($pdfPath),
                    'size' => Storage::size($pdfPath),
                ];
            } else {
                $results['all_exist'] = false;
                $results['missing_files'][] = $pdfPath;
            }
        }

        return $results;
    }

    /**
     * Genera un archivo ZIP con todos los PDFs de un evento
     */
    public function generateEventZip(Event $event): ?string
    {
        $directory = "pdfs/event_{$event->id}";

        if (! Storage::disk('local')->exists($directory)) {
            return null;
        }

        $zipPath = "zips/event_{$event->id}_pdfs.zip";

        // Crear archivo ZIP
        $zip = new \ZipArchive;
        $fullZipPath = Storage::disk('local')->path($zipPath);

        if ($zip->open($fullZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $files = Storage::disk('local')->allFiles($directory);

            foreach ($files as $file) {
                $relativePath = str_replace("{$directory}/", '', $file);
                $zip->addFile(Storage::disk('local')->path($file), $relativePath);
            }

            $zip->close();

            return $zipPath;
        }

        return null;
    }

    /**
     * Obtiene estadísticas de generación de PDFs para un evento
     */
    public function getPdfStatistics(Event $event): array
    {
        $totalParticipants = Participant::where('event_id', $event->id)->count();
        $participantsWithPdfs = Participant::where('event_id', $event->id)
            ->whereNotNull('pdf_paths')
            ->count();

        $totalTickets = Ticket::where('event_id', $event->id)->count();
        $ticketsWithPdfs = Ticket::where('event_id', $event->id)
            ->where('archivo_pdf', '!=', '')
            ->count();

        return [
            'total_participants' => $totalParticipants,
            'participants_with_pdfs' => $participantsWithPdfs,
            'participants_without_pdfs' => $totalParticipants - $participantsWithPdfs,
            'percentage_with_pdfs' => $totalParticipants > 0 ? round(($participantsWithPdfs / $totalParticipants) * 100, 2) : 0,

            'total_tickets' => $totalTickets,
            'tickets_with_pdfs' => $ticketsWithPdfs,
            'tickets_without_pdfs' => $totalTickets - $ticketsWithPdfs,
            'percentage_tickets_with_pdfs' => $totalTickets > 0 ? round(($ticketsWithPdfs / $totalTickets) * 100, 2) : 0,
        ];
    }
}
