<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    protected $names = [
        'investor.view', 'investor.create', 'investor.update', 'investor.delete',
        'investment.view', 'investment.create', 'investment.update', 'investment.delete',
    ];

    public function up()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->names as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Investor and Investment pages were reachable by anyone with account
        // access. Keep that access; administrators can then restrict each action.
        foreach (Role::where('guard_name', 'web')->with('permissions')->get() as $role) {
            if ($role->permissions->contains('name', 'account.access')) {
                $role->givePermissionTo($this->names);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        Permission::where('guard_name', 'web')->whereIn('name', $this->names)->get()->each->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
