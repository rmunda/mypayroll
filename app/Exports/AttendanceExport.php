<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to,
    ) {}

    public function query()
    {
        return Attendance::query()
            ->with(['employee.department'])
            ->whereBetween('date', [$this->from, $this->to])
            ->orderBy('date')
            ->orderBy('employee_id');
    }

    public function headings(): array
    {
        return [
            'Employee Code', 'Employee Name', 'Department',
            'Date', 'Status', 'Check In', 'Check Out',
            'Hours Worked', 'Remarks',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->employee?->employee_code,
            $attendance->employee?->name,
            $attendance->employee?->department?->name,
            $attendance->date?->format('Y-m-d'),
            $attendance->status,
            $attendance->check_in,
            $attendance->check_out,
            $attendance->hours_worked,
            $attendance->remarks,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
