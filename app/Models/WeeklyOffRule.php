<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class WeeklyOffRule extends Model
{
    protected $fillable = [
        'name',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'saturday_type',
        'is_default',
    ];

    protected $casts = [
        'monday'     => 'boolean',
        'tuesday'    => 'boolean',
        'wednesday'  => 'boolean',
        'thursday'   => 'boolean',
        'friday'     => 'boolean',
        'saturday'   => 'boolean',
        'sunday'     => 'boolean',
        'is_default' => 'boolean',
    ];

    // relationships
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // helpers

    // get the default rule
    public static function default(): ?self
    {
        return static::where('is_default', true)->first();
    }

    // check if a given date is a working day under this rule
    public function isWorkingDay(Carbon $date): bool
    {
        $dayName = strtolower($date->format('l')); // monday, tuesday...

        // check if the day is enabled in this rule
        if (!$this->$dayName) {
            return false;
        }

        // handle saturday special cases
        if ($dayName === 'saturday') {
            return match($this->saturday_type) {

                // every saturday is working
                'working' => true,

                // every saturday is half day — counts as working
                'half_day' => true,

                // 1st and 3rd saturday working
                // 2nd and 4th saturday off
                'alternate_1_3' => in_array(
                    $date->weekOfMonth, [1, 3]
                ),

                // 2nd and 4th saturday working
                // 1st and 3rd saturday off
                'alternate_2_4' => in_array(
                    $date->weekOfMonth, [2, 4]
                ),

                // saturday is off
                'non_working' => false,

                default => false,
            };
        }

        return true;
    }

    // check if a given date is a half day
    public function isHalfDay(Carbon $date): bool
    {
        $dayName = strtolower($date->format('l'));

        if ($dayName === 'saturday') {
            return $this->saturday
                && $this->saturday_type === 'half_day';
        }

        return false;
    }

    // get working days count between two dates
    // used by PayrollService and LeaveBalanceService
    public function countWorkingDays(Carbon $start, Carbon $end, array $holidays = []): int
    {
        $days    = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if (
                $this->isWorkingDay($current) &&
                !in_array($current->format('Y-m-d'), $holidays)
            ) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    // get working days as an array of day names
    // used for display in the resource table
    public function getWorkingDayNames(): array
    {
        $days = [];
        $allDays = [
            'monday', 'tuesday', 'wednesday',
            'thursday', 'friday', 'saturday', 'sunday'
        ];

        foreach ($allDays as $day) {
            if ($this->$day) {
                $days[] = ucfirst($day);
            }
        }

        return $days;
    }

    // get off days as array
    public function getOffDayNames(): array
    {
        $days = [];
        $allDays = [
            'monday', 'tuesday', 'wednesday',
            'thursday', 'friday', 'saturday', 'sunday'
        ];

        foreach ($allDays as $day) {
            if (!$this->$day) {
                $days[] = ucfirst($day);
            }
        }

        return $days;
    }
}
