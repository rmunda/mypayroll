<?php

namespace App\Filament\Resources\LeaveTransactions\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use App\Models\LeaveTransaction;

class LeaveTransactionInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Transaction details')
                ->columns(3)
                ->schema([
                    TextEntry::make('employee.name')
                        ->label('Employee'),

                    TextEntry::make('employee.employee_code')
                        ->label('Employee ID'),

                    TextEntry::make('financialYear.label')
                        ->label('Financial year'),

                    TextEntry::make('leaveType.name')
                        ->label('Leave type')
                        ->badge(),

                    TextEntry::make('transaction_type')
                        ->label('Transaction type')
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'allocated'    => 'info',
                            'accrued'      => 'success',
                            'carry_forward'=> 'info',
                            'credit'       => 'success',
                            'debit'        => 'danger',
                            'adjustment'   => 'warning',
                            'encashment'   => 'warning',
                            'lapsed'       => 'gray',
                            default        => 'gray',
                        }),

                    TextEntry::make('created_at')
                        ->label('Date & time')
                        ->dateTime('d M Y h:i A'),
                ]),

            Section::make('Balance impact')
                ->columns(3)
                ->schema([
                    TextEntry::make('balance_before')
                        ->label('Balance before')
                        ->suffix(' days'),

                    TextEntry::make('days')
                        ->label('Days affected')
                        ->formatStateUsing(fn($state, LeaveTransaction $record) =>
                            $record->isCredit()
                                ? '+' . $state . ' days'
                                : '-' . $state . ' days'
                        )
                        ->color(fn(LeaveTransaction $record) =>
                            $record->isCredit() ? 'success' : 'danger'
                        )
                        ->weight('bold'),

                    TextEntry::make('balance_after')
                        ->label('Balance after')
                        ->suffix(' days')
                        ->weight('bold'),
                ]),

            Section::make('Additional info')
                ->columns(2)
                ->schema([
                    TextEntry::make('remarks')
                        ->label('Remarks')
                        ->default('No remarks'),

                    TextEntry::make('createdBy.name')
                        ->label('Done by')
                        ->default('System'),

                    TextEntry::make('leave.from_date')
                        ->label('Leave from')
                        ->date('d M Y')
                        ->visible(fn($record) => $record->leave_id !== null),

                    TextEntry::make('leave.to_date')
                        ->label('Leave to')
                        ->date('d M Y')
                        ->visible(fn($record) => $record->leave_id !== null),
                ]),

        ]);
    }
}
