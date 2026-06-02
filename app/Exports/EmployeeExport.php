<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return Employee::query()->with('department')->orderBy('employee_code');
    }

    public function headings(): array
    {
        return [
            'Name', 'Email', 'Phone', 'Department', 'Designation',
            'Basic Salary', 'Pay Frequency', 'Date of Joining', 'Status',
            'Tax Regime', 'PAN Number', 'UAN Number', 'ESIC Number',
            'Bank Name', 'Bank Account', 'IFSC Code',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->name,
            $employee->email,
            $employee->phone,
            $employee->department?->name,
            $employee->designation,
            $employee->basic_salary,
            $employee->pay_frequency,
            $employee->date_of_joining?->format('Y-m-d'),
            $employee->status,
            $employee->tax_regime,
            $employee->pan_number,
            $employee->uan_number,
            $employee->esic_number,
            $employee->bank_name,
            $employee->bank_account,
            $employee->ifsc_code,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
