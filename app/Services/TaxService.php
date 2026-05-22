<?php
namespace App\Services;

use App\Models\Employee;

class TaxService
{
    // Monthly TDS = Annual tax / 12
    // FY 2025-26 slabs used
    public function calculateMonthlyTDS(Employee $emp, float $annualGross): float
    {
        $tax = $emp->tax_regime === 'new'
             ? $this->newRegimeTax($annualGross)
             : $this->oldRegimeTax($annualGross, $emp->basic_salary * 0.12 * 12);

        // 4% health & education cess
        return round(($tax * 1.04) / 12, 2);
    }

    // New regime FY 2025-26
    // 0-3L: 0% | 3-7L: 5% | 7-10L: 10% | 10-12L: 15% | 12-15L: 20% | 15L+: 30%
    protected function newRegimeTax(float $income): float
    {
        $income -= 75000; // standard deduction
        if ($income <= 0)      return 0;
        if ($income <= 700000) return 0; // rebate u/s 87A

        $tax = 0;
        foreach ([
            [300000, 700000,    0.05],
            [700000, 1000000,   0.10],
            [1000000,1200000,   0.15],
            [1200000,1500000,   0.20],
            [1500000,PHP_INT_MAX,0.30],
        ] as [$min,$max,$rate]) {
            if ($income > $min) $tax += (min($income,$max) - $min) * $rate;
        }
        return $tax;
    }

    // Old regime
    protected function oldRegimeTax(float $income, float $pf): float
    {
        $taxable = max(0, $income - min($pf + 50000, 150000) - 50000);
        if ($taxable <= 250000) return 0;

        $tax = 0;
        foreach ([
            [250000, 500000,     0.05],
            [500000, 1000000,    0.20],
            [1000000,PHP_INT_MAX,0.30],
        ] as [$min,$max,$rate]) {
            if ($taxable > $min) $tax += (min($taxable,$max) - $min) * $rate;
        }
        return $tax;
    }
}