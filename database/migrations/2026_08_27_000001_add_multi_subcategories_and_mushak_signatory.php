<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('expense_sub_category_ids')->nullable()->after('expense_sub_category_id');
        });

        Schema::table('mushak_invoices', function (Blueprint $table) {
            $table->string('authorised_person')->nullable()->after('vehicle_details');
            $table->string('designation')->nullable()->after('authorised_person');
        });
    }

    public function down()
    {
        Schema::table('mushak_invoices', function (Blueprint $table) {
            $table->dropColumn(['authorised_person', 'designation']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('expense_sub_category_ids');
        });
    }
};
