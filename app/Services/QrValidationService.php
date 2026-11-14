<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QrValidationService
{
    /**
     * Valida un ticket mediante su código QR
     */
    public function validateTicket(string $qrCode): array
    {
        // Buscar el ticket por código QR
        $ticket = Ticket::where('codigo_qr', $qrCode)->first();

        if (!$ticket) {
            return [
                'valid' => false,
                'message' => 'Ticket no encontrado',
                'ticket' => null,
            ];
        }

        // Verificar si el ticket ya fue validado recientemente (prevenir validaciones duplicadas)
        $cacheKey = 'ticket_validated_' . $qrCode;
        if (Cache::has($cacheKey)) {
            return [
                'valid' => false,
                'message' => 'Este ticket ya fue validado recientemente',
                'ticket' => $ticket,
                'recent_validation' => true,
            ];
        }

        // Marcar como validado
        $ticket->validado = true;
        $ticket->contador_validaciones = $ticket->contador_validaciones + 1;
        $ticket->ultima_validacion = now();
        $ticket->save();

        // Registrar en cache para prevenir validaciones duplicadas (5 minutos)
        Cache::put($cacheKey, true, 300);

        // Registrar la validación en el log
        Log::info('Ticket validado', [
            'ticket_id' => $ticket->id,
            'qr_code' => $qrCode,
            'participant' => $ticket->participant->full_name,
            'seat' => $ticket->seat_code,
            'validation_count' => $ticket->contador_validaciones,
        ]);

        return [
            'valid' => true,
            'message' => 'Ticket validado exitosamente',
            'ticket' => $ticket,
            'validation_count' => $ticket->contador_validaciones,
        ];
    }

    /**
     * Obtiene información de un ticket sin validarlo
     */
    public function getTicketInfo(string $qrCode): array
    {
        $ticket = Ticket::with(['participant', 'event'])
            ->where('codigo_qr', $qrCode)
            ->first();

        if (!$ticket) {
            return [
                'found' => false,
                'message' => 'Ticket no encontrado',
                'ticket' => null,
            ];
        }

        return [
            'found' => true,
            'ticket' => $ticket,
            'participant' => $ticket->participant,
            'event' => $ticket->event,
            'seat_info' => $ticket->seat_location,
            'validation_status' => $ticket->validado ? 'Validado' : 'Pendiente',
            'validation_count' => $ticket->contador_validaciones,
        ];
    }

    /**
     * Revierte la validación de un ticket
     */
    public function revertValidation(string $qrCode): array
    {
        $ticket = Ticket::where('codigo_qr', $qrCode)->first();

        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Ticket no encontrado',
            ];
        }

        if (!$ticket->validado) {
            return [
                'success' => false,
                'message' => 'El ticket no está validado',
            ];
        }

        // Revertir validación
        $ticket->validado = false;
        if ($ticket->contador_validaciones > 0) {
            $ticket->contador_validaciones = $ticket->contador_validaciones - 1;
        }
        $ticket->save();

        // Limpiar cache de validación reciente
        $cacheKey = 'ticket_validated_' . $qrCode;
        Cache::forget($cacheKey);

        Log::info('Validación revertida', [
            'ticket_id' => $ticket->id,
            'qr_code' => $qrCode,
            'participant' => $ticket->participant->full_name,
        ]);

        return [
            'success' => true,
            'message' => 'Validación revertida exitosamente',
            'ticket' => $ticket,
        ];
    }

    /**
     * Obtiene estadísticas de validación
     */
    public function getValidationStatistics(): array
    {
        $totalTickets = Ticket::count();
        $validatedTickets = Ticket::where('validado', true)->count();
        $totalValidations = Ticket::sum('contador_validaciones');

        // Validaciones por hora (últimas 24 horas)
        $hourlyValidations = Ticket::selectRaw('HOUR(ultima_validacion) as hour, COUNT(*) as count')
            ->where('validado', true)
            ->where('ultima_validacion', '>=', now()->subHours(24))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        // Rellenar horas faltantes
        $completeHourlyValidations = [];
        for ($i = 0; $i < 24; $i++) {
            $completeHourlyValidations[$i] = $hourlyValidations[$i] ?? 0;
        }

        // Validaciones por evento
        $eventValidations = Ticket::selectRaw('events.nombre as event_name, COUNT(tickets.id) as validated_count')
            ->join('events', 'tickets.event_id', '=', 'events.id')
            ->where('tickets.validado', true)
            ->groupBy('events.id', 'events.nombre')
            ->orderBy('validated_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'total_tickets' => $totalTickets,
            'validated_tickets' => $validatedTickets,
            'pending_tickets' => $totalTickets - $validatedTickets,
            'validation_rate' => $totalTickets > 0 ? round(($validatedTickets / $totalTickets) * 100, 2) : 0,
            'total_validations' => $totalValidations,
            'hourly_validations' => $completeHourlyValidations,
            'event_validations' => $eventValidations,
        ];
    }

    /**
     * Genera un código QR único
     */
    public function generateUniqueQrCode(): string
    {
        do {
            $qrCode = $this->generateRandomCode();
            $exists = Ticket::where('codigo_qr', $qrCode)->exists();
        } while ($exists);

        return $qrCode;
    }

    /**
     * Genera un código aleatorio
     */
    private function generateRandomCode(): string
    {
        // Generar un código único usando UUID y timestamp
        return 'TKT-' . strtoupper(substr(md5(uniqid() . microtime()), 0, 8)) . '-' . date('Ymd');
    }

    /**
     * Valida múltiples tickets en lote
     */
    public function batchValidate(array $qrCodes): array
    {
        $results = [
            'total' => count($qrCodes),
            'validated' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($qrCodes as $qrCode) {
            $validationResult = $this->validateTicket($qrCode);
            
            $results['details'][] = [
                'qr_code' => $qrCode,
                'valid' => $validationResult['valid'],
                'message' => $validationResult['message'],
            ];

            if ($validationResult['valid']) {
                $results['validated']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Verifica si un ticket es válido para entrada
     */
    public function checkEntryValidity(string $qrCode): array
    {
        $ticket = Ticket::with(['event', 'participant'])
            ->where('codigo_qr', $qrCode)
            ->first();

        if (!$ticket) {
            return [
                'valid' => false,
                'message' => 'Ticket no encontrado',
                'can_enter' => false,
            ];
        }

        $event = $ticket->event;
        $now = now();

        // Verificar si el evento ya pasó
        $eventDate = $event->fecha . ' ' . $event->hora;
        $eventDateTime = \Carbon\Carbon::parse($eventDate);
        
        if ($now->gt($eventDateTime->addHours(2))) { // 2 horas después del evento
            return [
                'valid' => false,
                'message' => 'El evento ya finalizó',
                'can_enter' => false,
                'ticket' => $ticket,
            ];
        }

        // Verificar si es demasiado temprano (más de 1 hora antes)
        if ($now->lt($eventDateTime->subHours(3))) { // 3 horas antes del evento
            return [
                'valid' => false,
                'message' => 'Es demasiado temprano para entrar',
                'can_enter' => false,
                'ticket' => $ticket,
            ];
        }

        // Verificar si ya fue validado
        if ($ticket->validado) {
            return [
                'valid' => true,
                'message' => 'Ticket ya validado anteriormente',
                'can_enter' => true,
                'ticket' => $ticket,
                'previously_validated' => true,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Ticket válido para entrada',
            'can_enter' => true,
            'ticket' => $ticket,
            'previously_validated' => false,
        ];
    }

    /**
     * Obtiene el historial de validaciones de un ticket
     */
    public function getValidationHistory(string $qrCode): array
    {
        $ticket = Ticket::with(['validationLogs'])
            ->where('codigo_qr', $qrCode)
            ->first();

        if (!$ticket) {
            return [
                'found' => false,
                'message' => 'Ticket no encontrado',
                'history' => [],
            ];
        }

        // En un sistema real, tendrías una tabla de logs de validación
        // Por ahora, simulamos el historial
        $history = [
            [
                'action' => 'Generado',
                'timestamp' => $ticket->created_at,
                'details' => 'Ticket creado',
            ],
        ];

        if ($ticket->validado) {
            $history[] = [
                'action' => 'Validado',
                'timestamp' => $ticket->ultima_validacion ?? now(),
                'details' => 'Validado en entrada',
            ];
        }

        return [
            'found' => true,
            'ticket' => $ticket,
            'history' => $history,
            'total_validations' => $ticket->contador_validaciones,
        ];
    }
}