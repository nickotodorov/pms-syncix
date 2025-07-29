<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $fillable = [
        'id',
        'number',
        'floor',
        'room_type_id',
        'sync_hash',
    ];

    public $incrementing = false;

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
