<?php

namespace App\Filament\Resources\DeductionRules\Pages;

use App\Filament\Resources\DeductionRules\DeductionRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeductionRules extends ListRecords
{
    protected static string $resource = DeductionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
