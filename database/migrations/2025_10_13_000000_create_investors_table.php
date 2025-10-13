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
        Schema::create('investors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('invest_amount', 15, 2)->default(0);
            $table->string('invoice_no')->nullable();
            $table->integer('received_account_id')->nullable();
            $table->date('received_date')->nullable();
            $table->decimal('return_amount', 15, 2)->default(0);
            $table->integer('return_account_id')->nullable();
            $table->date('return_date')->nullable();
            $table->text('remarks')->nullable();
            $table->string('loan_duration')->nullable();
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
        Schema::dropIfExists('investors');
    }
};
