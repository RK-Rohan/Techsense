<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission_exists = Permission::where('name', 'due_payment_received.view')
                                    ->exists();

        if (! $permission_exists) {
            Permission::create([
                'name' => 'due_payment_received.view',
                'guard_name' => 'web',
            ]);
        }

        $roles = Role::all();

        foreach ($roles as $role) {
            $role->givePermissionTo('due_payment_received.view');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
