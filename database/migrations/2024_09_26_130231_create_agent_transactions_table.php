<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('sales_id');
            $table->unsignedBigInteger('supplier_id');
            $table->string('invoice_no');
            $table->decimal('cnf_qty', 8, 2);
            $table->decimal('cnf_rate', 8, 2);
            $table->decimal('cnf_amount', 10, 2);
            $table->decimal('supplier_amount', 10, 2)->nullable();
            $table->decimal('number_of_cartons', 8, 2)->nullable();
            $table->string('tracking_no')->nullable();
            $table->enum('payment_status', ['paid', 'due'])->default('due');
            $table->enum('shipping_status', ['Processing', 'Shipped', 'Received'])->default('Processing');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_transactions');
    }
};
