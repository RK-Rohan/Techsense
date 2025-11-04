<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'custom_field_5')) {
                $table->string('custom_field_5')->nullable()->after('custom_field_4');
            }
            if (!Schema::hasColumn('transactions', 'custom_field_6')) {
                $table->string('custom_field_6')->nullable()->after('custom_field_5');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'custom_field_6')) {
                $table->dropColumn('custom_field_6');
            }
            if (Schema::hasColumn('transactions', 'custom_field_5')) {
                $table->dropColumn('custom_field_5');
            }
        });
    }
};
