<?php

namespace App\Filament\Resources\Attendances\Actions;

use App\Models\Attendance;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportAttendanceAction
{
    public static function make(): Action
    {
        return Action::make('import_attendance')
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

    protected static function getUploadStep(): Step
    {
        return Step::make('Upload File')
            ->description('Upload an Excel or CSV file')
            ->icon('heroicon-o-document-arrow-up')
            ->schema([
                FileUpload::make('file')
                    ->label('Excel / CSV File')
                    ->disk('local')
                    ->directory('imports/attendance')
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

                        if (!$filePath) { $set('headers', '[]'); return; }

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
                                    if ($val !== '') $headers[] = $val;
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
                    Select::make('map_employee_code')
                        ->label('Employee Code *')
                        ->options($cols)
                        ->required()
                        ->searchable()
                        ->helperText('Used to identify the employee'),

                    Select::make('map_date')
                        ->label('Date *')
                        ->options($cols)
                        ->required()
                        ->searchable()
                        ->helperText('Format: YYYY-MM-DD'),

                    Select::make('map_status')
                        ->label('Status *')
                        ->options($cols)
                        ->required()
                        ->searchable()
                        ->helperText('present / absent / half_day / on_leave / holiday'),

                    Select::make('map_check_in')
                        ->label('Check In')
                        ->options($all)
                        ->default('_skip')
                        ->helperText('Format: HH:MM'),

                    Select::make('map_check_out')
                        ->label('Check Out')
                        ->options($all)
                        ->default('_skip')
                        ->helperText('Format: HH:MM'),

                    Select::make('map_hours_worked')
                        ->label('Hours Worked')
                        ->options($all)
                        ->default('_skip'),

                    Select::make('map_remarks')
                        ->label('Remarks')
                        ->options($all)
                        ->default('_skip'),
                ];
            });
    }

    protected static function processImport(array $data): void
    {
        $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
        if (!$file) return;

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

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        // cache employees by code for fast lookup
        $employees = Employee::pluck('id', 'employee_code')->toArray();

        $validStatuses = ['present', 'absent', 'half_day', 'on_leave', 'holiday', 'weekend'];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            if (empty(array_filter($row))) continue;

            $getValue = function (string $field) use ($row, $headerMap, $data): ?string {
                $col = $data["map_{$field}"] ?? '_skip';
                if ($col === '_skip') return null;
                $idx = $headerMap[$col] ?? null;
                return isset($idx) ? trim((string) ($row[$idx] ?? '')) : null;
            };

            $employeeCode = $getValue('employee_code');
            $date         = $getValue('date');
            $status       = $getValue('status');

            // validate required fields
            if (empty($employeeCode)) {
                $errors[] = "Row {$rowNum}: Employee Code is required — skipped.";
                $skipped++; continue;
            }

            if (empty($date)) {
                $errors[] = "Row {$rowNum}: Date is required — skipped.";
                $skipped++; continue;
            }

            if (empty($status)) {
                $errors[] = "Row {$rowNum}: Status is required — skipped.";
                $skipped++; continue;
            }

            // validate employee exists
            $employeeId = $employees[$employeeCode] ?? null;
            if (!$employeeId) {
                $errors[] = "Row {$rowNum}: Employee code '{$employeeCode}' not found — skipped.";
                $skipped++; continue;
            }

            // validate status value
            $status = strtolower($status);
            if (!in_array($status, $validStatuses)) {
                $errors[] = "Row {$rowNum}: Invalid status '{$status}' — skipped.";
                $skipped++; continue;
            }

            // validate date format
            try {
                $parsedDate = \Carbon\Carbon::parse($date)->toDateString();
            } catch (\Exception) {
                $errors[] = "Row {$rowNum}: Invalid date '{$date}' — skipped.";
                $skipped++; continue;
            }

            try {
                // upsert — update if exists, create if not
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date'        => $parsedDate,
                    ],
                    array_filter([
                        'status'       => $status,
                        'check_in'     => $getValue('check_in'),
                        'check_out'    => $getValue('check_out'),
                        'hours_worked' => $getValue('hours_worked'),
                        'remarks'      => $getValue('remarks'),
                    ], fn ($v) => $v !== null && $v !== '')
                );

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: " . $e->getMessage() . ' — skipped.';
                $skipped++;
            }
        }

        Storage::disk('local')->delete($file);

        $body = "{$imported} records imported, {$skipped} skipped.";
        if (!empty($errors)) {
            $body .= ' Issues: ' . implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $body .= ' ...and ' . (count($errors) - 5) . ' more.';
        }

        Notification::make()
            ->title('Import complete')
            ->body($body)
            ->color($skipped > 0 ? 'warning' : 'success')
            ->persistent()
            ->send();
    }
}
