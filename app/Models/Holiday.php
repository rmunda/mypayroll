<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;
    protected $fillable = [
        'financial_year_id',
        'name',
        'date',
        'type',
        'description',
        'is_paid',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_paid' => 'boolean',
        ];
    }

    // Check if a given date is a holiday
    public static function isHoliday(Carbon $date): bool
    {
        return static::whereDate('date', $date)->exists();
    }

    // Get all holidays for a date range
    public static function forPeriod($start, $end): Collection
    {
        return static::whereBetween('date', [$start, $end])->get();
    }

    // Relation
    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class);
    }
}