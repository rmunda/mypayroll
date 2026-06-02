<?php

namespace App\Filament\Resources\Employees\Actions;

use App\Models\Department;
use App\Models\Employee;
use App\Services\LeaveBalanceService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportEmployeesAction
{
    /**
     * Builds the Import Employees Action using a wizard modal interface.
     */
    public static function make(): Action
    {
        return Action::make('import_employees')
            ->label('Import')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalWidth('3xl')
            ->form([
                Wizard::make([
                    self::getUploadStep(),
                    self::getMappingStep(),
                ]),
            ])
            ->action(fn (array $data) => self::processImport($data));
    }

    /**
     * Step 1: File Upload Configuration & Header Extraction
     */
    protected static function getUploadStep(): Step
    {
        return Step::make('Upload File')
            ->description('Upload an Excel or CSV file')
            ->icon('heroicon-o-document-arrow-up')
            ->schema([
                FileUpload::make('file')
                    ->label('Excel / CSV File')
                    ->disk('local')
                    ->directory('imports/employees')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $filePath = is_array($state) ? reset($state) : $state;
                        
                        if (! $filePath) {
                            $set('headers', '[]');
                            return;
                        }

                        try {
                            $path        = Storage::disk('local')->path($filePath);
                            $spreadsheet = IOFactory::load($path);
                            $sheet       = $spreadsheet->getActiveSheet();
                            $headers     = [];

                            foreach ($sheet->getRowIterator(1, 1) as $row) {
                                $cellIterator = $row->getCellIterator();
                                $cellIterator->setIterateOnlyExistingCells(true);
                                foreach ($cellIterator as $cell) {
                                    $val = trim((string) $cell->getValue());
                                    if ($val !== '') {
                                        $headers[] = $val;
                                    }
                                }
                            }
                            $set('headers', json_encode($headers));
                        } catch (\Exception $e) {
                            $set('headers', '[]');
                        }
                    }),

                Hidden::make('headers')->default('[]'),
            ]);
    }

    /**
     * Step 2: Dynamic Column Mapping Field Structure
     */
    protected static function getMappingStep(): Step
    {
        return Step::make('Map Columns')
            ->description('Match your columns to our fields')
            ->icon('heroicon-o-arrows-right-left')
            ->columns(2)
            ->schema(function (Get $get) {
                $headers = json_decode($get('headers') ?? '[]', true);
                $cols    = array_combine($headers, $headers) ?: [];
                $all     = ['_skip' => '— Skip —'] + $cols;

                return [
                    Select::make('map_name')->label('Name *')->options($cols)->required()->searchable(),
                    Select::make('map_email')->label('Email *')->options($cols)->required()->searchable(),
                    Select::make('map_phone')->label('Phone')->options($all)->default('_skip'),
                    Select::make('map_department')->label('Department')->options($all)->default('_skip')->helperText('Matched by name'),
                    Select::make('map_designation')->label('Designation')->options($all)->default('_skip'),
                    Select::make('map_basic_salary')->label('Basic Salary')->options($all)->default('_skip'),
                    Select::make('map_date_of_joining')->label('Date of Joining')->options($all)->default('_skip')->helperText('Format: YYYY-MM-DD'),
                    Select::make('map_pan_number')->label('PAN Number')->options($all)->default('_skip'),
                    Select::make('map_uan_number')->label('UAN Number')->options($all)->default('_skip'),
                    Select::make('map_esic_number')->label('ESIC Number')->options($all)->default('_skip'),
                    Select::make('map_bank_name')->label('Bank Name')->options($all)->default('_skip'),
                    Select::make('map_bank_account')->label('Bank Account')->options($all)->default('_skip'),
                    Select::make('map_ifsc_code')->label('IFSC Code')->options($all)->default('_skip'),
                ];
            });
    }

    /**
     * Core Data Processing Engine
     */
    protected static function processImport(array $data): void
    {
        $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
        if (! $file) return;

        $path = Storage::disk('local')->path($file);

        try {
            $spreadsheet = IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();
            $headers     = array_shift($rows); 
            $headerMap   = array_flip($headers);
        } catch (\Exception $e) {
            Notification::make()->title('Could not read file: ' . $e->getMessage())->danger()->send();
            return;
        }

        $imported    = 0;
        $skipped     = 0;
        $errors      = [];
        $departments = Department::pluck('id', 'name')->toArray();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            if (empty(array_filter($row))) continue;

            // Row value accessor utility closure
            $getValue = function (string $field) use ($row, $headerMap, $data): ?string {
                $col = $data["map_{$field}"] ?? '_skip';
                if ($col === '_skip') return null;
                $idx = $headerMap[$col] ?? null;
                return isset($idx) ? trim((string) ($row[$idx] ?? '')) : null;
            };

            $name  = $getValue('name');
            $email = $getValue('email');

            if (empty($name) || empty($email)) {
                $errors[] = "Row {$rowNum}: Target primary identity fields (Name/Email) cannot be blank.";
                $skipped++;
                continue;
            }

            if (Employee::where('email', $email)->exists()) {
                $errors[] = "Row {$rowNum}: Identity duplicate target record detected on email: '{$email}'.";
                $skipped++;
                continue;
            }

            $deptName = $getValue('department');
            $deptId   = $deptName ? ($departments[$deptName] ?? null) : null;

            if ($deptName && ! $deptId) {
                $errors[] = "Row {$rowNum}: Department reference entity matching rule configuration error '{$deptName}'.";
            }

            $employeeData = array_filter([
                'name'            => $name,
                'email'           => $email,
                'phone'           => $getValue('phone'),
                'department_id'   => $deptId,
                'designation'     => $getValue('designation') ?: 'Employee',
                'basic_salary'    => is_numeric($getValue('basic_salary')) ? (float) $getValue('basic_salary') : 0,
                'date_of_joining' => $getValue('date_of_joining') ?: now()->toDateString(),
                'pan_number'      => $getValue('pan_number'),
                'uan_number'      => $getValue('uan_number'),
                'esic_number'     => $getValue('esic_number'),
                'bank_name'       => $getValue('bank_name'),
                'bank_account'    => $getValue('bank_account'),
                'ifsc_code'       => $getValue('ifsc_code'),
                'status'          => 'active',
                'pay_frequency'   => 'monthly',
                'tax_regime'      => 'new',
            ], fn ($v) => $v !== null && $v !== '');

            try {
                $employee = Employee::withoutEvents(fn () => Employee::create($employeeData));
                app(LeaveBalanceService::class)->initializeForEmployee($employee);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: System exception executing transaction rollback state: " . $e->getMessage();
                $skipped++;
            }
        }

        Storage::disk('local')->delete($file);

        self::sendCompletionNotification($imported, $skipped, $errors);
    }

    /**
     * Renders completion summary layout out to the frontend notification tray
     */
    protected static function sendCompletionNotification(int $imported, int $skipped, array $errors): void
    {
        $body = "{$imported} records matched, {$skipped} skipped runtime exceptions.";
        if (! empty($errors)) {
            $body .= ' Operational Log Details: ' . implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $body .= ' ...and ' . (count($errors) - 5) . ' unresolved traces hidden.';
            }
        }

        Notification::make()
            ->title('Execution Stream Complete')
            ->body($body)
            ->color($skipped > 0 ? 'warning' : 'success')
            ->persistent()
            ->send();
    }
}