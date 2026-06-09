<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name'                 => 'Casual Leave',
                'code'                 => 'CL',
                'color'                => 'info',
                'is_paid'              => true,
                'is_accrued'           => false,
                'requires_document'    => false,
                'max_days_per_year'    => 12,
                'max_days_per_request' => 3,    // max 3 days at a time
                'min_notice_days'      => 1,    // 1 day advance notice
                'description'          => 'For personal and urgent work',
                'is_active'            => true,
            ],
            [
                'name'                 => 'Sick Leave',
                'code'                 => 'SL',
                'color'                => 'warning',
                'is_paid'              => true,
                'is_accrued'           => false,
                'requires_document'    => true,  // needs medical cert
                'max_days_per_year'    => 12,
                'max_days_per_request' => 12,
                'min_notice_days'      => 0,     // no advance notice needed
                'description'          => 'For illness and medical reasons',
                'is_active'            => true,
            ],
            [
                'name'                 => 'Earned Leave',
                'code'                 => 'EL',
                'color'                => 'success',
                'is_paid'              => true,
                'is_accrued'           => true,  // accrues 1.5 days/month
                'requires_document'    => false,
                'max_days_per_year'    => 15,
                'max_days_per_request' => 15,
                'min_notice_days'      => 7,     // 7 days advance notice
                'description'          => 'Earned leave accrued monthly',
                'is_active'            => true,
            ],
            [
                'name'                 => 'Maternity Leave',
                'code'                 => 'ML',
                'color'                => 'pink',
                'is_paid'              => true,
                'is_accrued'           => false,
                'requires_document'    => true,
                'max_days_per_year'    => 180,
                'max_days_per_request' => 180,
                'min_notice_days'      => 30,
                'applicable_gender'    => 'female',
                'description'          => 'As per Maternity Benefit Act 1961',
                'is_active'            => true,
            ],
            [
                'name'                 => 'Paternity Leave',
                'code'                 => 'PL',
                'color'                => 'purple',
                'is_paid'              => true,
                'is_accrued'           => false,
                'requires_document'    => true,
                'max_days_per_year'    => 15,
                'max_days_per_request' => 15,
                'min_notice_days'      => 7,
                'applicable_gender'    => 'male',
                'description'          => 'For fathers on birth of child',
                'is_active'            => true,
            ],
            [
                'name'                 => 'Compensatory Off',
                'code'                 => 'COF',
                'color'                => 'gray',
                'is_paid'              => true,
                'is_accrued'           => false,
                'requires_document'    => false,
                'max_days_per_year'    => 0,     // no fixed limit
                'max_days_per_request' => 1,
                'min_notice_days'      => 1,
                'description'          => 'For working on holidays or weekends',
                'is_active'            => true,
            ],
            [
                'name'                 => 'Loss of Pay',
                'code'                 => 'LOP',
                'color'                => 'danger',
                'is_paid'              => false,  // unpaid
                'is_accrued'           => false,
                'requires_document'    => false,
                'max_days_per_year'    => 0,      // no fixed limit
                'max_days_per_request' => 0,
                'min_notice_days'      => 0,
                'description'          => 'When all paid leave balance is exhausted',
                'is_active'            => true,
            ],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
