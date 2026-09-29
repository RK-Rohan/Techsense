<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links a purchase to each investor funding it, with the amount each put in.
     */
    public function up()
    {
        Schema::create('purchase_investors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id')->unsigned();
            $table->integer('transaction_id')->unsigned();
            $table->unsignedBigInteger('investor_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->index(['business_id', 'investor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_investors');
    }
};
