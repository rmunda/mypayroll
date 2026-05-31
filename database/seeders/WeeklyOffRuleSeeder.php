<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WeeklyOffRule;

class WeeklyOffRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 5 day week (Mon-Fri)
        WeeklyOffRule::firstOrCreate(
            ['name' => '5 Day Week'],
            [
                'monday'        => true,
                'tuesday'       => true,
                'wednesday'     => true,
                'thursday'      => true,
                'friday'        => true,
                'saturday'      => false,
                'sunday'        => false,
                'saturday_type' => 'non_working',
                'is_default'    => true,
            ]
        );

        // 6 day week (Mon-Sat)
        WeeklyOffRule::firstOrCreate(
            ['name' => '6 Day Week'],
            [
                'monday'        => true,
                'tuesday'       => true,
                'wednesday'     => true,
                'thursday'      => true,
                'friday'        => true,
                'saturday'      => true,
                'sunday'        => false,
                'saturday_type' => 'working',
                'is_default'    => false,
            ]
        );

        // alternate saturday
        WeeklyOffRule::firstOrCreate(
            ['name' => 'Alternate Saturday'],
            [
                'monday'        => true,
                'tuesday'       => true,
                'wednesday'     => true,
                'thursday'      => true,
                'friday'        => true,
                'saturday'      => true,
                'sunday'        => false,
                'saturday_type' => 'alternate_1_3',
                'is_default'    => false,
            ]
        );
    }
}
