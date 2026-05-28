<?php

namespace App\Filament\Resources\Leaves\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Models\Employee;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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

                Select::make('type')
                    ->options([
                        'casual' => 'Casual',
                        'sick' => 'Sick',
                        'earned' => 'Earned',
                        'maternity' => 'Maternity',
                        'unpaid' => 'Unpaid',
                    ])
                    ->required(),

                DatePicker::make('from_date')
                    ->required()
                    ->live(),

                DatePicker::make('to_date')
                    ->required()
                    ->afterOrEqual('from_date')
                    ->live(),

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
                            'pending'  => 'Pending (under review)',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
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
}
