<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            if (!Schema::hasColumn('investments', 'return_amount')) {
                $table->decimal('return_amount', 22, 4)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('investments', 'return_date')) {
                $table->date('return_date')->nullable()->after('return_amount');
            }
            if (!Schema::hasColumn('investments', 'return_account_id')) {
                $table->unsignedBigInteger('return_account_id')->nullable()->after('received_account_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            if (Schema::hasColumn('investments', 'return_account_id')) {
                $table->dropColumn('return_account_id');
            }
            if (Schema::hasColumn('investments', 'return_date')) {
                $table->dropColumn('return_date');
            }
            if (Schema::hasColumn('investments', 'return_amount')) {
                $table->dropColumn('return_amount');
            }
        });
    }
};
