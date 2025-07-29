<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Repositories\BookingRepository;
use App\Repositories\GuestRepository;
use App\Repositories\RoomRepository;
use App\Repositories\RoomTypeRepository;
use App\Services\BookingSyncService;
use App\Services\PmsAPIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\DB;

class BookingSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws ConnectionException
     */
    public function test_sync_bookings_processes_single_booking(): void
    {
        $bookingId = 123;
        $roomId = 10;
        $roomTypeId = 20;
        $guestId = 100;

        $pmsMock = Mockery::mock(PmsAPIService::class);
        $pmsMock->shouldReceive('getBooking')->with($bookingId)->andReturn([
            'id' => $bookingId,
            'room_id' => $roomId,
            'room_type_id' => $roomTypeId,
            'guest_ids' => [$guestId],
            'arrival_date' => '2025-08-01',
            'departure_date' => '2025-08-05',
            'status' => 'confirmed',
            'external_id' => 'ext-123',
            'notes' => 'Test notes',
        ]);

        $pmsMock->shouldReceive('getRoom')->with($roomId)->andReturn(['id' => $roomId]);
        $pmsMock->shouldReceive('getRoomType')->with($roomTypeId)->andReturn(['id' => $roomTypeId]);
        $pmsMock->shouldReceive('getGuest')->with($guestId)->andReturn(['id' => $guestId]);

        $bookingRepo = new BookingRepository();
        $guestRepo = new GuestRepository();
        $roomRepo = new RoomRepository();
        $roomTypeRepo = new RoomTypeRepository();

        $service = new BookingSyncService(
            $pmsMock,
            $bookingRepo,
            $guestRepo,
            $roomRepo,
            $roomTypeRepo
        );
        $this->prepareRecords($roomTypeId, $roomId, $guestId);

        $count = $service->syncBookings([$bookingId]);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'room_id' => $roomId,
            'room_type_id' => $roomTypeId,
        ]);
        $this->assertDatabaseHas('guests', ['id' => $guestId]);
    }

    protected function prepareRecords(int $roomTypeId, int $roomId, int $guestId): void
    {
        DB::table('room_types')->insert([
            'id' => $roomTypeId,
            'name' => 'Test Room Type',
            'description' => 'Test description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('rooms')->insert([
            'id' => $roomId,
            'number' => '101',
            'floor' => 1,
            'room_type_id' => $roomTypeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('guests')->insert([
            'id' => $guestId,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
