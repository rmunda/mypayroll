<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\PayStructure;
use App\Models\DeductionRule;
use App\Models\User;

class DefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        // Departments
        $depts = ['Engineering','Product','HR','Sales','Finance','Operations'];
        foreach ($depts as $d) {
            Department::firstOrCreate(['name' => $d], ['code' => strtoupper(substr($d,0,3))]);
        }

        // Pay structures
        PayStructure::firstOrCreate(['name' => 'Standard'], [
            'hra_percentage' => 40, 'ta_fixed' => 1600, 'special_allowance_pct' => 0, 'is_default' => true,
        ]);
        PayStructure::firstOrCreate(['name' => 'Management'], [
            'hra_percentage' => 50, 'ta_fixed' => 3200, 'special_allowance_pct' => 5, 'is_default' => false,
        ]);

        // Deduction rules (Indian statutory)
        $rules = [
            ['Provident Fund (Employee)', 'percentage', 12.0000, 'basic', 'employee', true],
            ['Provident Fund (Employer)', 'percentage', 12.0000, 'basic', 'employer', true],
            ['ESI (Employee)',            'percentage',  0.7500, 'gross', 'employee', true],
            ['ESI (Employer)',            'percentage',  3.2500, 'gross', 'employer', true],
            ['Professional Tax',          'fixed',     200.0000, 'gross', 'employee', true],
        ];
        foreach ($rules as [$name,$type,$value,$applies,$side,$statutory]) {
            DeductionRule::firstOrCreate(['name' => $name], [
                'type'           => $type,
                'value'          => $value,
                'applies_to'     => $applies,
                'deduction_side' => $side,
                'is_statutory'   => $statutory,
                'is_active'      => true,
            ]);
        }

        // Default admin user
        $admin = User::firstOrCreate(['email' => 'admin@payrollpro.test'], [
            'name'      => 'Admin User',
            'password'  => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');
    }
}