<?php

namespace App\Filament\Resources\FinancialYears\Pages;

use App\Filament\Resources\FinancialYears\FinancialYearResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Database\Eloquent\Model;

class EditFinancialYear extends EditRecord
{
    protected static string $resource = FinancialYearResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // Show a warning banner action (non-clickable info) when locked
            Action::make('locked_notice')
                ->label('Editing Disabled — linked payroll or holiday data exists')
                ->icon('heroicon-o-lock-closed')
                ->disabled()
                ->visible(fn () => ! $this->getRecord()->canBeEdited()),

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

    // Double-guard: block the save at the handler level too
    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        if (! $record->canBeEdited()) {
            Notification::make()
                ->title('Cannot edit this Financial Year')
                ->body('It has payroll runs or holidays linked to it.')
                ->danger()
                ->send();

            $this->halt(); // stops save, stays on the page
        }

        if (!empty($data['is_current'])) {
            $record->markAsCurrent();
            unset($data['is_current']);
        }

        $record->update($data);

        return $record;
    }
}
