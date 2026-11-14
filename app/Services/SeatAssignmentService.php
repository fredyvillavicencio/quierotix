<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeatAssignmentService
{
    /**
     * Cantidad máxima de tickets que puede solicitar un comprador.
     */
    public const MAX_TICKETS_PER_PURCHASE = 10;

    /**
     * Locación usada cuando el evento no configura locaciones.
     */
    public const DEFAULT_LOCATION_NAME = 'Ticket';

    /**
     * Capacidad de la locación General de respaldo.
     */
    public const DEFAULT_LOCATION_CAPACITY = 100;

    /**
     * Asigna asientos de una locación a un participante.
     *
     * @param  int  $quantity  Cantidad de tickets a generar.
     * @param  string|null  $locationName  Locación solicitada. Si es null se usa la primera con capacidad.
     */
    public function assignSeatsToParticipant(Participant $participant, int $quantity = self::MAX_TICKETS_PER_PURCHASE, ?string $locationName = null): array
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('La cantidad de tickets debe ser al menos 1.');
        }

        $event = $participant->event;
        $location = $this->resolveLocation($event, $locationName, $quantity);

        if ($location === null) {
            throw new \RuntimeException('No hay suficientes asientos disponibles en una locación para la cantidad solicitada.');
        }

        $seats = $this->getAvailableSeats($event, $quantity, $location['nombre']);

        if (count($seats) < $quantity) {
            throw new \RuntimeException('No hay suficientes asientos disponibles para asignar.');
        }

        $tickets = [];

        foreach ($seats as $seat) {
            $tickets[] = $this->createTicket(
                $participant,
                $seat['locacion'],
                $seat['asiento']
            );
        }

        return $tickets;
    }

    /**
     * Asigna un ticket de acreditación a un fotógrafo.
     */
    public function assignSeatsToFotografos(Participant $participant): array
    {
        return [
            $this->createTicket(
                $participant,
                Ticket::PHOTOGRAPHER_LOCATION,
                $participant->id
            ),
        ];
    }

    /**
     * Obtiene las locaciones disponibles con sus precios y cupos.
     *
     * @return array<int, array{id: int|null, nombre: string, precio: float, total_asientos: int, vendidos: int, disponibles: int}>
     */
    public function getLocationOptions(Event $event): array
    {
        $soldByLocation = $this->getSoldCounts($event);
        $locations = $event->locations()->orderBy('id')->get();

        if ($locations->isEmpty()) {
            $sold = (int) ($soldByLocation[self::DEFAULT_LOCATION_NAME] ?? 0);

            return [[
                'id' => null,
                'nombre' => self::DEFAULT_LOCATION_NAME,
                'precio' => (float) $event->precio,
                'total_asientos' => self::DEFAULT_LOCATION_CAPACITY,
                'vendidos' => $sold,
                'disponibles' => max(0, self::DEFAULT_LOCATION_CAPACITY - $sold),
            ]];
        }

        return $locations->map(function ($location) use ($soldByLocation): array {
            $sold = (int) ($soldByLocation[$location->nombre] ?? 0);
            $capacity = (int) $location->total_asientos;

            return [
                'id' => $location->id,
                'nombre' => $location->nombre,
                'precio' => (float) $location->precio,
                'total_asientos' => $capacity,
                'vendidos' => $sold,
                'disponibles' => max(0, $capacity - $sold),
            ];
        })->all();
    }

    /**
     * Cuenta los asientos disponibles de un evento o de una locación.
     */
    public function getAvailableSeatCount(Event $event, ?string $locationName = null): int
    {
        $options = $this->getLocationOptions($event);

        if ($locationName !== null) {
            foreach ($options as $option) {
                if ($this->sameLocation($option['nombre'], $locationName)) {
                    return $option['disponibles'];
                }
            }

            return 0;
        }

        return array_sum(array_column($options, 'disponibles'));
    }

    /**
     * Verifica si hay suficientes asientos disponibles para una cantidad.
     */
    public function canAssignSeats(Event $event, int $quantity, ?string $locationName = null): bool
    {
        if ($quantity < 1) {
            return false;
        }

        if ($locationName !== null) {
            return $this->getAvailableSeatCount($event, $locationName) >= $quantity;
        }

        foreach ($this->getLocationOptions($event) as $option) {
            if ($option['disponibles'] >= $quantity) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene asientos libres de una locación.
     *
     * @return array<int, array{locacion: string, asiento: int}>
     */
    public function getAvailableSeats(Event $event, ?int $limit = null, ?string $locationName = null): array
    {
        $limit = $limit !== null ? max(1, $limit) : null;
        $options = $this->getLocationOptions($event);
        $location = null;

        if ($locationName !== null) {
            foreach ($options as $option) {
                if ($this->sameLocation($option['nombre'], $locationName)) {
                    $location = $option;
                    break;
                }
            }
        } else {
            $location = collect($options)->first(
                fn (array $option): bool => $limit === null
                    ? $option['disponibles'] > 0
                    : $option['disponibles'] >= $limit
            ) ?? collect($options)->first(fn (array $option): bool => $option['disponibles'] > 0);
        }

        if ($location === null || $location['disponibles'] < 1) {
            return [];
        }

        $occupied = Ticket::where('event_id', $event->id)
            ->where('locacion', $location['nombre'])
            ->pluck('asiento')
            ->mapWithKeys(fn ($seat): array => [(int) $seat => true])
            ->all();

        $seats = [];

        for ($asiento = 1; $asiento <= $location['total_asientos']; $asiento++) {
            if (isset($occupied[$asiento])) {
                continue;
            }

            $seats[] = [
                'locacion' => $location['nombre'],
                'asiento' => $asiento,
            ];

            if ($limit !== null && count($seats) >= $limit) {
                break;
            }
        }

        return $seats;
    }

    /**
     * Obtiene el siguiente asiento disponible para un evento.
     */
    public function getNextAvailableSeat(Event $event): ?array
    {
        return $this->getAvailableSeats($event, 1)[0] ?? null;
    }

    /**
     * Obtiene estadísticas de asientos para un evento.
     */
    public function getSeatStatistics(Event $event): array
    {
        $options = $this->getLocationOptions($event);
        $validatedByLocation = Ticket::where('event_id', $event->id)
            ->where('validado', true)
            ->select('locacion', DB::raw('COUNT(*) as total'))
            ->groupBy('locacion')
            ->pluck('total', 'locacion')
            ->map(fn ($total): int => (int) $total)
            ->all();

        $locations = array_map(function (array $location) use ($validatedByLocation): array {
            $sold = (int) $location['vendidos'];
            $validated = (int) ($validatedByLocation[$location['nombre']] ?? 0);

            $location['validados'] = $validated;
            $location['porcentaje_validados'] = $sold > 0
                ? round(($validated / $sold) * 100, 2)
                : 0;

            return $location;
        }, $options);

        $totalSeats = array_sum(array_column($locations, 'total_asientos'));
        $assignedSeats = array_sum(array_column($locations, 'vendidos'));
        $availableSeats = array_sum(array_column($locations, 'disponibles'));
        $validatedSeats = array_sum(array_column($locations, 'validados'));

        return [
            'total_seats' => $totalSeats,
            'assigned_seats' => $assignedSeats,
            'available_seats' => $availableSeats,
            'validated_seats' => $validatedSeats,
            'percentage_assigned' => $totalSeats > 0 ? round(($assignedSeats / $totalSeats) * 100, 2) : 0,
            'percentage_validated' => $assignedSeats > 0 ? round(($validatedSeats / $assignedSeats) * 100, 2) : 0,
            'locations' => $locations,
        ];
    }

    /**
     * Obtiene el mapa de asientos agrupado por locación.
     *
     * @return array<string, array{precio: float, total_asientos: int, assigned: int, available: int, asientos: array<int, array{assigned: bool, validated: bool, participant: string|null}>}>
     */
    public function getSeatMap(Event $event): array
    {
        $tickets = Ticket::where('event_id', $event->id)
            ->where('locacion', '!=', Ticket::PHOTOGRAPHER_LOCATION)
            ->with('participant')
            ->get()
            ->keyBy(fn (Ticket $ticket): string => $ticket->locacion.'|'.$ticket->asiento);

        $seatMap = [];

        foreach ($this->getLocationOptions($event) as $location) {
            $seats = [];
            $assigned = 0;

            for ($asiento = 1; $asiento <= $location['total_asientos']; $asiento++) {
                $ticket = $tickets->get($location['nombre'].'|'.$asiento);

                if ($ticket !== null) {
                    $assigned++;
                }

                $seats[$asiento] = [
                    'assigned' => $ticket !== null,
                    'validated' => (bool) $ticket?->validado,
                    'participant' => $ticket?->participant?->full_name,
                ];
            }

            $seatMap[$location['nombre']] = [
                'precio' => $location['precio'],
                'total_asientos' => $location['total_asientos'],
                'assigned' => $assigned,
                'available' => max(0, $location['total_asientos'] - $assigned),
                'asientos' => $seats,
            ];
        }

        return $seatMap;
    }

    /**
     * Resuelve la locación donde se asignarán los asientos.
     */
    private function resolveLocation(Event $event, ?string $locationName, int $quantity): ?array
    {
        $options = $this->getLocationOptions($event);

        if ($locationName !== null) {
            foreach ($options as $option) {
                if ($this->sameLocation($option['nombre'], $locationName)) {
                    return $option;
                }
            }

            return null;
        }

        if (count($options) === 1) {
            return $options[0];
        }

        foreach ($options as $option) {
            if ($option['disponibles'] >= $quantity) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Obtiene la cantidad de tickets vendidos por locación.
     *
     * @return array<string, int>
     */
    private function getSoldCounts(Event $event): array
    {
        return Ticket::where('event_id', $event->id)
            ->select('locacion', DB::raw('COUNT(*) as total'))
            ->groupBy('locacion')
            ->pluck('total', 'locacion')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }

    /**
     * Compara nombres de locación sin distinguir mayúsculas.
     */
    private function sameLocation(string $first, string $second): bool
    {
        return mb_strtolower(trim($first)) === mb_strtolower(trim($second));
    }

    /**
     * Crea un ticket individual.
     */
    private function createTicket(Participant $participant, string $location, int $seat): Ticket
    {
        return Ticket::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'codigo_qr' => $this->generateUniqueQrCode(),
            'locacion' => $location,
            'asiento' => $seat,
            'archivo_pdf' => '',
            'validado' => false,
            'contador_validaciones' => 0,
        ]);
    }

    /**
     * Genera un código QR único.
     */
    private function generateUniqueQrCode(): string
    {
        do {
            $code = Str::uuid()->toString();
        } while (Ticket::where('codigo_qr', $code)->exists());

        return $code;
    }
}
