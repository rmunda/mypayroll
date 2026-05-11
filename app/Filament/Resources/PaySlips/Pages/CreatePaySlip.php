<?php

namespace App\Filament\Resources\PaySlips\Pages;

use App\Filament\Resources\PaySlips\PaySlipResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaySlip extends CreateRecord
{
    protected static string $resource = PaySlipResource::class;
}
