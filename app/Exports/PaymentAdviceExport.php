<?php

namespace App\Exports;

use App\Models\PayrollRun;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PaymentAdviceExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected int $srNo = 0;

    public function __construct(
        protected PayrollRun $run,
        protected string     $bankFormat = 'standard'
    ) {}

    public function collection()
    {
        return $this->run
                    ->paySlips()
                    ->with('employee')
                    ->where('status', 'approved')
                    ->get()
                    ->filter(fn($slip) =>
                        // only include employees with bank details
                        !empty($slip->employee->bank_account) &&
                        !empty($slip->employee->ifsc_code)
                    );
    }

    public function headings(): array
    {
        return match($this->bankFormat) {

            'hdfc' => [
                'Beneficiary Name',
                'Account Number',
                'IFSC Code',
                'Amount',
                'Remarks',
            ],

            'icici' => [
                'SR NO',
                'Beneficiary Name',
                'Account Number',
                'IFSC Code',
                'Amount',
                'Payment Type',
                'Remarks',
            ],

            'sbi' => [
                'Account Number',
                'IFSC Code',
                'Amount',
                'Payee Name',
                'Payment Reference',
                'Remarks',
            ],

            default => [
                'SR No',
                'Employee Code',
                'Employee Name',
                'Bank Name',
                'Account Number',
                'IFSC Code',
                'Amount (INR)',
                'Payment Mode',
                'Reference',
                'Remarks',
            ],
        };
    }

    public function map($slip): array
    {
        $this->srNo++;

        $reference = 'SAL/'
            . $slip->employee->employee_code
            . '/'
            . now()->format('MY');

        $remarks = 'Salary ' . $this->run->period_label;
        $amount  = number_format($slip->net_pay, 2, '.', '');

        return match($this->bankFormat) {

            'hdfc' => [
                $slip->employee->name,
                $slip->employee->bank_account,
                $slip->employee->ifsc_code,
                $amount,
                $remarks,
            ],

            'icici' => [
                $this->srNo,
                $slip->employee->name,
                $slip->employee->bank_account,
                $slip->employee->ifsc_code,
                $amount,
                'NEFT',
                $remarks,
            ],

            'sbi' => [
                $slip->employee->bank_account,
                $slip->employee->ifsc_code,
                $amount,
                $slip->employee->name,
                $reference,
                $remarks,
            ],

            default => [
                $this->srNo,
                $slip->employee->employee_code,
                $slip->employee->name,
                $slip->employee->bank_name,
                $slip->employee->bank_account,
                $slip->employee->ifsc_code,
                $amount,
                'NEFT',
                $reference,
                $remarks,
            ],
        };
    }

    public function styles(Worksheet $sheet): array
    {
        return [
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
        ];
    }

    public function title(): string
    {
        return 'Payment Advice';
    }
}