<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    // Relationship
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}