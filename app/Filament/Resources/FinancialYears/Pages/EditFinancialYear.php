<?php

namespace App\Filament\Resources\FinancialYears\Pages;

use App\Filament\Resources\FinancialYears\FinancialYearResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Database\Eloquent\Model;

class EditFinancialYear extends EditRecord
{
    protected static string $resource = FinancialYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function ($record, $action) {
                    if (! $record->canBeDeleted()) {
                        Notification::make()
                            ->title('Cannot delete this Financial Year')
                            ->body('It has payroll runs or holidays linked to it.')
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (!empty($data['is_current'])) {
            $record->markAsCurrent();
            unset($data['is_current']);
        }

        $record->update($data);

        return $record;
    }
}
