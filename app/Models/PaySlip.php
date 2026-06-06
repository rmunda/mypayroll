<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaySlip extends Model
{
    use HasFactory;
    protected $fillable = [
        'payroll_run_id','employee_id','working_days','present_days','leave_days','absent_days',
        'basic','hra','transport_allowance','special_allowance','bonus','arrears','gross_earnings',
        'pf_employee','pf_employer','esi_employee','esi_employer','professional_tax',
        'tds','other_deductions','total_deductions','net_pay',
        'status','pdf_path','sent_at','deduction_snapshot',
        'payment_reference','payment_mode','payment_status','paid_at','payment_failure_reason',
    ];
    protected $casts = ['deduction_snapshot'=>'array','sent_at'=>'datetime','paid_at'=>'datetime'];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function payrollRun() { return $this->belongsTo(PayrollRun::class); }
}