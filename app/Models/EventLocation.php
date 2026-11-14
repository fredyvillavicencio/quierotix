<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLocation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'nombre',
        'precio',
        'total_asientos',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'precio' => 'decimal:2',
        'total_asientos' => 'integer',
    ];

    /**
     * Get the event that owns the location.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
