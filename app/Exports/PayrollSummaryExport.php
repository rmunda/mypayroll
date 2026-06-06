<?php

namespace App\Exports;

use App\Models\PayrollRun;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PayrollSummaryExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithColumnFormatting
{
    protected int $srNo = 0;

    public function __construct(protected PayrollRun $run) {}

    public function collection()
    {
        return $this->run
                    ->paySlips()
                    ->with('employee.department')
                    ->get();
    }

    public function headings(): array
    {
        return [
            'Sr No',
            'Employee Code',
            'Employee Name',
            'Department',
            'Designation',
            'Bank Name',
            'Account No',
            'IFSC Code',
            'Working Days',
            'Present Days',
            'Leave Days',
            'Absent Days',
            'Basic',
            'HRA',
            'Transport Allow.',
            'Special Allow.',
            'Bonus',
            'Arrears',
            'Gross Earnings',
            'PF (Employee)',
            'PF (Employer)',
            'ESI (Employee)',
            'ESI (Employer)',
            'Prof. Tax',
            'TDS',
            'Other Deductions',
            'Total Deductions',
            'Net Pay',
            'Status',
        ];
    }

    public function map($slip): array
    {
        $this->srNo++;

        return [
            $this->srNo,
            $slip->employee->employee_code,
            $slip->employee->name,
            $slip->employee->department->name ?? '',
            $slip->employee->designation,
            $slip->employee->bank_name,
            $slip->employee->bank_account,
            $slip->employee->ifsc_code,
            $slip->working_days,
            $slip->present_days,
            $slip->leave_days,
            $slip->absent_days,
            $slip->basic,
            $slip->hra,
            $slip->transport_allowance,
            $slip->special_allowance,
            $slip->bonus,
            $slip->arrears,
            $slip->gross_earnings,
            $slip->pf_employee,
            $slip->pf_employer,
            $slip->esi_employee,
            $slip->esi_employer,
            $slip->professional_tax,
            $slip->tds,
            $slip->other_deductions,
            $slip->total_deductions,
            $slip->net_pay,
            ucfirst($slip->status),
        ];
    }

    public function columnFormats(): array
    {
        $money = '#,##0.00';

        return [
            'M'  => $money, // Basic
            'N'  => $money, // HRA
            'O'  => $money, // Transport
            'P'  => $money, // Special
            'Q'  => $money, // Bonus
            'R'  => $money, // Arrears
            'S'  => $money, // Gross
            'T'  => $money, // PF Employee
            'U'  => $money, // PF Employer
            'V'  => $money, // ESI Employee
            'W'  => $money, // ESI Employer
            'X'  => $money, // Prof Tax
            'Y'  => $money, // TDS
            'Z'  => $money, // Other
            'AA' => $money, // Total Deductions
            'AB' => $money, // Net Pay
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        return [
            // header row
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '185FA5'],
                ],
            ],

            // totals row at bottom
            "M{$lastRow}:AB{$lastRow}" => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EAF3DE'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Payroll Summary';
    }
}