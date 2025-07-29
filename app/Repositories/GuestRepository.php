<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Guest;

class GuestRepository
{
    public function bulkUpsert(array $guestDataList): void
    {
        if (empty($guestDataList) === true) {
            return;
        }

        $preparedGuests = [];

        foreach ($guestDataList as $guestData) {
            $hash = md5(json_encode($guestData));
            $guestData['sync_hash'] = $hash;
            $preparedGuests[$guestData['id']] = $guestData;
        }

        $existingGuests = Guest::whereIn('id', array_keys($preparedGuests))
                                ->get()
                                ->keyBy('id');

        $guestsToUpsert = [];

        foreach ($preparedGuests as $id => $guestData) {
            $existing = $existingGuests[$id] ?? null;

            if (!$existing || $existing->sync_hash !== $guestData['sync_hash']) {
                $guestsToUpsert[] = $guestData;
            }
        }

        if (empty($guestsToUpsert) === false) {
            $columns = array_keys($guestsToUpsert[0]);
            $updateColumns = array_diff($columns, ['id']);

            Guest::upsert($guestsToUpsert, ['id'], $updateColumns);
        }
    }

}
