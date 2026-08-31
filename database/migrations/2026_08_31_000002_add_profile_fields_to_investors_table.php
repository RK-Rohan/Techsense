<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            if (! Schema::hasColumn('investors', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('investors', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('address');
            }
            if (! Schema::hasColumn('investors', 'emergency_contact_number')) {
                $table->string('emergency_contact_number', 50)->nullable()->after('emergency_contact_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            foreach (['emergency_contact_number', 'emergency_contact_name', 'address'] as $col) {
                if (Schema::hasColumn('investors', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
