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
            
            // SIWES Activity Log management
            'siwes.activity.view',
            'siwes.activity.create',
            'siwes.activity.edit',
            'siwes.activity.delete',
            'siwes.activity.approve',
            'siwes.activity.reject',
            'siwes.activity.history',
            
            // SIWES Student management
            'siwes.student.view',
            'siwes.student.manage',
            'siwes.student.assign-supervisor',
            
            // SIWES Supervisor management
            'siwes.supervisor.view',
            'siwes.supervisor.manage',
            'siwes.supervisor.approvals',
            'siwes.supervisor.students',
            
            // SIWES PPA management
            'siwes.ppa.setup',
            'siwes.ppa.view',
            'siwes.ppa.edit',
            
            // SIWES Weekly Summary
            'siwes.summary.create',
            'siwes.summary.view',
            'siwes.summary.edit',
            
            // SIWES Settings (Admin only)
            'siwes.settings.view',
            'siwes.settings.manage',
            'siwes.settings.control',
            
            // System management
            'dashboard.view',
            'system.manage',
            'report.view',
            'data.export',
            
            // Department management
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',
            'department.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Student role
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $studentRole->syncPermissions([
            'dashboard.view',
            'siwes.activity.view',
            'siwes.activity.create',
            'siwes.activity.edit',
            'siwes.activity.history',
            'siwes.ppa.setup',
            'siwes.ppa.view',
            'siwes.ppa.edit',
            'siwes.summary.create',
            'siwes.summary.view',
            'siwes.summary.edit',
        ]);

        // Supervisor role
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisorRole->syncPermissions([
            'dashboard.view',
            'user.view',
            'siwes.activity.view',
            'siwes.activity.approve',
            'siwes.activity.reject',
            'siwes.activity.history',
            'siwes.student.view',
            'siwes.supervisor.approvals',
            'siwes.supervisor.students',
            'siwes.summary.view',
            'report.view',
        ]);

        // hod role
        $hodRole = Role::firstOrCreate(['name' => 'hod']);
        $hodRole->syncPermissions([
            'dashboard.view',
            'user.view',
            'siwes.activity.view',
            'siwes.activity.approve',
            'siwes.activity.reject',
            'siwes.activity.history',
            'siwes.student.view',
            'siwes.supervisor.approvals',
            'siwes.supervisor.students',
            'siwes.summary.view',
            'report.view',
            'department.view',
            'department.manage',
        ]);

        // Superadmin role
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $superadminRole->syncPermissions(Permission::all());
    }
}
