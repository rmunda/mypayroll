<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\FinancialYear;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeavePolicySeeder extends Seeder
{
    public function run(): void
    {
        $fy = FinancialYear::where('is_current', true)->first();
        if (!$fy) return;

        $policy = LeavePolicy::firstOrCreate(
            [
                'financial_year_id' => $fy->id,
                'name'              => 'Standard Policy',
            ],
            [
                'is_default'               => true,
                'earned_leave_accrual'     => true,
                'earned_accrual_per_month' => 1.5,
                'accrual_frequency'        => 'monthly',
                'carry_forward_earned'     => true,
                'max_carry_forward_days'   => 30,
            ]
        );

        // create policy details per leave type
        $details = [
            'CL'  => ['days_per_year' => 12,  'carry_forward' => false, 'accrual_per_month' => 0],
            'SL'  => ['days_per_year' => 12,  'carry_forward' => false, 'accrual_per_month' => 0],
            'EL'  => ['days_per_year' => 15,  'carry_forward' => true,  'accrual_per_month' => 1.5, 'max_carry_forward' => 30],
            'ML'  => ['days_per_year' => 180, 'carry_forward' => false, 'accrual_per_month' => 0],
            'PL'  => ['days_per_year' => 15,  'carry_forward' => false, 'accrual_per_month' => 0],
            'COF' => ['days_per_year' => 0,   'carry_forward' => false, 'accrual_per_month' => 0],
            'LOP' => ['days_per_year' => 0,   'carry_forward' => false, 'accrual_per_month' => 0],
        ];

        foreach ($details as $code => $detail) {
            $leaveType = LeaveType::where('code', $code)->first();
            if (!$leaveType) continue;

            LeavePolicyDetail::firstOrCreate(
                [
                    'leave_policy_id' => $policy->id,
                    'leave_type_id'   => $leaveType->id,
                ],
                array_merge($detail, [
                    'max_carry_forward'   => $detail['max_carry_forward'] ?? 0,
                    'allow_encashment'    => false,
                    'max_encashment_days' => 0,
                ])
            );
        }
    }
}