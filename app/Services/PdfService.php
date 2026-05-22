<?php
namespace App\Services;

use App\Models\PaySlip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function generatePaySlip(PaySlip $slip): string
    {
        $slip->load('employee.department','employee.payStructure','payrollRun');

        $pdf  = Pdf::loadView('pdf.payslip', compact('slip'))
                   ->setPaper('a4','portrait');

        $dir  = 'payslips/' . $slip->payrollRun->period_start->format('Y-m');
        $file = $dir . '/' . $slip->employee->employee_code . '.pdf';

        Storage::disk('local')->put($file, $pdf->output());

        return $file;
    }
}