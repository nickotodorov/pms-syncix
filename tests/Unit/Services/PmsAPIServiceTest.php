<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\PmsAPIService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PmsAPIServiceTest extends TestCase
{
    protected PmsAPIService $pms;

    protected function setUp(): void
    {
        parent::setUp();

        config(['pms-api.base_uri' => 'https://api.pms.test/api/']);
        $this->pms = new PmsAPIService();
    }

    /**
     * @throws ConnectionException
     */
    public function test_it_fetches_bookings_successfully(): void
    {
        Http::fake([
            'https://api.pms.test/api/bookings*' => Http::response(['data' => [['id' => 1]]]),
        ]);

        $result = $this->pms->getBookings();

        $this->assertIsArray($result);
        $this->assertEquals([['id' => 1]], $result['data']);
    }

    /**
     * @throws ConnectionException
     */
    public function test_it_appends_updated_at_filter(): void
    {
        Http::fake([
            'https://api.pms.test/api/bookings?updated_at.gt=2025-07-20' => Http::response(['data' => []]),
        ]);

        $result = $this->pms->getBookings('2025-07-20');

        $this->assertIsArray($result);
    }

    /**
     * @throws ConnectionException
     */
    public function test_it_throws_exception_on_non_successful_response(): void
    {
        Http::fake([
            'https://api.pms.test/api/bookings/123' => Http::response('Not found', 404),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed API call to https://api.pms.test/api/bookings/123, status: 404');

        $this->pms->getBooking(123);
    }

    /**
     * @throws ConnectionException
     */
    public function test_it_throws_exception_on_invalid_json(): void
    {
        Http::fake([
            'https://api.pms.test/api/rooms/55' => Http::response('Invalid JSON'),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON from https://api.pms.test/api/rooms/55');

        $this->pms->getRoom(55);
    }

    /**
     * @throws ConnectionException
     */
    public function test_it_fetches_guests_rooms_roomtypes(): void
    {
        Http::fake([
            'https://api.pms.test/api/guests/5' => Http::response(['id' => 5]),
            'https://api.pms.test/api/rooms/10' => Http::response(['id' => 10]),
            'https://api.pms.test/api/room-types/3' => Http::response(['id' => 3]),
        ]);

        $guest = $this->pms->getGuest(5);
        $room = $this->pms->getRoom(10);
        $roomType = $this->pms->getRoomType(3);

        $this->assertEquals(['id' => 5], $guest);
        $this->assertEquals(['id' => 10], $room);
        $this->assertEquals(['id' => 3], $roomType);
    }
}
