<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $fillable = [
        'id',
        'name',
        'description',
        'sync_hash',
    ];

    public $incrementing = false;
}
