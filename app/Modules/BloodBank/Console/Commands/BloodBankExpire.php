<?php

namespace App\Modules\BloodBank\Console\Commands;

use App\Modules\BloodBank\Services\BloodBankService;
use Illuminate\Console\Command;

class BloodBankExpire extends Command
{
    protected $signature = 'bloodbank:expire';

    protected $description = 'Mark overdue blood donations as expired and release their reservations';

    public function handle(BloodBankService $bloodBankService): int
    {
        $expired = $bloodBankService->expireOverdue();

        $this->info(sprintf('Marked %d blood donation(s) as expired.', $expired));

        return self::SUCCESS;
    }
}
