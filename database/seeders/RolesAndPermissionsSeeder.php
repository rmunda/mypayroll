<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {   
        // Clear the permission cache first
        // Spatie caches permissions for performance
        // If you don't clear it, old cached data causes conflicts
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------
        // CREATE ALL PERMISSIONS
        // -------------------------

        $permissions = [
            // Employee permissions
            'ViewAny:Employee',
            'View:Employee',
            'Create:Employee',
            'Update:Employee',
            'Delete:Employee',
            'DeleteAny:Employee',
            'Restore:Employee',
            'RestoreAny:Employee',
            'ForceDelete:Employee',
            'ForceDeleteAny:Employee',
            'Replicate:Employee',
            'Reorder:Employee',

            // Payroll permissions
            'ViewAny:PayrollRun',
            'View:PayrollRun',
            'Create:PayrollRun',
            'Update:PayrollRun',
            'Process:Payroll',
            'Approve:Payroll',
            'Send:Payslips',

            // Pay slip permissions
            'ViewAny:PaySlip',
            'View:PaySlip',
            'Download:PaySlip',

            // Attendance permissions
            'ViewAny:Attendance',
            'View:Attendance',
            'Create:Attendance',
            'Update:Attendance',

            // Leave permissions
            'ViewAny:Leave',
            'View:Leave',
            'Create:Leave',
            'Update:Leave',
            'Approve:Leave',

            // Deduction permissions
            'ViewAny:DeductionRule',
            'View:DeductionRule',
            'Create:DeductionRule',
            'Update:DeductionRule',

            // Department permissions
            'ViewAny:Department',
            'View:Department',
            'Create:Department',
            'Update:Department',

            // Report permissions
            'View:Reports',
            'Export:Reports',

            // User management permissions
            'ViewAny:User',
            'View:User',
            'Create:User',
            'Update:User',
            'Delete:User',
        ];

        // -------------------------
        // CREATE ROLES AND ASSIGN PERMISSIONS
        // -------------------------

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ADMIN — can do absolutely everything
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // HR — everything except user management
        $hr = Role::firstOrCreate(['name' => 'hr']);
        $hr->syncPermissions([
            'ViewAny:Employee', 'View:Employee', 'Create:Employee', 'Update:Employee',
            'ViewAny:PayrollRun', 'View:PayrollRun', 'Create:PayrollRun',
            'Update:PayrollRun', 'Process:Payroll', 'Send:Payslips',
            'ViewAny:PaySlip', 'View:PaySlip', 'Download:PaySlip',
            'ViewAny:Attendance', 'View:Attendance', 'Create:Attendance', 'Update:Attendance',
            'ViewAny:Leave', 'View:Leave', 'Update:Leave', 'Approve:Leave',
            'ViewAny:DeductionRule', 'View:DeductionRule',
            'ViewAny:Department', 'View:Department',
            'View:Reports', 'Export:Reports',
        ]);

        // MANAGER — can see their team, approve leaves, view attendance
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'ViewAny:Employee', 'View:Employee',
            'ViewAny:Attendance', 'View:Attendance', 'Create:Attendance', 'Update:Attendance',
            'ViewAny:Leave', 'View:Leave', 'Approve:Leave',
            'View:PaySlip', 'Download:PaySlip',
            'View:Reports',
        ]);

        // EMPLOYEE — self service only
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'View:PaySlip',
            'Download:PaySlip',
            'View:Attendance',
            'View:Leave',
            'Create:Leave',
        ]);
    }
}