<?php

namespace Tests\Unit;

use App\Modules\BloodBank\Services\BloodBankService;
use PHPUnit\Framework\TestCase;

class BloodBankServiceTest extends TestCase
{
    public function test_stock_status_out_when_no_units(): void
    {
        $this->assertSame('out', (new BloodBankService)->stockStatus(0, 2));
        $this->assertSame('out', (new BloodBankService)->stockStatus(-1, 2));
    }

    public function test_stock_status_low_when_at_or_below_threshold(): void
    {
        $this->assertSame('low', (new BloodBankService)->stockStatus(1, 2));
        $this->assertSame('low', (new BloodBankService)->stockStatus(2, 2));
    }

    public function test_stock_status_available_when_above_threshold(): void
    {
        $this->assertSame('available', (new BloodBankService)->stockStatus(3, 2));
    }
}
