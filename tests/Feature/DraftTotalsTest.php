<?php

namespace Tests\Feature;

use App\Http\Controllers\SellController;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class DraftTotalsTest extends TestCase
{
    public function test_draft_total_respects_search_and_filters_across_pages_without_counting_items_twice()
    {
        // Use a separate in-memory database, never the configured business database.
        config(['database.connections.draft_totals_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('draft_totals_test');
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', fn (...$parts) => implode('', $parts));

        DB::statement('CREATE TABLE contacts (id INTEGER, name TEXT, mobile TEXT, supplier_business_name TEXT)');
        DB::statement('CREATE TABLE users (id INTEGER, surname TEXT, first_name TEXT, last_name TEXT)');
        DB::statement('CREATE TABLE business_locations (id INTEGER, name TEXT)');
        DB::statement('CREATE TABLE transaction_sell_lines (id INTEGER, transaction_id INTEGER, parent_sell_line_id INTEGER, quantity REAL)');
        DB::statement('CREATE TABLE transactions (id INTEGER, business_id INTEGER, contact_id INTEGER, created_by INTEGER, location_id INTEGER, type TEXT, status TEXT, sub_status TEXT, final_total REAL, transaction_date TEXT, invoice_no TEXT, custom_field_1 TEXT, custom_field_2 TEXT, is_direct_sale INTEGER, is_export INTEGER)');

        DB::table('contacts')->insert([
            ['id' => 1, 'name' => 'Alpha', 'mobile' => '', 'supplier_business_name' => ''],
            ['id' => 2, 'name' => 'Beta', 'mobile' => '', 'supplier_business_name' => ''],
        ]);
        DB::table('business_locations')->insert(['id' => 1, 'name' => 'Office']);
        foreach ([[1, 1, 100, null, 1], [2, 1, 200, null, 1], [3, 2, 400, null, 1], [4, 1, 800, 'quotation', 1], [5, 1, 1600, null, 2]] as [$id, $contact, $amount, $subStatus, $business]) {
            DB::table('transactions')->insert([
                'id' => $id, 'business_id' => $business, 'contact_id' => $contact,
                'created_by' => 1, 'location_id' => 1, 'type' => 'sell', 'status' => 'draft',
                'sub_status' => $subStatus, 'final_total' => $amount,
                'transaction_date' => '2026-09-30 12:00:00', 'invoice_no' => 'D'.$id,
                'is_direct_sale' => 1, 'is_export' => 0,
            ]);
        }
        DB::table('transaction_sell_lines')->insert([
            ['id' => 1, 'transaction_id' => 1, 'quantity' => 1],
            ['id' => 2, 'transaction_id' => 1, 'quantity' => 1],
        ]);

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->andReturn(false);
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $this->actingAs($user);
        $moduleUtil = Mockery::mock(ModuleUtil::class);
        $moduleUtil->shouldReceive('isModuleInstalled')->andReturn(false);
        $this->app->instance(ModuleUtil::class, $moduleUtil);
        session(['user.business_id' => 1, 'business.date_format' => 'd-m-Y',
            'currency' => ['decimal_separator' => '.', 'thousand_separator' => ',']]);

        $columns = [
            ['data' => 'invoice_no', 'name' => 'invoice_no', 'searchable' => 'true'],
            ['data' => 'conatct_name', 'name' => 'conatct_name', 'searchable' => 'true'],
        ];
        foreach ([
            [[], 700, 3, 1],
            [['start' => 1], 700, 3, 1],
            [['customer_id' => 1], 300, 2, 1],
            [['search' => ['value' => 'Alpha']], 300, 2, 1],
            [['search' => ['value' => 'Missing']], 0, 0, 0],
        ] as [$filters, $expectedTotal, $expectedCount, $expectedPageCount]) {
            $request = Request::create('/sells/draft-dt', 'GET', array_replace([
                'is_quotation' => 0, 'draw' => 1, 'start' => 0, 'length' => 1,
                'columns' => $columns,
            ], $filters), [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
            $request->setLaravelSession($this->app['session.store']);
            $this->app->instance('request', $request);
            $this->app->forgetInstance('datatables.request');
            $response = $this->app->make(SellController::class)->getDraftDatables()->getData(true);
            $this->assertArrayNotHasKey('error', $response);
            $this->assertEquals($expectedTotal, $response['total_amount']);
            $this->assertEquals($expectedCount, $response['recordsFiltered']);
            $this->assertCount($expectedPageCount, $response['data']);
        }
    }
}
