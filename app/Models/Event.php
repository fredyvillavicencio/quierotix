<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo_evento',
        'recurrente_product_id',
        'recurrente_product_url',
        'nombre',
        'fecha',
        'hora',
        'lugar',
        'direccion',
        'precio',
        'imagen',
        'imagen_ticket',
        'imagen_cover',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'precio' => 'decimal:2',
    ];

    /**
     * Get the public URL for the event cover image.
     */
    protected function imagenUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->imagen ? url('public/'.$this->imagen) : null
        );
    }

    /**
     * Get the public URL for the event ticket image.
     */
    protected function imagenTicketUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->imagen_ticket ? url('public/'.$this->imagen_ticket) : null
        );
    }

    /**
     * Get the public URL for the event purchase page cover image.
     */
    protected function imagenCoverUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->imagen_cover ? url('public/'.$this->imagen_cover) : null
        );
    }

    /**
     * Get the locations (sections) configured for the event.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(EventLocation::class);
    }

    /**
     * Determine whether the event has configured locations.
     */
    public function hasLocations(): bool
    {
        return $this->locations()->exists();
    }

    /**
     * Get the participants for the event.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Get the tickets for the event.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
