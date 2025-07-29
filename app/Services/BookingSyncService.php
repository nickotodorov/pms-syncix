<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\GuestRepository;
use App\Repositories\RoomRepository;
use App\Repositories\RoomTypeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookingSyncService
{
    private float $lastRequestTime = 0.0;

    private array $failedBookings = [];

    public function __construct(
        public readonly PmsAPIService $pmsApiService,
        public readonly BookingRepository $bookingRepo,
        public readonly GuestRepository $guestRepo,
        public readonly RoomRepository $roomRepo,
        public readonly RoomTypeRepository $roomTypeRepo,
    )
    {
    }

    public function syncBookings(array $bookings, ?string $since = null, ?callable $onProgress = null): int
    {
        $processedBookingsCount = 0;
        $total = count($bookings);

        if ($total === 0) {
            return 0;
        }

        $rooms = [];
        $roomTypes = [];
        $guests = [];

        foreach ($bookings as $bookingId) {
            DB::beginTransaction();

            try {
                $this->throttle();

                $booking = $this->pmsApiService->getBooking($bookingId);
                $roomTypeId = $booking['room_type_id'];
                $roomId = $booking['room_id'];
                $bookingGuests = $booking['guest_ids'];

                if (in_array($roomTypeId, $roomTypes, true) === false) {
                    $this->throttle();

                    $roomTypes[$roomTypeId] = $this->pmsApiService->getRoomType($roomTypeId);
                    $this->roomTypeRepo->updateOrCreate($roomTypes[$roomTypeId]);
                }

                if (in_array($roomId, $rooms, true) === false) {
                    $this->throttle();

                    $rooms[$roomId] = $this->pmsApiService->getRoom($roomId);
                    $this->roomRepo->updateOrCreate($rooms[$roomId], $roomTypeId);
                }

                $guestsToUpsert = [];
                $guestIdsToSync = [];

                foreach ($bookingGuests as $guestId) {
                    if (!isset($guests[$guestId])) {
                        $this->throttle();
                        $guestData = $this->pmsApiService->getGuest($guestId);
                        $guests[$guestId] = $guestData;
                        $guestsToUpsert[] = $guestData;
                    }
                    $guestIdsToSync[] = $guestId;
                }

                $this->guestRepo->bulkUpsert($guestsToUpsert);

                $bookingHash = md5(json_encode($booking));
                $bookingModel = $this->bookingRepo->findById($bookingId);

                if (!$bookingModel || $bookingModel->sync_hash !== $bookingHash) {
                    $bookingModel = $this->bookingRepo->updateOrCreate(
                        $booking,
                        $roomId,
                        $roomTypeId,
                        $bookingHash
                    );
                }

                $bookingModel->guests()
                    ->syncWithoutDetaching($guestIdsToSync);

                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();

                $this->failedBookings[] = $bookingId;
                Log::error("Booking sync failed: {$e->getMessage()}", [
                    'booking_id' => $bookingId,
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $processedBookingsCount++;

            if (is_callable($onProgress) === true) {
                $onProgress($processedBookingsCount, $total);
            }
        }

        return $processedBookingsCount;
    }

    public function getFailedBookings(): array
    {
        return $this->failedBookings;
    }

    private function throttle(): void
    {
        $minInterval = 0.5;
        $now = microtime(true);
        $elapsed = $now - $this->lastRequestTime;

        if ($elapsed < $minInterval) {
            usleep((int)(($minInterval - $elapsed) * 1000000));
        }

        $this->lastRequestTime = microtime(true);
    }
}
