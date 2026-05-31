<?php

namespace App\Filament\Resources\LeaveTransactions\Pages;

use App\Filament\Resources\LeaveTransactions\LeaveTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeaveTransaction extends EditRecord
{
    protected static string $resource = LeaveTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
