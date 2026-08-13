<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAdvance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'advance_number',
        'employee_id',
        'advance_date',
        'amount',
        'deducted_amount',
        'remaining_amount',
        'payment_mode',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
        'deducted_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salarySlipDeductions()
    {
        return $this->hasMany(SalarySlipAdvanceDeduction::class, 'employee_advance_id');
    }
}
