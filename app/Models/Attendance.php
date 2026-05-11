<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table    = 'attendance';
    protected $fillable = ['employee_id','date','status','check_in','check_out','hours_worked','remarks'];
    protected $casts    = ['date'=>'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function isPresent(): bool { return in_array($this->status,['present','half_day']); }
}