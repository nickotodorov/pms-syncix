<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BookingSyncService;
use Illuminate\Console\Command;
use App\Helpers\Date;
use Illuminate\Http\Client\ConnectionException;

class SyncBookings extends Command
{
    protected $signature = 'pms:sync-bookings {--since=}';

    protected $description = 'Sync bookings data from PMS API service';

    private const string SUCCESS_MSG = 'Bookings synced successfully. Total processed: %s';

    private const string DATE_HINT_MSG = 'Enter a valid datetime (YYYY-MM-DD or ISO format) to sync since, or leave empty to' .
    ' fetch all';

    private const string INVALID_DATE_MSG = 'The date %s is not a valid format. Please use YYYY-MM-DD or a full ISO 8601' .
    ' format.';

    private const string FAILED_BOOKINGS_MSG = 'Bookings failed: %s';

    private const string START_COMMAND_MSG = 'Start synchronization of the bookings list...';

    public function __construct(
        protected readonly BookingSyncService $syncService,
    )
    {
        parent::__construct();
    }

    /**
     * @throws ConnectionException
     */
    public function handle(): int
    {
        $since = $this->option('since');

        while (!$since || !Date::isValidDate($since)) {
            $since = $this->ask(self::DATE_HINT_MSG);

            if ($since === null || $since === '') {
                break;
            }

            if (Date::isValidDate($since) === false) {
                $this->warn(sprintf(self::INVALID_DATE_MSG, $since));
                $since = null;
            }
        }

        $this->info(self::START_COMMAND_MSG);

        $bookingIds = $this->syncService->pmsApiService->getBookings($since)['data'] ?? [];
        $bar = $this->output->createProgressBar(count($bookingIds));

        $bar->start();

        $count = $this->syncService->syncBookings($bookingIds, $since, function () use ($bar) {
            $bar->advance();
        });
        $bar->finish();

        $this->newLine();

        $failedBookings = $this->syncService->getFailedBookings();
        $failedBookingsCount = count($failedBookings);

        $this->info(sprintf(self::SUCCESS_MSG, ($count - $failedBookingsCount)));

        if ($failedBookingsCount > 0) {
            //here could be implemented a retry mechanism
            $this->newLine();
            $this->info(sprintf(self::FAILED_BOOKINGS_MSG, implode(', ', $failedBookings)));
        }

        return 0;
    }
}
