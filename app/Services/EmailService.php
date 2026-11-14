<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;

class EmailService
{
    /**
     * Envía emails con los PDFs a todos los participantes de un evento
     */
    public function sendEventEmails(Event $event): array
    {
        $results = [
            'total_participants' => 0,
            'emails_sent' => 0,
            'emails_failed' => 0,
            'errors' => [],
            'participants' => [],
        ];

        $participants = Participant::where('event_id', $event->id)
            ->with('tickets')
            ->get();

        $results['total_participants'] = $participants->count();

        foreach ($participants as $participant) {
            try {
                $this->sendParticipantEmail($participant);
                $results['emails_sent']++;
                $results['participants'][] = [
                    'id' => $participant->id,
                    'name' => $participant->full_name,
                    'email' => $participant->email,
                    'status' => 'sent',
                ];
            } catch (\Exception $e) {
                $results['emails_failed']++;
                $results['errors'][] = [
                    'participant_id' => $participant->id,
                    'participant_name' => $participant->full_name,
                    'email' => $participant->email,
                    'error' => $e->getMessage(),
                ];
                
                $results['participants'][] = [
                    'id' => $participant->id,
                    'name' => $participant->full_name,
                    'email' => $participant->email,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Envía un email a un participante específico con sus PDFs
     */
    public function sendParticipantEmail(Participant $participant): bool
    {
        $event = $participant->event;
        
        // Verificar que el participante tenga PDFs generados
        if (empty($participant->pdf_paths)) {
            throw new \Exception("El participante no tiene PDFs generados");
        }

        // Verificar que los PDFs existan físicamente
        foreach ($participant->pdf_paths as $pdfPath) {
            if (!Storage::exists($pdfPath)) {
                throw new \Exception("Uno o más PDFs no existen en el sistema de archivos");
            }
        }

        // Usar Brevo (Sendinblue) si está configurado
        if ($this->isBrevoConfigured()) {
            return $this->sendViaBrevo($participant);
        }

        // Usar el sistema de correo de Laravel como fallback
        return $this->sendViaLaravel($participant);
    }

    /**
     * Verifica si Brevo está configurado
     */
    private function isBrevoConfigured(): bool
    {
        return !empty(config('services.brevo.key'));
    }

    /**
     * Envía el email usando Brevo (Sendinblue)
     */
    private function sendViaBrevo(Participant $participant): bool
    {
        $event = $participant->event;
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', config('services.brevo.key'));

        $apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );

        // Crear el email SIN adjuntos (solo links de descarga)
        $sendSmtpEmail = new SendSmtpEmail([
            'subject' => "Acto Solemne de Investidura Profesional - UMG 2026",
            'sender' => [
                'name' => config('mail.from.name', 'Sistema de Tickets'),
                'email' => config('mail.from.address', 'noreply@tickets.com'),
            ],
            'to' => [[
                'email' => $participant->email,
                'name' => $participant->full_name,
            ]],
            'htmlContent' => $this->getEmailHtmlContent($participant),
            'textContent' => $this->getEmailTextContent($participant),
            // No incluir attachments ya que solo se envían links
        ]);

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            
            // Registrar el envío en la base de datos
            $participant->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error al enviar email via Brevo: " . $e->getMessage());
        }
    }

    /**
     * Envía el email usando el sistema de correo de Laravel
     */
    private function sendViaLaravel(Participant $participant): bool
    {
        try {
            Mail::html($this->getEmailHtmlContent($participant), function (Message $message) use ($participant) {
                $message->to($participant->email, $participant->full_name)
                    ->subject('Acto Solemne de Investidura Profesional - UMG 2026');
            });
            
            // Registrar el envío en la base de datos
            $participant->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error al enviar email via Laravel: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el contenido HTML del email basado en la plantilla Blade
     */
    private function getEmailHtmlContent(Participant $participant): string
    {
        $event = $participant->event;
        $tickets = $participant->tickets;
        
        // Generar URLs de descarga para cada ticket
        $downloadUrls = [];
        foreach ($tickets as $ticket) {
            $downloadUrls[] = route('admin.tickets.download.pdf', $ticket->id);
        }
        
        // URL para descargar todos los PDFs en ZIP
        $allPdfsUrl = route('admin.participants.download.pdfs', $participant->id);
        
        // Renderizar la plantilla Blade
        return view('emails.email', [
            'participant' => $participant,
            'event' => $event,
            'tickets' => $tickets,
            'downloadUrls' => $downloadUrls,
            'allPdfsUrl' => $allPdfsUrl,
        ])->render();
    }

    /**
     * Obtiene el contenido de texto plano del email
     */
    private function getEmailTextContent(Participant $participant): string
    {
        $event = $participant->event;
        $tickets = $participant->tickets;
        
        // Convertir fechas a Carbon si son strings
        $fecha = \Carbon\Carbon::parse($event->fecha);
        $hora = \Carbon\Carbon::parse($event->hora);
        
        $text = "{$event->nombre}\n";
        $text .= "Fecha: {$fecha->format('d/m/Y')} a las {$hora->format('h:i A')}\n\n";
        $text .= "Hola {$participant->nombre},\n\n";
        $text .= "Adjunto encontrarás tus tickets para el evento '{$event->nombre}'.\n\n";
        $text .= "Tus Asientos:\n";
        
        foreach ($tickets as $ticket) {
            $text .= "- Asiento: {$ticket->seat_code}\n";
            $text .= "  Ubicación: {$ticket->seat_location}\n";
            $text .= "  Código QR: " . substr($ticket->codigo_qr, 0, 8) . "...\n\n";
        }
        
        $text .= "Instrucciones importantes:\n";
        $text .= "1. Guarda estos archivos PDF en un lugar seguro\n";
        $text .= "2. Presenta el código QR en la entrada del evento\n";
        $text .= "3. Cada ticket es único e intransferible\n";
        $text .= "4. Llega con anticipación al evento\n\n";
        $text .= "Para cualquier consulta, contacta a la administración del evento.\n\n";
        $text .= "¡Esperamos verte pronto!\n\n";
        $text .= "---\n";
        $text .= "Este email fue generado automáticamente por el Sistema de Gestión de Tickets\n";
        $text .= "No respondas a este email\n";
        
        return $text;
    }

    /**
     * Verifica el estado de los emails enviados para un evento
     */
    public function getEmailStatus(Event $event): array
    {
        $participants = Participant::where('event_id', $event->id)->get();
        
        $status = [
            'total' => $participants->count(),
            'sent' => $participants->where('email_sent', true)->count(),
            'not_sent' => $participants->where('email_sent', false)->count(),
            'with_pdfs' => $participants->whereNotNull('pdf_paths')->count(),
            'without_pdfs' => $participants->whereNull('pdf_paths')->count(),
            'participants' => [],
        ];

        foreach ($participants as $participant) {
            $status['participants'][] = [
                'id' => $participant->id,
                'name' => $participant->full_name,
                'email' => $participant->email,
                'email_sent' => (bool) $participant->email_sent,
                'email_sent_at' => $participant->email_sent_at,
                'has_pdfs' => !empty($participant->pdf_paths),
                'pdf_count' => count($participant->pdf_paths ?? []),
            ];
        }

        return $status;
    }

    /**
     * Reenvía emails a participantes que no los recibieron
     */
    public function resendFailedEmails(Event $event): array
    {
        $participants = Participant::where('event_id', $event->id)
            ->where('email_sent', false)
            ->whereNotNull('pdf_paths')
            ->get();

        $results = [
            'attempted' => $participants->count(),
            'resent' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($participants as $participant) {
            try {
                $this->sendParticipantEmail($participant);
                $results['resent']++;
            } catch (\Exception $e) {
                $results['failed']++;
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
     * Envía un email de prueba
     */
    public function sendTestEmail(string $email): bool
    {
        if ($this->isBrevoConfigured()) {
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', config('services.brevo.key'));

            $apiInstance = new TransactionalEmailsApi(
                new Client(),
                $config
            );

            $sendSmtpEmail = new SendSmtpEmail([
                'subject' => 'Email de prueba - Sistema de Tickets',
                'sender' => [
                    'name' => config('mail.from.name', 'Sistema de Tickets'),
                    'email' => config('mail.from.address', 'noreply@tickets.com'),
                ],
                'to' => [[
                    'email' => $email,
                    'name' => 'Usuario de Prueba',
                ]],
                'htmlContent' => '<h1>Email de prueba</h1><p>Este es un email de prueba del Sistema de Tickets.</p>',
                'textContent' => "Email de prueba\nEste es un email de prueba del Sistema de Tickets.",
            ]);

            try {
                $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
                return true;
            } catch (\Exception $e) {
                throw new \Exception("Error al enviar email de prueba via Brevo: " . $e->getMessage());
            }
        }

        // Usar Laravel Mail como fallback
        try {
            Mail::raw('Email de prueba del Sistema de Tickets', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Email de prueba - Sistema de Tickets');
            });
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error al enviar email de prueba: " . $e->getMessage());
        }
    }
}