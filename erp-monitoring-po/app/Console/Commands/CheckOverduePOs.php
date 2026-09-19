<?php

namespace App\Console\Commands;

use App\Support\ErpFlow;
use Illuminate\Console\Command;

class CheckOverduePOs extends Command
{
    protected $signature = 'po:check-overdue';
    protected $description = 'Check and update PO statuses for overdue deliveries (Full/Partial/Delayed)';

    public function handle(): int
    {
        $this->info('Checking PO statuses for overdue deliveries...');

        $count = ErpFlow::refreshAllPoStatuses();

        $this->info("PO status refresh completed. Updated {$count} purchase orders.");

        return Command::SUCCESS;
    }
}