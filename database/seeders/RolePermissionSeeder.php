<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            
            // Role management
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            'role.assign',
            
            // Log management
            'log.view',
            'log.create',
            'log.edit',
            'log.delete',
            'log.approve',
            
            // System management
            'dashboard.view',
            'system.manage',
            'report.view',
            'data.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Student role
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $studentRole->syncPermissions([
            'log.view',
            'log.create',
            'log.edit',
            'dashboard.view',
        ]);

        // Supervisor role
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisorRole->syncPermissions([
            'user.view',
            'log.view',
            'log.create',
            'log.edit',
            'log.approve',
            'dashboard.view',
            'report.view',
        ]);

        // Superadmin role
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $superadminRole->syncPermissions(Permission::all());
    }
}
