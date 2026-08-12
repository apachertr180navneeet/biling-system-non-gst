<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'designation',
        'department',
        'joining_date',
        'salary_type',
        'basic_salary',
        'bank_name',
        'account_number',
        'ifsc_code',
        'address',
        'is_active',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function salarySlips()
    {
        return $this->hasMany(SalarySlip::class);
    }
}
