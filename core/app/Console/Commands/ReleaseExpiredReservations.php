<?php

namespace App\Console\Commands;

use App\Services\InventoryReservationService;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'inventory:release-expired';

    protected $description = 'Mark expired soft inventory reservations and release held stock';

    public function handle(InventoryReservationService $reservations): int
    {
        $count = $reservations->expireDueReservations();
        $this->info("Expired {$count} reservation(s).");

        return self::SUCCESS;
    }
}
