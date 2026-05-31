<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeavePolicy extends Model
{
    protected $fillable = [
        'financial_year_id',
        'name',
        'is_default',
        'earned_leave_accrual',
        'earned_accrual_per_month',
        'accrual_frequency',
        'carry_forward_earned',
        'max_carry_forward_days',
    ];

    protected $casts = [
        'is_default'               => 'boolean',
        'earned_leave_accrual'     => 'boolean',
        'carry_forward_earned'     => 'boolean',
        'earned_accrual_per_month' => 'decimal:2',
        'max_carry_forward_days'   => 'decimal:1',
    ];

    // relationships
    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function policyDetails(): HasMany
    {
        return $this->hasMany(LeavePolicyDetail::class);
    }

    // helpers

    // get current active policy
    public static function current(): ?self
    {
        $fy = FinancialYear::current();

        if (!$fy) return null;

        return static::where('financial_year_id', $fy->id)
                     ->where('is_default', true)
                     ->first();
    }

    // get policy for a specific financial year
    public static function forYear(FinancialYear $fy): ?self
    {
        return static::where('financial_year_id', $fy->id)
                     ->where('is_default', true)
                     ->first();
    }

    // get allocation for a specific leave type
    public function getAllocationForType(int $leaveTypeId): float
    {
        $detail = $this->policyDetails()
                       ->where('leave_type_id', $leaveTypeId)
                       ->first();

        return $detail?->days_per_year ?? 0;
    }

    // get accrual per month for a specific leave type
    public function getAccrualForType(int $leaveTypeId): float
    {
        $detail = $this->policyDetails()
                       ->where('leave_type_id', $leaveTypeId)
                       ->first();

        return $detail?->accrual_per_month ?? 0;
    }

    // check if carry forward is allowed for a leave type
    public function allowsCarryForward(int $leaveTypeId): bool
    {
        $detail = $this->policyDetails()
                       ->where('leave_type_id', $leaveTypeId)
                       ->first();

        return $detail?->carry_forward ?? false;
    }

    // get max carry forward days for a leave type
    public function getMaxCarryForward(int $leaveTypeId): float
    {
        $detail = $this->policyDetails()
                       ->where('leave_type_id', $leaveTypeId)
                       ->first();

        return $detail?->max_carry_forward ?? 0;
    }

    // when setting a policy as default
    // unset all other policies for the same financial year
    public function markAsDefault(): void
    {
        static::where('financial_year_id', $this->financial_year_id)
              ->where('id', '!=', $this->id)
              ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }
}
