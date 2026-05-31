<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Order matters:
        // 1. Roles and permissions must exist before we assign them to users
        // 2. Default data (departments, pay structures etc) seeds after
        $this->call([
            RolesAndPermissionsSeeder::class,
            DefaultDataSeeder::class,
            WeeklyOffRuleSeeder::class,
            FinancialYearAndHolidaySeeder::class, 
            LeaveTypeSeeder::class,
            LeavePolicySeeder::class,
        ]);
    }
}
