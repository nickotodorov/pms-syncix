<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    protected $fillable = [
        'id',
        'external_id',
        'arrival_date',
        'departure_date',
        'status',
        'notes',
        'room_id',
        'room_type_id',
        'sync_hash',
    ];

    public $incrementing = false;

    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
