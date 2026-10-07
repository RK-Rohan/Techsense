<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('transactions', 'shipping_line_ids')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->text('shipping_line_ids')->nullable()->after('shipping_line_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('transactions', 'shipping_line_ids')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('shipping_line_ids');
            });
        }
    }
};
