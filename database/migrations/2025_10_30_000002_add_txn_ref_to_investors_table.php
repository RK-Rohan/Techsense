<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->string('txn_ref')->nullable()->after('invoice_no');
        });
    }

    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            if (Schema::hasColumn('investors', 'txn_ref')) {
                $table->dropColumn('txn_ref');
            }
        });
    }
};
