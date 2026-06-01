<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Services\LeaveBalanceService;
use App\Models\FinancialYear;

// default Laravel inspire command — keep this
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// ─────────────────────────────────────────
// SCHEDULED TASKS
// ─────────────────────────────────────────

// 1. Monthly earned leave accrual
// Runs on 1st of every month at midnight
Schedule::call(function () {
    Log::info('Running monthly earned leave accrual');
    app(LeaveBalanceService::class)->accrueMonthlyEarnedLeave();
    Log::info('Monthly earned leave accrual completed');
})
->monthlyOn(1, '00:00')
->name('accrue-earned-leave')
->withoutOverlapping()
->onOneServer();


// 2. Initialize leave balances for new financial year
// Runs every April 1st at midnight
Schedule::call(function () {
    Log::info('Initializing leave balances for new FY');

    $fy = FinancialYear::where('is_current', true)->first();

    if (!$fy) {
        Log::error('No current FY found for leave balance initialization');
        return;
    }

    app(LeaveBalanceService::class)->initializeForYear($fy);
    Log::info('Leave balances initialized for ' . $fy->label);
})
->yearlyOn(4, 1, '00:00')
->name('initialize-leave-balances')
->withoutOverlapping()
->onOneServer();


// 3. Lapse unused non-carry-forward leave at year end
// Runs every March 31st at 11:59 PM
Schedule::call(function () {
    Log::info('Running year-end leave lapse');

    $fy = FinancialYear::where('is_current', true)->first();
    if (!$fy) return;

    $nonCarryForwardTypes = \App\Models\LeaveType::where('is_active', true)
        ->whereHas('policyDetails', fn($q) =>
            $q->where('carry_forward', false)
        )
        ->get();

    foreach ($nonCarryForwardTypes as $leaveType) {
        $balances = \App\Models\LeaveBalance::where('financial_year_id', $fy->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->get();

        foreach ($balances as $balance) {
            $available = $balance->available;
            if ($available <= 0) continue;

            $before = $available;
            $balance->increment('lapsed', $available);

            \App\Models\LeaveTransaction::record(
                balance:         $balance->fresh(),
                transactionType: 'lapsed',
                days:            $available,
                balanceBefore:   $before,
                leaveId:         null,
                remarks:         'Year end lapse — unused ' . $leaveType->name
            );
        }
    }

    Log::info('Year-end leave lapse completed');
})
->yearlyOn(3, 31, '23:59')
->name('year-end-leave-lapse')
->withoutOverlapping()
->onOneServer();


// 4. Clear old activity logs
// Runs every week
Schedule::call(function () {
    \Spatie\Activitylog\Models\Activity::where(
        'created_at', '<', now()->subDays(90)
    )->delete();
    Log::info('Old activity logs cleared');
})
->weekly()
->name('clear-activity-logs');