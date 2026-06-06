<?php

namespace App\Imports;

use App\Models\PayrollRun;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankStatementImport implements ToCollection, WithHeadingRow
{
    public array $results = [
        'matched'    => 0,
        'unmatched'  => 0,
        'mismatched' => [],
    ];

    // Common column aliases by bank statement type
    private const ACCOUNT_KEYS = [
        'account_number', 'account_no', 'accountnumber', 'accountno',
        'beneficiary_account', 'beneficiary_account_number',
        'acc_no', 'account', 'credit_account',
    ];

    private const AMOUNT_KEYS = [
        'amount', 'credit_amount', 'net_amount', 'transfer_amount',
        'credited_amount', 'paid_amount', 'transaction_amount', 'txn_amount',
    ];

    private const REFERENCE_KEYS = [
        'utr', 'utr_number', 'utr_no', 'reference', 'reference_number',
        'ref_no', 'transaction_id', 'txn_id', 'rrn', 'bank_reference',
        'transaction_reference',
    ];

    private const STATUS_KEYS = [
        'status', 'transaction_status', 'payment_status',
        'transfer_status', 'credit_status', 'txn_status',
    ];

    public function __construct(protected PayrollRun $run) {}

    public function collection(Collection $rows): void
    {
        // Build lookup: cleaned_bank_account → PaySlip (only for this payroll run)
        $slipsByAccount = $this->run
            ->paySlips()
            ->with('employee')
            ->get()
            ->keyBy(fn ($slip) => $this->cleanAccount($slip->employee->bank_account ?? ''))
            ->filter(fn ($slip, $account) => $account !== '');

        foreach ($rows as $row) {
            $account   = $this->cleanAccount((string) ($this->pick($row, self::ACCOUNT_KEYS) ?? ''));
            $amount    = $this->pick($row, self::AMOUNT_KEYS);
            $reference = $this->pick($row, self::REFERENCE_KEYS);
            $status    = strtolower((string) ($this->pick($row, self::STATUS_KEYS) ?? ''));

            if ($account === '') {
                $this->results['unmatched']++;
                continue;
            }

            $slip = $slipsByAccount->get($account);

            if (! $slip) {
                $this->results['unmatched']++;
                continue;
            }

            // Bank explicitly reported a failure
            if ($this->isFailed($status)) {
                $slip->update([
                    'payment_status'         => 'failed',
                    'payment_reference'      => $reference,
                    'payment_failure_reason' => "Bank reported: {$status}",
                ]);
                $this->results['mismatched'][] = [
                    'employee' => $slip->employee->name,
                    'account'  => $account,
                    'reason'   => "Transfer failed — bank status: {$status}",
                ];
                continue;
            }

            // Amount mismatch — tolerance ±1 rupee to absorb rounding differences
            if ($amount !== null && abs((float) $amount - (float) $slip->net_pay) > 1.00) {
                $slip->update([
                    'payment_status'         => 'paid',
                    'payment_reference'      => $reference,
                    'paid_at'                => now(),
                    'payment_failure_reason' => "Amount mismatch: expected ₹{$slip->net_pay}, bank shows ₹{$amount}",
                ]);
                $this->results['mismatched'][] = [
                    'employee' => $slip->employee->name,
                    'account'  => $account,
                    'reason'   => "Amount mismatch: expected ₹{$slip->net_pay}, bank shows ₹{$amount}",
                ];
                $this->results['matched']++;
                continue;
            }

            // Clean match — mark paid and clear any prior failure note
            $slip->update([
                'payment_status'         => 'paid',
                'payment_reference'      => $reference,
                'paid_at'                => now(),
                'payment_failure_reason' => null,
            ]);
            $this->results['matched']++;
        }
    }

    // Try each alias against the row, normalising both sides to handle spacing/case differences
    private function pick(Collection $row, array $keys): mixed
    {
        $rowNormalised = $row->keys()->map(fn ($k) => $this->normalise($k))->values();

        foreach ($keys as $key) {
            $index = $rowNormalised->search($this->normalise($key));
            if ($index !== false) {
                $value = $row->values()->get($index);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    // Collapse spaces/dashes/underscores and lowercase — used for column-name matching
    private function normalise(string $key): string
    {
        return strtolower(preg_replace('/[\s\-_]/', '', $key));
    }

    // Strip spaces and dashes from account numbers before comparing
    private function cleanAccount(string $account): string
    {
        return preg_replace('/[\s\-]/', '', $account);
    }

    private function isFailed(string $status): bool
    {
        return in_array($status, ['failed', 'failure', 'rejected', 'bounced', 'error', 'reversed', 'returned']);
    }
}
