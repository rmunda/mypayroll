<?php
// app/Models/FinancialYear.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FinancialYear extends Model
{
    protected $fillable = [
        'label',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
    ];

    // relationships
    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    public function payrollRuns()
    {
        return $this->hasMany(PayrollRun::class);
    }

    // helpers
    public static function current(): self
    {
        return static::where('is_current', true)->firstOrFail();
    }

    public static function forDate(Carbon $date): ?self
    {
        return static::where('start_date', '<=', $date)
                     ->where('end_date',   '>=', $date)
                     ->first();
    }

    // when marking a new FY as current
    // automatically unset the previous one
    public function markAsCurrent(): void
    {
        static::where('is_current', true)->update(['is_current' => false]);
        $this->update(['is_current' => true]);
    }

    public function getHolidayDates(): array
    {
        return $this->holidays
                    ->pluck('date')
                    ->map(fn($d) => $d->format('Y-m-d'))
                    ->toArray();
    }
}