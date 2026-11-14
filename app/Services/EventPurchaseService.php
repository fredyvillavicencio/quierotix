<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EventPurchaseService
{
    public function __construct(
        private readonly SeatAssignmentService $seatAssignmentService,
        private readonly PdfGenerationService $pdfGenerationService,
        private readonly EmailService $emailService,
    ) {}

    /**
     * Registra al comprador y le asigna sus asientos.
     *
     * @param  array{nombre: string, apellido: string, email: string, telefono?: string|null, carne?: string|null}  $participantData
     */
    public function purchase(Event $event, array $participantData, int $quantity, ?string $locationName = null): Participant
    {
        if ($quantity < 1 || $quantity > SeatAssignmentService::MAX_TICKETS_PER_PURCHASE) {
            throw new \InvalidArgumentException(
                'La cantidad de tickets debe estar entre 1 y '.SeatAssignmentService::MAX_TICKETS_PER_PURCHASE.'.'
            );
        }

        $locationName = trim((string) $locationName);

        return DB::transaction(function () use ($event, $participantData, $quantity, $locationName): Participant {
            // Bloquea el evento para serializar las compras y usar datos frescos.
            $lockedEvent = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

            $locations = $this->seatAssignmentService->getLocationOptions($lockedEvent);
            $location = $this->findLocationOption($locations, $locationName);

            if ($location === null) {
                throw new \RuntimeException('La locación seleccionada no está disponible para este evento.');
            }

            if (! $this->seatAssignmentService->canAssignSeats($lockedEvent, $quantity, $location['nombre'])) {
                throw new \RuntimeException('No hay suficientes asientos disponibles en esta locación para la cantidad de tickets solicitada.');
            }

            $participant = Participant::create([
                'event_id' => $lockedEvent->id,
                'carne' => $participantData['carne'] ?? 'CF',
                'nombre' => $participantData['nombre'],
                'apellido' => $participantData['apellido'] ?? '',
                'email' => $participantData['email'],
                'telefono' => $participantData['telefono'] ?? '',
            ]);

            $this->seatAssignmentService->assignSeatsToParticipant($participant, $quantity, $location['nombre']);

            return $participant;
        }, 3);
    }

    /**
     * Busca una locación por nombre. Si no se solicitó ninguna, usa la única disponible.
     *
     * @param  array<int, array{id: int|null, nombre: string, precio: float, total_asientos: int, vendidos: int, disponibles: int}>  $locations
     * @return array{id: int|null, nombre: string, precio: float, total_asientos: int, vendidos: int, disponibles: int}|null
     */
    public function findLocationOption(array $locations, string $requestedName): ?array
    {
        $requestedName = trim($requestedName);

        if ($requestedName === '') {
            return count($locations) === 1 ? $locations[0] : null;
        }

        foreach ($locations as $location) {
            if (mb_strtolower($location['nombre']) === mb_strtolower($requestedName)) {
                return $location;
            }
        }

        return null;
    }

    /**
     * Genera los PDFs de todos los tickets del participante.
     *
     * @return array<int, string>
     */
    public function generatePdfs(Participant $participant): array
    {
        $participant->load('tickets');

        return $this->pdfGenerationService->generateParticipantPdfs($participant);
    }

    /**
     * Envía el correo de confirmación al participante.
     */
    public function sendEmail(Participant $participant): bool
    {
        $participant->loadMissing(['event', 'tickets']);

        return $this->emailService->sendParticipantEmail($participant);
    }

    /**
     * Genera los PDFs y envía el correo. Un fallo de correo no elimina los tickets.
     *
     * @return array{pdf_paths: array<int, string>, email_sent: bool}
     */
    public function deliver(Participant $participant): array
    {
        $participant->loadMissing(['event', 'tickets']);
        $pdfPaths = $participant->pdf_paths ?? [];

        if (! $this->hasGeneratedPdfs($participant)) {
            $pdfPaths = $this->generatePdfs($participant);
            $participant->refresh();
        }

        $emailSent = (bool) $participant->email_sent;

        if (! $emailSent) {
            try {
                $emailSent = $this->sendEmail($participant);
            } catch (Throwable $exception) {
                Log::error('Error al enviar email de compra', [
                    'event_id' => $participant->event_id,
                    'participant_id' => $participant->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $participant->refresh();

        return [
            'pdf_paths' => $pdfPaths,
            'email_sent' => $emailSent,
        ];
    }

    /**
     * Verifica que las rutas de PDFs existan físicamente en el disco local.
     */
    private function hasGeneratedPdfs(Participant $participant): bool
    {
        $pdfPaths = $participant->pdf_paths ?? [];

        if ($pdfPaths === []) {
            return false;
        }

        foreach ($pdfPaths as $pdfPath) {
            if (! Storage::disk('local')->exists($pdfPath)) {
                return false;
            }
        }

        return true;
    }
}
