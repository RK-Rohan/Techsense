<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'investor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('investor_id')
                    ->nullable()
                    ->after('status');

                $table->foreign('investor_id')
                    ->references('id')->on('investors')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'investor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['investor_id']);
                $table->dropColumn('investor_id');
            });
        }
    }
};
