<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Room;
use App\Models\RoomType;

class RoomRepository
{
    public function updateOrCreate(array $roomData, int $roomTypeId): Room
    {
        $syncHash = md5(json_encode($roomData));
        $model = RoomType::find($roomData['id']);

        if ($model && $model->sync_hash === $syncHash) {
            return $model;
        }

        return Room::updateOrCreate(
            ['id' => $roomData['id'],],
            array_merge($roomData, ['room_type_id' => $roomTypeId,])
        );
    }
}