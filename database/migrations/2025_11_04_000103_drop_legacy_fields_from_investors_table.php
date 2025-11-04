<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $columns = [
                'invest_amount',
                'received_date',
                'invoice_no',
                'txn_ref',
                'received_account_id',
                'return_amount',
                'return_date',
                'return_account_id',
                'remarks',
                'loan_duration',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('investors', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            // Best-effort rollback: re-add columns as nullable
            if (!Schema::hasColumn('investors', 'invest_amount')) {
                $table->decimal('invest_amount', 22, 4)->nullable();
            }
            if (!Schema::hasColumn('investors', 'received_date')) {
                $table->date('received_date')->nullable();
            }
            if (!Schema::hasColumn('investors', 'invoice_no')) {
                $table->string('invoice_no')->nullable();
            }
            if (!Schema::hasColumn('investors', 'txn_ref')) {
                $table->string('txn_ref')->nullable();
            }
            if (!Schema::hasColumn('investors', 'received_account_id')) {
                $table->unsignedBigInteger('received_account_id')->nullable();
            }
            if (!Schema::hasColumn('investors', 'return_amount')) {
                $table->decimal('return_amount', 22, 4)->nullable();
            }
            if (!Schema::hasColumn('investors', 'return_date')) {
                $table->date('return_date')->nullable();
            }
            if (!Schema::hasColumn('investors', 'return_account_id')) {
                $table->unsignedBigInteger('return_account_id')->nullable();
            }
            if (!Schema::hasColumn('investors', 'remarks')) {
                $table->text('remarks')->nullable();
            }
            if (!Schema::hasColumn('investors', 'loan_duration')) {
                $table->string('loan_duration')->nullable();
            }
        });
    }
};
