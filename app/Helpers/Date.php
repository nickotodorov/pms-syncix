<?php

declare(strict_types=1);

namespace App\Helpers;

class Date
{
    public static function isValidDate(string $date): bool
    {
        return strtotime($date) !== false;
    }
}