<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySlipAdvanceDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_slip_id',
        'employee_advance_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function salarySlip()
    {
        return $this->belongsTo(SalarySlip::class);
    }

    public function employeeAdvance()
    {
        return $this->belongsTo(EmployeeAdvance::class, 'employee_advance_id');
    }
}
