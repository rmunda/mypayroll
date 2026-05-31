<?php

namespace App\Filament\Resources\WeeklyOffRules\Pages;

use App\Filament\Resources\WeeklyOffRules\WeeklyOffRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWeeklyOffRule extends EditRecord
{
    protected static string $resource = WeeklyOffRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
