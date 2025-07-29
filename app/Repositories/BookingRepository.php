<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Booking;

class BookingRepository
{
    public function findById(int $id): ?Booking
    {
        return Booking::find($id);
    }

    public function updateOrCreate(
        array $bookingData,
        int $roomId,
        int $roomTypeId,
        string $syncHash,
    ): Booking
    {
        return Booking::updateOrCreate(
            ['id' => $bookingData['id']],
            [
                'external_id' => $bookingData['external_id'] ?? null,
                'arrival_date' => $bookingData['arrival_date'],
                'departure_date' => $bookingData['departure_date'],
                'status' => $bookingData['status'],
                'notes' => $bookingData['notes'] ?? null,
                'room_id' => $roomId,
                'room_type_id' => $roomTypeId,
                'sync_hash' => $syncHash,
            ]
        );
    }
}
