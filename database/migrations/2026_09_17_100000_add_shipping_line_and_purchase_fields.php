<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the shipping line lookup and the extra purchase fields captured on
     * the Add Purchase screen: tracking number, shipping line and investor.
     */
    public function up()
    {
        if (! Schema::hasTable('shipping_lines')) {
            Schema::create('shipping_lines', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('business_id')->unsigned();
                $table->string('name');
                $table->integer('created_by')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('business_id');
            });
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('shipping_status');
            }
            if (! Schema::hasColumn('transactions', 'shipping_line_id')) {
                $table->integer('shipping_line_id')->unsigned()->nullable()->after('tracking_number');
            }
            if (! Schema::hasColumn('transactions', 'investor_id')) {
                $table->integer('investor_id')->unsigned()->nullable()->after('shipping_line_id');
            }
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['tracking_number', 'shipping_line_id', 'investor_id'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('shipping_lines');
    }
};
