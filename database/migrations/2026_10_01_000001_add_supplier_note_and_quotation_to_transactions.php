<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the supplier note captured on the purchase form, and remembers the
     * sell draft a purchase was raised from so it is shown again on edit.
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'supplier_note')) {
                $table->text('supplier_note')->nullable()->after('additional_notes');
            }
            if (! Schema::hasColumn('transactions', 'quotation_id')) {
                $table->integer('quotation_id')->unsigned()->nullable()->after('shipping_line_id');
            }
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['supplier_note', 'quotation_id'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
