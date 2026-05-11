<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = [
        'period_label','period_start','period_end','status',
        'total_gross','total_deductions','total_net','processed_by','approved_at','paid_at',
    ];
    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'approved_at'  => 'datetime',
        'paid_at'      => 'datetime',
    ];

    public function paySlips()    { return $this->hasMany(PaySlip::class); }
    public function processedBy() { return $this->belongsTo(User::class,'processed_by'); }

    public function isDraft():    bool { return $this->status === 'draft'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isPaid():     bool { return $this->status === 'paid'; }
}