<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = [
        'employee_id','leave_type_id','from_date','to_date','days','reason','status','approved_by','approved_at',
    ];
    protected $casts = ['from_date'=>'date','to_date'=>'date','approved_at'=>'datetime'];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function approvedBy() { return $this->belongsTo(User::class,'approved_by'); }
}