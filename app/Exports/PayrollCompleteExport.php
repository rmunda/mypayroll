<?php

namespace App\Exports;

use App\Models\PayrollRun;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PayrollCompleteExport implements WithMultipleSheets
{
    public function __construct(
        protected PayrollRun $run,
        protected string     $bankFormat = 'standard'
    ) {}

    public function sheets(): array
    {
        return [
            new PaymentAdviceExport($this->run, $this->bankFormat),
            new PayrollSummaryExport($this->run),
        ];
    }
}