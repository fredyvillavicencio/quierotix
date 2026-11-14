<?php

namespace App\Models;

use App\Services\SeatAssignmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class Ticket extends Model
{
    /**
     * Locación utilizada para tickets de fotógrafos acreditados.
     */
    public const PHOTOGRAPHER_LOCATION = 'Fotógrafo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'participant_id',
        'event_id',
        'codigo_qr',
        'numero',
        'codigo',
        'locacion',
        'asiento',
        'archivo_pdf',
        'validado',
        'validado_por',
        'contador_validaciones',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'numero' => 'integer',
    ];

    /**
     * Bootstrap the model and its generated ticket identifiers.
     */
    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket): void {
            $ticket->numero = $ticket->generateUniqueNumero();
        });

        static::created(function (Ticket $ticket): void {
            $ticket->forceFill([
                'codigo' => $ticket->buildTicketCode(),
            ])->saveQuietly();
        });

        static::updating(function (Ticket $ticket): void {
            if ($ticket->isDirty('event_id')) {
                $ticket->numero = $ticket->generateUniqueNumero(
                    (int) $ticket->event_id,
                    $ticket->id
                );
            }

            if ($ticket->isDirty(['participant_id', 'locacion'])) {
                $ticket->codigo = $ticket->buildTicketCode();
            }
        });
    }

    /**
     * Get the participant that owns the ticket.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Get the event that owns the ticket.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that owns the ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine whether the ticket belongs to an accredited photographer.
     */
    public function getIsPhotographerAttribute(): bool
    {
        return $this->locacion === self::PHOTOGRAPHER_LOCATION;
    }

    /**
     * Get the seat location as a string.
     */
    public function getSeatLocationAttribute(): string
    {
        if ($this->is_photographer) {
            return 'Fotógrafo Acreditado';
        }

        return "{$this->locacion}, Asiento {$this->asiento}";
    }

    /**
     * Get the compact code used to identify the ticket in downloads.
     */
    public function getTicketFileCodeAttribute(): string
    {
        return $this->codigo ?: $this->buildTicketCode();
    }

    /**
     * Build the ticket code from the location initial and related IDs.
     */
    public function buildTicketCode(): string
    {
        $location = trim($this->resolvedLocationName());
        $initial = $location === ''
            ? 'G'
            : mb_strtoupper(mb_substr($location, 0, 1));

        return "{$initial}{$this->participant_id}{$this->id}";
    }

    /**
     * Generate an available number for the ticket event.
     */
    private function generateUniqueNumero(?int $eventId = null, ?int $ignoreTicketId = null): int
    {
        $eventId ??= (int) $this->event_id;
        $ignoreTicketId ??= $this->id;
        $capacity = $this->resolveNumberCapacity($eventId);

        $usedNumbers = static::query()
            ->where('event_id', $eventId)
            ->whereNotNull('numero')
            ->whereBetween('numero', [0, $capacity - 1])
            ->when($ignoreTicketId !== null, fn ($query) => $query->where('id', '!=', $ignoreTicketId))
            ->pluck('numero')
            ->map(fn ($numero): int => (int) $numero)
            ->unique()
            ->values()
            ->all();

        if (count($usedNumbers) >= $capacity) {
            throw new RuntimeException(
                "No hay números disponibles para el evento {$eventId}."
            );
        }

        $usedNumbers = array_fill_keys($usedNumbers, true);
        $start = random_int(0, $capacity - 1);

        for ($offset = 0; $offset < $capacity; $offset++) {
            $candidate = ($start + $offset) % $capacity;

            if (! isset($usedNumbers[$candidate])) {
                return $candidate;
            }
        }

        throw new RuntimeException(
            "No hay números disponibles para el evento {$eventId}."
        );
    }

    /**
     * Resolve how many ticket numbers are available for the event.
     */
    private function resolveNumberCapacity(int $eventId): int
    {
        $capacity = (int) EventLocation::query()
            ->where('event_id', $eventId)
            ->sum('total_asientos');

        return $capacity > 0
            ? $capacity
            : SeatAssignmentService::DEFAULT_LOCATION_CAPACITY;
    }

    /**
     * Get the ticket label used on download pages and PDFs.
     */
    public function getDownloadLabelAttribute(): string
    {
        return "{$this->resolvedLocationName()} - {$this->ticket_file_code}";
    }

    /**
     * Get the filename used when downloading the ticket PDF.
     */
    public function getDownloadFileNameAttribute(): string
    {
        return "ticket_{$this->ticket_file_code}.pdf";
    }

    /**
     * Get the compact location label used on the admin ticket list.
     */
    public function getLocationLabelAttribute(): string
    {
        return "{$this->resolvedLocationName()} {$this->ticket_file_code}";
    }

    /**
     * Resolve the display name for the ticket location.
     */
    private function resolvedLocationName(): string
    {
        return $this->is_photographer
            ? 'Fotógrafo Acreditado'
            : trim((string) $this->locacion);
    }

    /**
     * Get the seat code as a string.
     */
    public function getSeatCodeAttribute(): string
    {
        if ($this->is_photographer) {
            return 'Fotógrafo';
        }

        return "{$this->locacion}-{$this->asiento}";
    }

    /**
     * Mark the ticket as validated.
     */
    public function markAsValidated(): void
    {
        $this->update([
            'validado' => true,
            'contador_validaciones' => $this->contador_validaciones + 1,
        ]);
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
