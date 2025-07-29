<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PmsAPIService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('pms-api.base_uri'), '/') . '/';
    }

    /**
     * @throws ConnectionException
     */
    public function getBookings(?string $updatedSince = null): array
    {
        $uri = 'bookings';

        if ($updatedSince) {
            $uri .= '?updated_at.gt=' . urlencode($updatedSince);
        }

        return $this->get($uri);
    }

    /**
     * @throws ConnectionException
     */
    public function getBooking(int $bookingId): array
    {
        return $this->get("bookings/{$bookingId}");
    }

    /**
     * @throws ConnectionException
     */
    public function getRoom(int $roomId): array
    {
        return $this->get("rooms/{$roomId}");
    }

    /**
     * @throws ConnectionException
     */
    public function getRoomType(int $roomTypeId): array
    {
        return $this->get("room-types/{$roomTypeId}");
    }

    /**
     * @throws ConnectionException
     */
    public function getGuest(int $guestId): array
    {
        return $this->get("guests/{$guestId}");
    }

    public function getRooms(?string $since = null): array
    {
        return $this->get('rooms' . ($since ? '?updated_at.gt=' . urlencode($since) : ''));
    }

    public function getRoomTypes(?string $since = null): array
    {
        return $this->get('room-types' . ($since ? '?updated_at.gt=' . urlencode($since) : ''));
    }

    public function getGuests(?string $since = null): array
    {
        return $this->get('guests' . ($since ? '?updated_at.gt=' . urlencode($since) : ''));
    }

    /**
     * @throws ConnectionException
     */
    protected function get(string $uri): array
    {
        $url = $this->baseUrl . ltrim($uri, '/');

        $response = Http::timeout(10)->get($url);

        if (!$response->successful()) {
            Log::error("Failed API call to {$url}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException("Failed API call to {$url}, status: {$response->status()}");
        }

        $json = $response->json();

        if (!is_array($json)) {
            throw new RuntimeException("Invalid JSON from {$url}");
        }

        return $json;
    }
}
