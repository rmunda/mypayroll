<?php

namespace App\Filament\Resources\Leaves\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveType;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class LeaveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('employee_id')
                    ->label('Employee')
                    ->options(function () {

                        $user = Auth::user();

                        // if logged in user is employee
                        if ($user->hasRole('employee')) {

                            return Employee::where('user_id', $user->id)
                                ->pluck('name', 'id');
                        }

                        // admin/hr can see all employees
                        return Employee::pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),

                // from database
                Select::make('leave_type_id')
                    ->label('Leave type')
                    ->options(
                        LeaveType::where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // auto set max days hint
                        $type = LeaveType::find($state);
                        if ($type && $type->min_notice_days > 0) {
                            // you can show a helper text dynamically
                        }
                    }),

                DatePicker::make('from_date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) =>
                        self::calculateDays($get, $set)
                    ),

                DatePicker::make('to_date')
                    ->required()
                    ->afterOrEqual('from_date')
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) =>
                        self::calculateDays($get, $set)
                    ),    

                TextInput::make('days')
                    ->numeric()
                    ->required(),

                Textarea::make('reason'),

                // STATUS — different options per role
                Select::make('status')
                    ->options(function () {
                        // employee can only set Request or Cancelled
                        if (auth()->user()->hasRole('employee')) {
                            return [
                                'request'   => 'Request',
                                'cancelled' => 'Cancel request',
                            ];
                        }
                        // hr, admin, manager see operational statuses
                        return [
                            'request'   => 'Request',
                            'pending'  => 'Pending (under review)',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            'cancelled' => 'Cancel request',
                        ];
                    })
                    ->default(function () {
                        // auto set to request for employee
                        if (auth()->user()->hasRole('employee')) {
                            return 'request';
                        }
                        return 'pending';
                    })
                    ->required(),
            ]);
    }

    protected static function calculateDays(Get $get, Set $set): void
    {
        $from = $get('from_date');
        $to   = $get('to_date');

        if ($from && $to) {

            // get employee's weekly off rule, fall back to default rule
            $employee = Employee::with('weeklyOffRule')->find($get('employee_id'));
            $rule     = $employee?->weeklyOffRule
                        ?? \App\Models\WeeklyOffRule::where('is_default', true)->first();

            $start = Carbon::parse($from)->copy();
            $end   = Carbon::parse($to);
            $days  = 0;

            while ($start->lte($end)) {

                // Skip non-working days per employee's weekly off rule
                $isWorking = $rule ? $rule->isWorkingDay($start) : !$start->isWeekend();
                if (!$isWorking) {
                    $start->addDay();
                    continue;
                }

                // Skip public holidays
                if (Holiday::isHoliday($start)) {
                    $start->addDay();
                    continue;
                }

                $days++;
                $start->addDay();
            }

            $set('days', $days);
        }
    }
}
