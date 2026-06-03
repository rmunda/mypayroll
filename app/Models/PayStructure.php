<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayStructure extends Model
{
    use HasFactory;
    protected $fillable = ['name','hra_percentage','ta_fixed','special_allowance_pct','is_default'];
    protected $casts    = ['is_default'=>'boolean'];

    public function employees() { return $this->hasMany(Employee::class); }
}
