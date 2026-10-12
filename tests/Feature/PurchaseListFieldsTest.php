<?php

namespace Tests\Feature;

use App\Utils\TransactionUtil;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseListFieldsTest extends TestCase
{
    public function test_shipping_lines_and_linked_invoice_totals_preserve_payment_sums()
    {
        config(['database.connections.purchase_list_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('purchase_list_test');
        $connection = DB::connection();
        $connection->getPdo()->sqliteCreateFunction('CONCAT', fn (...$parts) => implode('', $parts));
        $connection->getPdo()->sqliteCreateFunction('FIND_IN_SET', function ($id, $list) {
            return in_array((string) $id, explode(',', (string) $list), true) ? 1 : 0;
        });
        // Translate only MySQL's ordered aggregate syntax for the isolated SQLite fixture.
        $connection->setQueryGrammar(new class extends SQLiteGrammar {
            public function compileSelect(\Illuminate\Database\Query\Builder $query)
            {
                return preg_replace("/GROUP_CONCAT\((.*?) ORDER BY .*? SEPARATOR ('[^']*')\)/", 'GROUP_CONCAT($1, $2)', parent::compileSelect($query));
            }
        });

        DB::statement('CREATE TABLE contacts (id INTEGER, name TEXT, supplier_business_name TEXT)');
        DB::statement('CREATE TABLE business_locations (id INTEGER, name TEXT)');
        DB::statement('CREATE TABLE users (id INTEGER, surname TEXT, first_name TEXT, last_name TEXT)');
        DB::statement('CREATE TABLE transaction_payments (transaction_id INTEGER, amount REAL)');
        DB::statement('CREATE TABLE shipping_lines (id INTEGER, business_id INTEGER, name TEXT)');
        DB::statement('CREATE TABLE investors (id INTEGER, name TEXT)');
        DB::statement('CREATE TABLE purchase_investors (id INTEGER, transaction_id INTEGER, investor_id INTEGER, amount REAL)');
        DB::statement('CREATE TABLE transactions (id INTEGER, business_id INTEGER, type TEXT, contact_id INTEGER, location_id INTEGER, created_by INTEGER, return_parent_id INTEGER, document TEXT, transaction_date TEXT, ref_no TEXT, custom_field_1 TEXT, custom_field_2 TEXT, custom_field_3 TEXT, supplier_note TEXT, status TEXT, payment_status TEXT, final_total REAL, pay_term_number INTEGER, pay_term_type TEXT, tracking_number TEXT, shipping_status TEXT, shipping_line_ids TEXT, shipping_line_id INTEGER, investor_id INTEGER, invoice_no TEXT)');
        DB::table('business_locations')->insert(['id' => 1, 'name' => 'Office']);
        DB::table('shipping_lines')->insert([
            ['id' => 1, 'business_id' => 1, 'name' => 'Hand Carry'],
            ['id' => 2, 'business_id' => 1, 'name' => 'Courier'],
            ['id' => 3, 'business_id' => 2, 'name' => 'Other Business'],
        ]);
        foreach (['["1"]', '[1, 2]', null, '[]', '["3"]'] as $index => $ids) {
            DB::table('transactions')->insert([
                'id' => $index + 1, 'business_id' => 1, 'type' => 'purchase',
                'location_id' => 1, 'shipping_line_ids' => $ids, 'shipping_line_id' => 1,
                'custom_field_3' => $index === 4 ? '' : '2026/3429', 'final_total' => 23000,
            ]);
        }
        DB::table('transactions')->insert([
            ['id' => 10, 'business_id' => 1, 'type' => 'sell', 'invoice_no' => '2026/3429', 'final_total' => 119780, 'status' => 'draft'],
            ['id' => 11, 'business_id' => 2, 'type' => 'sell', 'invoice_no' => '2026/3429', 'final_total' => 999, 'status' => 'final'],
        ]);
        DB::table('transaction_payments')->insert([
            ['transaction_id' => 1, 'amount' => 100],
            ['transaction_id' => 1, 'amount' => 200],
            ['transaction_id' => 2, 'amount' => 100],
            ['transaction_id' => 2, 'amount' => 200],
        ]);

        $rows = $this->app->make(TransactionUtil::class)->getListPurchases(1)->get()->keyBy('id');
        $this->assertCount(5, $rows);
        $this->assertSame('Hand Carry', $rows[1]->shipping_line);
        $this->assertEqualsCanonicalizing(['Hand Carry', 'Courier'], explode(', ', $rows[2]->shipping_line));
        $this->assertSame('Hand Carry', $rows[3]->shipping_line);
        $this->assertSame('Hand Carry', $rows[4]->shipping_line);
        $this->assertNull($rows[5]->shipping_line);
        $this->assertNull($rows[5]->client_po_amount);
        foreach ([1, 2, 3, 4] as $id) {
            $this->assertEquals(119780, $rows[$id]->client_po_amount);
        }
        $this->assertEquals(300, $rows[1]->amount_paid);
        $this->assertEquals(300, $rows[2]->amount_paid);
    }
}
