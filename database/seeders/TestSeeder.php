<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Shield-style permissions for all resources so the sidebar
        // can call $user->can('View:Attendance') etc. without throwing.
        $actions = ['ViewAny', 'View', 'Create', 'Update', 'Delete'];

        $resources = [
            'Attendance', 'DeductionRule', 'Department', 'Employee',
            'FinancialYear', 'Holiday', 'Leave', 'LeaveBalance',
            'LeavePolicy', 'LeavePolicyDetail', 'LeaveTransaction', 'LeaveType',
            'PayrollRun', 'PaySlip', 'User', 'WeeklyOffRule',
            'Role', 'Permission',
        ];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$action}:{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create roles and assign those permissions
        $this->call(RolesAndPermissionsSeeder::class);
    }
}
