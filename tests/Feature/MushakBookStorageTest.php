<?php

namespace Tests\Feature;

use App\MushakBook;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MushakBookStorageTest extends TestCase
{
    public function test_generation_uses_source_rows_and_edits_only_the_saved_snapshot()
    {
        DB::beginTransaction();
        try {
            foreach (['6-1', '6-2'] as $type) {
                $source = ['date' => '2026-09-16 12:30:00', 'challan_date' => '2026-09-16 12:30:00',
                    'description' => 'Source item', 'quantity' => 2, 'value' => 100, 'taxable_value' => 200, 'vat' => 15];
                $controller = \Mockery::mock(\App\Http\Controllers\MushakBookController::class, [app(\App\Utils\Util::class)])
                    ->makePartial()->shouldAllowMockingProtectedMethods();
                $controller->shouldReceive($type === '6-1' ? 'buildPurchaseBook' : 'buildSalesBook')
                    ->once()->with(4294967294, \Mockery::on(function ($request) {
                        return $request->start_date === '2026-09-01' && $request->end_date === '2026-09-30';
                    }))->andReturn(['rows' => collect([$source])]);
                $book = new MushakBook(['business_id' => 4294967294, 'created_by' => 1, 'type' => $type]);
                $input = ['document_no' => 'GENERATED', 'issued_at' => '2026-09-17',
                    'start_date' => '2026-09-01', 'end_date' => '2026-09-30', 'registered_name' => 'Test business',
                    'rows_json' => json_encode([['description' => 'Untrusted custom row']])];
                $save = new \ReflectionMethod(\App\Http\Controllers\MushakBookController::class, 'saveBook');
                $save->setAccessible(true);
                $save->invoke($controller, new \Illuminate\Http\Request($input), $type, $book);
                $book->refresh();
                $this->assertSame('Source item', $book->rows[0]['description']);
                $this->assertSame('2026-09-16', $book->rows[0]['date']);
                $this->assertEquals($type === '6-1' ? 100 : 200, $book->total_amount);
                $rows = $book->rows;
                $rows[0]['description'] = 'Report correction';
                $input['rows_json'] = json_encode($rows);
                $save->invoke($controller, new \Illuminate\Http\Request($input), $type, $book);
                $this->assertSame('Report correction', $book->fresh()->rows[0]['description']);
                $this->assertSame('2026-09-01', $book->fresh()->start_date);
                $input['rows_json'] = json_encode(array_merge($rows, $rows));
                try {
                    $save->invoke($controller, new \Illuminate\Http\Request($input), $type, $book);
                    $this->fail('Adding report rows must be rejected.');
                } catch (\Illuminate\Validation\ValidationException $e) {
                    $this->assertArrayHasKey('rows', $e->errors());
                }
                $this->assertCount(1, $book->fresh()->rows);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function test_books_keep_separate_snapshots_and_render_their_saved_rows()
    {
        DB::beginTransaction();
        try {
            $ids = [];
            foreach (['6-1', '6-2'] as $type) {
                $rows = MushakBook::calculateRows($type, [[
                    'date' => '2026-09-16', 'challan_date' => '2026-09-16',
                    'description' => 'Custom item '.$type, 'quantity' => 2,
                    'value' => 100, 'taxable_value' => 200,
                ]]);
                $book = MushakBook::create([
                    'business_id' => 4294967294, 'created_by' => 1, 'type' => $type,
                    'document_no' => 'TEST', 'issued_at' => '2026-09-16',
                    'start_date' => '2026-09-01', 'end_date' => '2026-09-30',
                    'registered_name' => 'Custom registered person', 'rows' => $rows,
                ]);
                $ids[$type] = $book->id;
                $this->assertEquals($rows, $book->fresh()->rows);
                $data = [
                    'business' => (object) ['name' => $book->registered_name],
                    'government_seal' => null, 'seller_bin' => '', 'seller_address' => '',
                    'start_date' => $book->start_date, 'end_date' => $book->end_date,
                    'rows' => collect($book->fresh()->rows),
                ];
                $html = view('mushak.pdf.mushak_'.str_replace('-', '_', $type), $data)->render();
                $this->assertStringContainsString('Custom item '.$type, $html);
                $this->assertStringContainsString('Custom registered person', $html);
                $pdf = \PDF::loadHTML($html)->setPaper('a4', 'landscape')->output();
                $this->assertStringStartsWith('%PDF-', $pdf);
            }
            $this->assertNull(MushakBook::where('type', '6-1')->find($ids['6-2']));
            $this->assertNull(MushakBook::where('business_id', 4294967293)->find($ids['6-1']));
            MushakBook::findOrFail($ids['6-1'])->delete();
            $this->assertNull(MushakBook::find($ids['6-1']));
            $this->assertNotNull(MushakBook::find($ids['6-2']));
        } finally {
            DB::rollBack();
        }
    }
}
