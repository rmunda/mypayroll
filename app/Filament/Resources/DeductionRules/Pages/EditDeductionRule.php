<?php

namespace App\Filament\Resources\DeductionRules\Pages;

use App\Filament\Resources\DeductionRules\DeductionRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeductionRule extends EditRecord
{
    protected static string $resource = DeductionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
