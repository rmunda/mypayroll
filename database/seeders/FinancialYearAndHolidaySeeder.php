<?php
// database/seeders/FinancialYearSeeder.php

namespace Database\Seeders;

use App\Models\FinancialYear;
use App\Models\Holiday;
use Illuminate\Database\Seeder;

class FinancialYearAndHolidaySeeder extends Seeder
{
    public function run(): void
    {
        // create FY 2025-26
        $fy = FinancialYear::firstOrCreate(
            ['label' => 'FY 2026-27'],
            [
                'start_date' => '2026-04-01',
                'end_date'   => '2027-03-31',
                'is_current' => true,
            ]
        );

        // seed holidays for FY 2025-26
        $holidays = [
            ['name' => 'Republic Day',      'date' => '2027-01-26', 'type' => 'national'],
            ['name' => 'Holi',              'date' => '2027-03-22', 'type' => 'national'],
            ['name' => 'Good Friday',       'date' => '2027-03-26', 'type' => 'national'],
            ['name' => 'Independence Day',  'date' => '2026-08-15', 'type' => 'national'],
            ['name' => 'Gandhi Jayanti',    'date' => '2026-10-02', 'type' => 'national'],
            ['name' => 'Dussehra',          'date' => '2026-10-20', 'type' => 'national'],
            ['name' => 'Diwali',            'date' => '2026-11-08', 'type' => 'national'],
            ['name' => 'Christmas',         'date' => '2026-12-25', 'type' => 'national'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                [
                    'financial_year_id' => $fy->id,
                    'date'              => $holiday['date'],
                ],
                array_merge($holiday, [
                    'financial_year_id' => $fy->id,
                    'is_paid'           => true,
                ])
            );
        }

        // create FY 2026-27 in advance
        // so HR can start adding next year's holidays
        FinancialYear::firstOrCreate(
            ['label' => 'FY 2027-28'],
            [
                'start_date' => '2027-04-01',
                'end_date'   => '2028-03-31',
                'is_current' => false,
            ]
        );
    }
}