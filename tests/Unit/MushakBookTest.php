<?php

namespace Tests\Unit;

use App\MushakBook;
use PHPUnit\Framework\TestCase;

class MushakBookTest extends TestCase
{
    public function test_purchase_balances_and_custom_details_are_preserved()
    {
        $rows = MushakBook::calculateRows('6-1', [[
            'opening_qty' => '10', 'opening_value' => '100', 'quantity' => '5', 'value' => '60',
            'used_qty' => '2', 'used_value' => '20', 'supplier_name' => 'Custom supplier',
            'total_value' => 999, 'buyer_name' => 'Must not carry over',
        ]]);
        $this->assertSame(15.0, $rows[0]['total_qty']);
        $this->assertSame(160.0, $rows[0]['total_value']);
        $this->assertSame(13.0, $rows[0]['closing_qty']);
        $this->assertSame(140.0, $rows[0]['closing_value']);
        $this->assertSame('Custom supplier', $rows[0]['supplier_name']);
        $this->assertArrayNotHasKey('buyer_name', $rows[0]);
    }

    public function test_sales_calculations_use_production_and_preserve_each_opening_balance()
    {
        $rows = MushakBook::calculateRows('6-2', [3 => [
            'opening_qty' => 10, 'opening_value' => 100, 'produced_qty' => 5, 'produced_value' => 60,
            'quantity' => 4, 'taxable_value' => 50, 'buyer_name' => 'Custom buyer',
        ], 7 => ['opening_qty' => 30, 'opening_value' => 300]]);
        $this->assertSame(15.0, $rows[0]['total_qty']);
        $this->assertSame(11.0, $rows[0]['closing_qty']);
        $this->assertSame(110.0, $rows[0]['closing_value']);
        $this->assertSame(30.0, $rows[1]['opening_qty']);
        $this->assertSame(2, $rows[1]['serial']);
        $this->assertSame('Custom buyer', $rows[0]['buyer_name']);
        $this->assertArrayNotHasKey('supplier_name', $rows[0]);
    }
}
