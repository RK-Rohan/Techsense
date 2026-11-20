<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactPersonsTable extends Migration
{
    public function up()
    {
        Schema::create('contact_persons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('contact_id');
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->index(['contact_id', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_persons');
    }
}
