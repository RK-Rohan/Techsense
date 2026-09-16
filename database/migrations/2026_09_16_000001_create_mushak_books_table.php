<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('mushak_books', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->string('type', 3);
            $table->unsignedInteger('created_by');
            $table->string('document_no');
            $table->date('issued_at');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('registered_name');
            $table->text('seller_address')->nullable();
            $table->string('seller_bin')->nullable();
            $table->json('rows');
            $table->decimal('total_amount', 22, 4)->default(0);
            $table->decimal('tax_amount', 22, 4)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['business_id', 'type', 'issued_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mushak_books');
    }
};
