<?php

namespace App\Filament\Resources\PaySlips\Pages;

use App\Filament\Resources\PaySlips\PaySlipResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPaySlip extends ViewRecord
{
    protected static string $resource = PaySlipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
