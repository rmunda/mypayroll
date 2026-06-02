<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Exports\EmployeeExport;
use App\Filament\Resources\Employees\Actions\ImportEmployeesAction;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Widgets\EmployeeStatsWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportEmployeesAction::make(),

            Action::make('export_employees')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => Excel::download(new EmployeeExport, 'employees-' . now()->format('Y-m-d') . '.xlsx')),
        ];
    }
}
