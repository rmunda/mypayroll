<?php

namespace App\Filament\Resources\PaySlips\Pages;

use App\Filament\Resources\PaySlips\PaySlipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaySlip extends EditRecord
{
    protected static string $resource = PaySlipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
