<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'sync_hash',
    ];

    public $incrementing = false;
}
