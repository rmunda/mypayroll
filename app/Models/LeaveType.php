<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'color',
        'is_paid',
        'is_accrued',
        'requires_document',
        'max_days_per_year',
        'max_days_per_request',
        'min_notice_days',
        'applicable_gender',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_paid'            => 'boolean',
        'is_accrued'         => 'boolean',
        'requires_document'  => 'boolean',
        'is_active'          => 'boolean',
        'max_days_per_year'  => 'decimal:1',
        'max_days_per_request' => 'decimal:1',
    ];

    // relationships
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function policyDetails(): HasMany
    {
        return $this->hasMany(LeavePolicyDetail::class);
    }

    public function leaveTransactions(): HasMany
    {
        return $this->hasMany(LeaveTransaction::class);
    }

    // helpers
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    public function isUnlimited(): bool
    {
        return $this->max_days_per_year == 0;
    }

    // whether this leave type should be allocated to an employee of the given gender.
    // 'all' (or a null employee gender against a restricted type) is handled here so
    // the allocation logic stays a simple appliesTo() check.
    public function appliesTo(?string $gender): bool
    {
        if ($this->applicable_gender === 'all' || $this->applicable_gender === null) {
            return true;
        }

        return $this->applicable_gender === $gender;
    }

    public function requiresAdvanceNotice(): bool
    {
        return $this->min_notice_days > 0;
    }
}
