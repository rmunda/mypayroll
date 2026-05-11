<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayStructure extends Model
{
    protected $fillable = ['name','hra_percentage','ta_fixed','special_allowance_pct','is_default'];
    protected $casts    = ['is_default'=>'boolean'];

    public function employees() { return $this->hasMany(Employee::class); }
}
