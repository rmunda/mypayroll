<?php

namespace App\Filament\Resources\WeeklyOffRules\Pages;

use App\Filament\Resources\WeeklyOffRules\WeeklyOffRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWeeklyOffRules extends ListRecords
{
    protected static string $resource = WeeklyOffRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
