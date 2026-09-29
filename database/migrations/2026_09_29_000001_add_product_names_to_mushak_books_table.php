<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('mushak_books', function (Blueprint $table) {
            $table->text('product_names')->nullable()->after('seller_bin');
        });
    }

    public function down()
    {
        Schema::table('mushak_books', function (Blueprint $table) {
            $table->dropColumn('product_names');
        });
    }
};
