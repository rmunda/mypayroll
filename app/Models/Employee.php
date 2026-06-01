<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_code','name','email','phone',
        'department_id','pay_structure_id','weekly_off_rule_id','designation',
        'basic_salary','pay_frequency','bank_name','bank_account',
        'ifsc_code','pan_number','uan_number','esic_number',
        'date_of_joining','date_of_leaving','status','tax_regime','user_id',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
        'date_of_leaving' => 'date',
        'basic_salary'    => 'decimal:2',
    ];

    public function department()    { return $this->belongsTo(Department::class); }
    public function payStructure()  { return $this->belongsTo(PayStructure::class); }
    public function weeklyOffRule() { return $this->belongsTo(WeeklyOffRule::class); }
    public function paySlips()      { return $this->hasMany(PaySlip::class); }
    public function attendance()    { return $this->hasMany(Attendance::class); }
    public function leaves()        { return $this->hasMany(Leave::class); }
    public function user()          { return $this->belongsTo(User::class); }

    // Computed HRA
    public function getHraAttribute(): float
    {
        return $this->basic_salary * ($this->payStructure->hra_percentage / 100);
    }

    // Computed gross
    public function getGrossSalaryAttribute(): float
    {
        return $this->basic_salary + $this->hra + $this->payStructure->ta_fixed;
    }

    // Auto-generate employee code EMP-001
    protected static function booted(): void
    {
        static::creating(function (Employee $e) {
            if (empty($e->employee_code)) {
                $last = static::withTrashed()->latest('id')->first();
                $next = $last ? (intval(substr($last->employee_code, 4)) + 1) : 1;
                $e->employee_code = 'EMP-' . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        });

        // Auto create User account when Employee is created
        static::created(function (Employee $e) {
            $user = User::firstOrCreate(
                ['email' => $e->email],
                [
                    'name'      => $e->name,
                    'password'  => bcrypt('Welcome@123'), // default password
                    'is_active' => true,
                ]
            );

            // assign employee role
            $user->assignRole('employee');

            // link user to employee
             $e->updateQuietly(['user_id' => $user->id]);

            // initialize leave balances for new employee
            app(LeaveBalanceService::class)
                ->initializeForEmployee($e);

            // send welcome email
            Mail::to($e->email)
                ->send(new WelcomeMail($e, 'Welcome@123'));
        });
    }

}