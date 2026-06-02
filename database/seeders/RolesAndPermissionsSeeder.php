<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // roles only — Shield creates the permissions
        $admin    = Role::firstOrCreate(['name' => 'admin',    'guard_name' => 'web']);
        $hr       = Role::firstOrCreate(['name' => 'hr',       'guard_name' => 'web']);
        $manager  = Role::firstOrCreate(['name' => 'manager',  'guard_name' => 'web']);
        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // Normal admin gets everything except role and permissions
        $admin->syncPermissions(
            Permission::whereNotIn('name', [
                'ViewAny:Role',
                'View:Role',
                'Create:Role',
                'Update:Role',
                'Delete:Role',

                'ViewAny:Permission',
                'View:Permission',
                'Create:Permission',
                'Update:Permission',
                'Delete:Permission',
            ])->get()
        );

        // hr
        $hr->syncPermissions(
            Permission::whereIn('name', [
                'ViewAny:Employee',    'View:Employee',
                'Create:Employee',     'Update:Employee',
                'ViewAny:PayrollRun',  'View:PayrollRun',
                'Create:PayrollRun',   'Update:PayrollRun',
                'ViewAny:PaySlip',     'View:PaySlip',
                'ViewAny:Attendance',  'View:Attendance',
                'Create:Attendance',   'Update:Attendance',
                'ViewAny:Leave',       'View:Leave',
                'Update:Leave',
                'ViewAny:Department',  'View:Department',
                'ViewAny:Holiday',     'View:Holiday',
                'ViewAny:FinancialYear','View:FinancialYear',
                'ViewAny:LeaveType',   'View:LeaveType',
                'ViewAny:LeaveBalance','View:LeaveBalance',
            ])->get()
        );

        // manager
        $manager->syncPermissions(
            Permission::whereIn('name', [
                'ViewAny:Employee',   'View:Employee',
                'ViewAny:Attendance', 'View:Attendance',
                'Create:Attendance',  'Update:Attendance',
                'ViewAny:Leave',      'View:Leave',
                'ViewAny:PaySlip',    'View:PaySlip',
                'ViewAny:Holiday',    'View:Holiday',
            ])->get()
        );

        // employee
        $employee->syncPermissions(
            Permission::whereIn('name', [
                'View:PaySlip',
                'View:Attendance',
                'ViewAny:Leave',
                'View:Leave',
                'Create:Leave',
                'ViewAny:Holiday',
                'View:Holiday',
            ])->get()
        );
    }
}