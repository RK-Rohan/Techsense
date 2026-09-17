<?php
namespace Tests\Feature;

use App\ExpenseCategory;
use App\Utils\ExpenseItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExpenseItemsTest extends TestCase
{
    public function test_items_determine_total_and_reject_invalid_categories_and_amounts()
    {
        DB::beginTransaction();
        try {
            $business = \App\Business::firstOrFail();
            $parent = ExpenseCategory::create(['business_id'=>$business->id, 'name'=>'Test parent']);
            $child = ExpenseCategory::create(['business_id'=>$business->id, 'parent_id'=>$parent->id, 'name'=>'Test rent']);
            $second = ExpenseCategory::create(['business_id'=>$business->id, 'parent_id'=>$parent->id, 'name'=>'Test electricity']);
            $items = [['subcategory_id'=>$child->id, 'amount'=>30000, 'note'=>'Office rent'], ['subcategory_id'=>$second->id, 'amount'=>8500, 'note'=>'Electricity']];
            $request = new Request(['expense_category_id'=>$parent->id, 'final_total'=>1, 'expense_items_json'=>json_encode($items)]);
            $result = ExpenseItems::fromRequest($request, $business->id);
            $this->assertEquals(38500, $result['final_total']);
            $this->assertSame('Office rent', $result['expense_items'][0]['note']);
            $this->assertSame([$child->id, $second->id], $result['expense_sub_category_ids']);
            foreach ([[], [$items[0], $items[0]], [['subcategory_id'=>$parent->id,'amount'=>10]], [['subcategory_id'=>$child->id,'amount'=>-1]]] as $invalid) {
                try {
                    ExpenseItems::fromRequest(new Request(['expense_category_id'=>$parent->id,'expense_items_json'=>json_encode($invalid)]), $business->id);
                    $this->fail('Invalid details must be rejected.');
                } catch (ValidationException $e) {
                    $this->assertNotEmpty($e->errors());
                }
            }
            $user = \App\User::where('business_id', $business->id)->firstOrFail();
            $this->actingAs($user);
            session(['currency'=>['thousand_separator'=>',', 'decimal_separator'=>'.']]);
            $request->merge(['location_id'=>\App\BusinessLocation::where('business_id', $business->id)->value('id'),
                'transaction_date'=>'2026-09-17 12:00:00', 'payment'=>[
                    ['method'=>'cash','amount'=>30000,'note'=>'First payment'],
                    ['method'=>'bank_transfer','amount'=>8500,'note'=>'Second payment'],
                ]]);
            $util = app(\App\Utils\TransactionUtil::class);
            $saved = $util->createExpense($request, $business->id, $user->id, false);
            $this->assertCount(2, $saved->fresh()->expense_items);
            $this->assertCount(2, $saved->fresh()->payment_lines);
            $this->assertEquals(38500, $saved->fresh()->final_total);
            $this->assertSame('paid', $saved->fresh()->payment_status);
            $items[0]['note'] = 'Edited rent note';
            $request->merge(['expense_items_json'=>json_encode($items)]);
            $util->updateExpense($request, $saved->id, $business->id, false);
            $this->assertSame('Edited rent note', $saved->fresh()->expense_items[0]['note']);
            $this->assertCount(2, $saved->fresh()->payment_lines);
            $recurring = $util->createRecurringExpense($saved->fresh());
            $this->assertEquals($saved->fresh()->expense_items, $recurring->expense_items);
            try {
                $request->merge(['payment'=>[['method'=>'cash','amount'=>40000]]]);
                $util->createExpense($request, $business->id, $user->id, false);
                $this->fail('Overpayment must be rejected.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('payment', $e->errors());
            }
            $this->assertSame([], ExpenseItems::fromRequest(new Request(['final_total'=>100]), $business->id));
            $transaction = new \App\Transaction(['expense_items'=>$result['expense_items']]);
            $this->assertEquals($result['expense_items'], $transaction->expense_items);
        } finally {
            DB::rollBack();
        }
    }
}
