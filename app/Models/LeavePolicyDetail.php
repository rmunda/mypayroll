<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeavePolicyDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'leave_policy_id',
        'leave_type_id',
        'days_per_year',
        'carry_forward',
        'max_carry_forward',
        'accrual_per_month',
        'allow_encashment',
        'max_encashment_days',
    ];

    protected $casts = [
        'carry_forward'       => 'boolean',
        'allow_encashment'    => 'boolean',
        'days_per_year'       => 'decimal:1',
        'max_carry_forward'   => 'decimal:1',
        'accrual_per_month'   => 'decimal:2',
        'max_encashment_days' => 'decimal:1',
    ];

    // relationships
    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    // helpers

    // check if this leave type has unlimited days
    public function isUnlimited(): bool
    {
        return $this->days_per_year == 0;
    }

    // check if this leave type accrues
    public function accrues(): bool
    {
        return $this->accrual_per_month > 0;
    }

    // get annual accrual total
    // e.g. 1.5 per month x 12 = 18 days
    public function annualAccrualTotal(): float
    {
        return $this->accrual_per_month * 12;
    }

    // get effective days for a given month count
    // used when employee joins mid year
    // e.g. joins in October = 6 months remaining
    // earned leave = 1.5 x 6 = 9 days
    public function effectiveDaysForMonths(int $months): float
    {
        if ($this->accrues()) {
            return round($this->accrual_per_month * $months, 1);
        }

        // pro-rate non-accrued leave by months remaining
        if ($this->days_per_year > 0) {
            return round(($this->days_per_year / 12) * $months, 1);
        }

        return 0;
    }
}
