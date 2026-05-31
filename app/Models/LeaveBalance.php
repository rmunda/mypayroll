<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'financial_year_id',
        'leave_type_id',
        'allocated',
        'accrued',
        'carried_forward',
        'used',
        'pending',
        'encashed',
        'lapsed',
    ];

    protected $casts = [
        'allocated'       => 'decimal:1',
        'accrued'         => 'decimal:1',
        'carried_forward' => 'decimal:1',
        'used'            => 'decimal:1',
        'pending'         => 'decimal:1',
        'encashed'        => 'decimal:1',
        'lapsed'          => 'decimal:1',
    ];

    // relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LeaveTransaction::class);
    }

    // computed attributes

    // total available = allocated + accrued + carried_forward - used - pending - encashed - lapsed
    public function getAvailableAttribute(): float
    {
        return max(0,
            $this->allocated
            + $this->accrued
            + $this->carried_forward
            - $this->used
            - $this->pending
            - $this->encashed
            - $this->lapsed
        );
    }

    // total credited = all positive entries
    public function getTotalCreditedAttribute(): float
    {
        return $this->allocated
             + $this->accrued
             + $this->carried_forward;
    }

    // total debited = all negative entries
    public function getTotalDebitedAttribute(): float
    {
        return $this->used
             + $this->pending
             + $this->encashed
             + $this->lapsed;
    }

    // check if employee has enough balance
    public function hasSufficientBalance(float $days): bool
    {
        return $this->available >= $days;
    }

    // check if balance is low
    // useful for warning notifications
    public function isLow(float $threshold = 2): bool
    {
        return $this->available <= $threshold;
    }

    // helpers

    // get or create balance for an employee
    public static function getOrCreate(
        int $employeeId,
        int $financialYearId,
        int $leaveTypeId
    ): self {
        return static::firstOrCreate(
            [
                'employee_id'       => $employeeId,
                'financial_year_id' => $financialYearId,
                'leave_type_id'     => $leaveTypeId,
            ],
            [
                'allocated'       => 0,
                'accrued'         => 0,
                'carried_forward' => 0,
                'used'            => 0,
                'pending'         => 0,
                'encashed'        => 0,
                'lapsed'          => 0,
            ]
        );
    }

    // get all balances for an employee for current year
    public static function forEmployee(int $employeeId): \Illuminate\Database\Eloquent\Collection
    {
        $fy = FinancialYear::current();

        return static::where('employee_id', $employeeId)
                     ->where('financial_year_id', $fy?->id)
                     ->with('leaveType')
                     ->get();
    }

    // add to a balance field and save
    // used by LeaveBalanceService
    public function credit(string $field, float $days): void
    {
        $this->increment($field, $days);
    }

    // subtract from a balance field and save
    // used by LeaveBalanceService
    public function debit(string $field, float $days): void
    {
        $this->decrement($field, max(0, $days));
    }
}
