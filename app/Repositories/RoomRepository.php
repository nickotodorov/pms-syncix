<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Room;

class RoomRepository
{
    public function updateOrCreate(array $roomData, int $roomTypeId): Room
    {
        $syncHash = md5(json_encode($roomData));
        $model = Room::find($roomData['id']);

        if ($model && $model->sync_hash === $syncHash) {
            return $model;
        }

        return Room::updateOrCreate(
            ['id' => $roomData['id'],],
            array_merge($roomData, ['room_type_id' => $roomTypeId,])
        );
    }
}