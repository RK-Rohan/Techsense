<?php

namespace Tests\Feature;

use App\Utils\SalePurchaseLink;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalePurchaseLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session()->put('currency', ['thousand_separator' => ',', 'decimal_separator' => '.']);
        config(['database.connections.sale_link_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('sale_link_test');
        DB::statement('CREATE TABLE transactions (id INTEGER PRIMARY KEY, business_id INTEGER, type TEXT, contact_id INTEGER, location_id INTEGER, created_by INTEGER, ref_no TEXT, custom_field_1 TEXT, custom_field_2 TEXT, custom_field_3 TEXT, custom_field_4 TEXT, custom_field_5 TEXT, custom_field_6 TEXT, supplier_note TEXT, investor_id INTEGER, final_total REAL)');
        DB::statement('CREATE TABLE contacts (id INTEGER PRIMARY KEY, name TEXT, supplier_business_name TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE investors (id INTEGER PRIMARY KEY, name TEXT)');
        DB::statement('CREATE TABLE purchase_investors (id INTEGER PRIMARY KEY, transaction_id INTEGER, investor_id INTEGER, amount REAL)');
        DB::table('contacts')->insert(['id' => 1, 'name' => 'Supmea']);
        DB::table('investors')->insert([['id' => 1, 'name' => 'Tamim Khan'], ['id' => 2, 'name' => 'Second Investor']]);
        foreach ([1, 2, 3, 4, 5] as $id) {
            DB::table('transactions')->insert([
                'id' => $id, 'business_id' => $id === 2 ? 2 : 1,
                'type' => $id === 3 ? 'sell' : 'purchase', 'contact_id' => 1,
                'location_id' => $id === 4 ? 2 : 1, 'created_by' => $id === 5 ? 99 : 12,
                'ref_no' => 'PO2026/'.$id, 'supplier_note' => 'Supmea (3400 RMB)', 'investor_id' => 1, 'final_total' => 65700.50,
            ]);
        }
        DB::table('purchase_investors')->insert([
            ['id' => 1, 'transaction_id' => 1, 'investor_id' => 1, 'amount' => 65700],
            ['id' => 2, 'transaction_id' => 1, 'investor_id' => 2, 'amount' => 1000],
        ]);
        $this->userPermissions(['purchase.view']);
    }

    private function userPermissions(array $permissions)
    {
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->id = 12;
        $user->shouldReceive('can')->andReturnUsing(fn ($permission) => in_array($permission, $permissions));
        $user->shouldReceive('permitted_locations')->andReturn([1]);
        auth()->guard()->setUser($user);
    }

    public function test_purchase_fields_override_manual_input_and_use_purchase_total()
    {
        $fields = app(SalePurchaseLink::class)->apply(1, [
            'linked_purchase_id' => '1', 'custom_field_4' => 'Forged supplier',
            'custom_field_5' => 'Forged investor', 'custom_field_6' => 'Wrong amount',
        ]);
        $this->assertSame('Supmea', $fields['custom_field_4']);
        $this->assertSame('Tamim Khan, Second Investor', $fields['custom_field_5']);
        $this->assertSame('65,700.50', $fields['custom_field_6']);
        $this->assertSame('1', $fields['linked_purchase_id']);
    }

    public function test_options_are_scoped_by_business_type_and_permitted_location()
    {
        $ids = app(SalePurchaseLink::class)->query(1)->pluck('id')->all();
        $this->assertEqualsCanonicalizing([1, 5], $ids);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(SalePurchaseLink::class)->apply(1, ['linked_purchase_id' => 2]);
    }

    public function test_own_purchase_permission_excludes_other_creators()
    {
        $this->userPermissions(['view_own_purchase']);
        $this->assertSame([1], app(SalePurchaseLink::class)->query(1)->pluck('id')->all());
    }

    public function test_no_purchase_permission_is_rejected()
    {
        $this->userPermissions([]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(SalePurchaseLink::class)->apply(1, ['linked_purchase_id' => 1]);
    }

    public function test_unlinked_sales_preserve_manual_fields_and_legacy_investor_is_supported()
    {
        $link = app(SalePurchaseLink::class);
        $manual = ['custom_field_4' => 'Manual supplier'];
        $this->assertSame($manual, $link->apply(1, $manual));
        $cleared = $link->apply(1, $manual + ['linked_purchase_id' => '']);
        $this->assertNull($cleared['linked_purchase_id']);
        $this->assertSame('Manual supplier', $cleared['custom_field_4']);
        $this->assertSame('Tamim Khan', $link->apply(1, ['linked_purchase_id' => 5])['custom_field_5']);
    }

    public function test_search_endpoint_returns_autofill_fields_and_reopens_selected_purchase()
    {
        $this->userPermissions(['purchase.view', 'direct_sell.access']);
        $session = app('session.store');
        $session->put('user.business_id', 1);
        $controller = app(\App\Http\Controllers\SellController::class);
        foreach ([['q' => 'Supmea'], ['id' => 1]] as $params) {
            $request = \Illuminate\Http\Request::create('/sells/purchase-options', 'GET', $params);
            $request->setLaravelSession($session);
            $data = $controller->purchaseOptions($request)->getData(true);
            $this->assertSame(1, $data['results'][array_key_exists('id', $params) ? 0 : 1]['id']);
            $this->assertSame('65,700.50', $data['results'][0]['fields']['custom_field_6']);
            $this->assertFalse($data['pagination']['more']);
        }
        $request = \Illuminate\Http\Request::create('/sells/purchase-options', 'GET', ['id' => 2]);
        $request->setLaravelSession($session);
        $this->assertSame([], $controller->purchaseOptions($request)->getData(true)['results']);
        $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create('/sells/purchase-options'));
        $this->assertSame('purchaseOptions', $route->getActionMethod());
    }

    public function test_sale_update_keeps_the_purchase_link_and_copies_values_into_list_fields()
    {
        session()->put('business', (object) ['enable_rp' => 0]);
        $sale = \Mockery::mock(\App\Transaction::class)->makePartial();
        $sale->forceFill(['id' => 10, 'status' => 'final', 'invoice_no' => '2026/3524']);
        $sale->shouldReceive('update')->once()->andReturn(true);
        app(\App\Utils\TransactionUtil::class)->updateSellTransaction($sale, 1, [
            'linked_purchase_id' => 1, 'status' => 'final', 'final_total' => 100,
            'pay_term_number' => 1, 'pay_term_type' => 'days', 'contact_id' => 1,
            'customer_group_id' => null, 'tax_rate_id' => null, 'discount_type' => 'fixed',
            'discount_amount' => 0, 'commission_agent' => null,
            'custom_field_4' => 'Manual value must be replaced',
        ], ['total_before_tax' => 100, 'tax' => 0], 12, false);
        $this->assertSame(1, $sale->linked_purchase_id);
        $this->assertSame('Supmea', $sale->custom_field_4);
        $this->assertSame('Tamim Khan, Second Investor', $sale->custom_field_5);
        $this->assertSame('65,700.50', $sale->custom_field_6);
    }
}
