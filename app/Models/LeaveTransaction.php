<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveTransaction extends Model
{
    protected $fillable = [
        'employee_id',
        'financial_year_id',
        'leave_balance_id',
        'leave_id',
        'leave_type_id',
        'transaction_type',
        'days',
        'balance_before',
        'balance_after',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'days'           => 'decimal:1',
        'balance_before' => 'decimal:1',
        'balance_after'  => 'decimal:1',
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

    public function leaveBalance(): BelongsTo
    {
        return $this->belongsTo(LeaveBalance::class);
    }

    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // helpers

    // record a new transaction
    // called by LeaveBalanceService every time balance changes
    public static function record(
        LeaveBalance $balance,
        string       $transactionType,
        float        $days,
        float        $balanceBefore,
        ?int         $leaveId = null,
        string       $remarks = ''
    ): self {
        return static::create([
            'employee_id'       => $balance->employee_id,
            'financial_year_id' => $balance->financial_year_id,
            'leave_balance_id'  => $balance->id,
            'leave_id'          => $leaveId,
            'leave_type_id'     => $balance->leave_type_id,
            'transaction_type'  => $transactionType,
            'days'              => $days,
            'balance_before'    => $balanceBefore,
            'balance_after'     => $balance->fresh()->available,
            'remarks'           => $remarks,
            'created_by'        => auth()->id(),
        ]);
    }

    // check if transaction is a credit
    public function isCredit(): bool
    {
        return in_array($this->transaction_type, [
            'allocated',
            'accrued',
            'carry_forward',
            'credit',
        ]);
    }

    // check if transaction is a debit
    public function isDebit(): bool
    {
        return in_array($this->transaction_type, [
            'debit',
            'encashment',
            'lapsed',
        ]);
    }

    // get color for badge display
    public function getColorAttribute(): string
    {
        return match($this->transaction_type) {
            'allocated'    => 'info',
            'accrued'      => 'success',
            'carry_forward'=> 'info',
            'credit'       => 'success',
            'debit'        => 'danger',
            'adjustment'   => 'warning',
            'encashment'   => 'warning',
            'lapsed'       => 'gray',
            default        => 'gray',
        };
    }

    // get icon for display
    public function getIconAttribute(): string
    {
        return match($this->transaction_type) {
            'allocated'    => 'heroicon-o-plus-circle',
            'accrued'      => 'heroicon-o-arrow-trending-up',
            'carry_forward'=> 'heroicon-o-arrow-right-circle',
            'credit'       => 'heroicon-o-plus-circle',
            'debit'        => 'heroicon-o-minus-circle',
            'adjustment'   => 'heroicon-o-pencil',
            'encashment'   => 'heroicon-o-banknotes',
            'lapsed'       => 'heroicon-o-clock',
            default        => 'heroicon-o-circle',
        };
    }

    // scope — get transactions for a specific employee
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // scope — get transactions for current financial year
    public function scopeCurrentYear($query)
    {
        $fy = FinancialYear::current();
        return $query->where('financial_year_id', $fy?->id);
    }

    // scope — get only credits
    public function scopeCredits($query)
    {
        return $query->whereIn('transaction_type', [
            'allocated',
            'accrued',
            'carry_forward',
            'credit',
        ]);
    }

    // scope — get only debits
    public function scopeDebits($query)
    {
        return $query->whereIn('transaction_type', [
            'debit',
            'encashment',
            'lapsed',
        ]);
    }
}
