<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'slip_number',
        'employee_id',
        'month',
        'year',
        'total_days',
        'present_days',
        'absent_days',
        'half_days',
        'paid_leaves',
        'basic_salary',
        'earned_salary',
        'allowances',
        'deductions',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_mode',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'earned_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'present_days' => 'float',
        'absent_days' => 'float',
        'paid_leaves' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
