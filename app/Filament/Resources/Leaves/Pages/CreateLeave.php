<?php

namespace App\Filament\Resources\Leaves\Pages;

use App\Filament\Resources\Leaves\LeaveResource;
use App\Services\LeaveBalanceService;
use Filament\Resources\Pages\CreateRecord;

class CreateLeave extends CreateRecord
{
    protected static string $resource = LeaveResource::class;

    protected function afterCreate(): void
    {
        // call onLeaveRequested after leave record is created
        app(LeaveBalanceService::class)
            ->onLeaveRequested($this->record);
    }

    // redirect back to list after creating
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
