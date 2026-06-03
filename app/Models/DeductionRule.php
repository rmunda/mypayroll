<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeductionRule extends Model
{
    use HasFactory;
    protected $fillable = ['name','type','value','applies_to','deduction_side','is_statutory','is_active'];
    protected $casts    = ['is_statutory'=>'boolean','is_active'=>'boolean','value'=>'decimal:4'];

    public function calculate(float $basic, float $gross): float
    {
        $base = match($this->applies_to) {
            'basic' => $basic,
            'gross' => $gross,
            default => 0,
        };
        return match($this->type) {
            'percentage' => round($base * ($this->value / 100), 2),
            'fixed'      => (float) $this->value,
            default      => 0,
        };
    }
}