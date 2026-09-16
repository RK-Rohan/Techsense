<?php

namespace Tests\Feature;

use App\MushakBook;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MushakBookStorageTest extends TestCase
{
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
