<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $this->renameLabel('Supplier Amount', 'Purchase Amount');
    }

    public function down()
    {
        $this->renameLabel('Purchase Amount', 'Supplier Amount');
    }

    private function renameLabel($from, $to)
    {
        DB::table('business')->select('id', 'custom_labels')->orderBy('id')->chunkById(100, function ($businesses) use ($from, $to) {
            foreach ($businesses as $business) {
                $labels = json_decode($business->custom_labels ?? '', true);
                if (strcasecmp(trim($labels['sell']['custom_field_6'] ?? ''), $from) === 0) {
                    $labels['sell']['custom_field_6'] = $to;
                    DB::table('business')->where('id', $business->id)->update(['custom_labels' => json_encode($labels)]);
                }
            }
        });
    }
};
