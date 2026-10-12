<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $legacy = [
            'mushak_6_1' => ['purchase.view', 'view_own_purchase'],
            'mushak_6_2' => ['sell.view', 'view_own_sell_only'],
            'mushak_6_3' => ['print_invoice'],
        ];
        foreach ($legacy as $prefix => $permissions) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                Permission::findOrCreate($prefix.'.'.$action, 'web');
            }
        }
        foreach (['invoice_download.view', 'delivery_challan.view'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Preserve existing role access; administrators can then restrict each action.
        foreach (Role::where('guard_name', 'web')->with('permissions')->get() as $role) {
            $existing = $role->permissions->pluck('name')->all();
            foreach ($legacy as $prefix => $permissions) {
                if (array_intersect($existing, $permissions)) {
                    $role->givePermissionTo(array_map(fn ($action) => $prefix.'.'.$action, ['view', 'create', 'update', 'delete']));
                }
            }
            if (in_array('print_invoice', $existing)) {
                $role->givePermissionTo(['invoice_download.view', 'delivery_challan.view']);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        $names = ['invoice_download.view', 'delivery_challan.view'];
        foreach (['mushak_6_1', 'mushak_6_2', 'mushak_6_3'] as $prefix) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $names[] = $prefix.'.'.$action;
            }
        }
        Permission::where('guard_name', 'web')->whereIn('name', $names)->get()->each->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
