<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('investor_id');
            $table->decimal('amount', 22, 4);
            $table->date('received_date')->nullable();
            $table->unsignedBigInteger('received_account_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('txn_ref')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('investor_id')->references('id')->on('investors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
