<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccrualChartFields extends Migration
{
    public function up()
    {
        Schema::table('accounting_account_types', function (Blueprint $table) {
            $table->string('chart_code')->nullable();
        });

        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->boolean('is_control_ledger')->default(false);
        });
    }

    public function down()
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->dropColumn('is_control_ledger');
        });

        Schema::table('accounting_account_types', function (Blueprint $table) {
            $table->dropColumn('chart_code');
        });
    }
}
