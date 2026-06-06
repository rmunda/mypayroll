<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $data = $this->form->getRawState();

        if (!isset($data['is_login_active'])) return;

        $user = $this->record->user;

        if (!$user) return;

        $user->update(['is_active' => $data['is_login_active']]);

        Notification::make()
            ->title(
                $data['is_login_active']
                    ? 'Login enabled for ' . $this->record->name
                    : 'Login disabled for ' . $this->record->name
            )
            ->success()
            ->send();
    }
}
