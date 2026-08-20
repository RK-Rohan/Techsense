<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Holds the Mushak 6.3 specific overrides for a sale.
     *
     * Every value is a snapshot taken when the Mushak is generated, so editing
     * a VAT document never rewrites the sale, the contact master or the
     * delivery chalan. Anything left blank falls back to the value derived
     * from the transaction at print time.
     */
    public function up()
    {
        Schema::create('mushak_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('transaction_id');

            $table->string('mushak_invoice_no')->nullable();
            $table->dateTime('issued_at')->nullable();

            $table->string('purchaser_name')->nullable();
            $table->string('purchaser_bin')->nullable();
            $table->text('purchaser_address')->nullable();
            $table->text('destination_address')->nullable();
            $table->string('vehicle_details')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');

            $table->index(['business_id', 'transaction_id']);
            $table->index('mushak_invoice_no');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mushak_invoices');
    }
};
