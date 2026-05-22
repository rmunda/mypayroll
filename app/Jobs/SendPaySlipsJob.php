<?php
namespace App\Jobs;

use App\Models\PayrollRun;
use App\Services\PdfService;
use App\Mail\PaySlipMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPaySlipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PayrollRun $run) {}

    public function handle(PdfService $pdfService): void
    {
        $this->run->paySlips()->with('employee')->each(function ($slip) use ($pdfService) {
            $path = $pdfService->generatePaySlip($slip);
            $slip->update(['pdf_path' => $path]);
            Mail::to($slip->employee->email)->send(new PaySlipMail($slip, $path));
            $slip->update(['status' => 'sent', 'sent_at' => now()]);
        });

        $this->run->update(['status' => 'paid', 'paid_at' => now()]);
    }
}