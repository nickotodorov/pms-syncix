<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\RoomType;

class RoomTypeRepository
{
    public function updateOrCreate(array $roomTypeData): RoomType
    {
        $syncHash = md5(json_encode($roomTypeData));
        $model = RoomType::find($roomTypeData['id']);

        if ($model && $model->sync_hash === $syncHash) {
            return $model;
        }

        return RoomType::updateOrCreate(
            ['id' => $roomTypeData['id']],
            $roomTypeData
        );
    }
}
