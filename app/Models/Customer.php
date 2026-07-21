<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'first_name', 'last_name', 'company_name', 'phone', 'email',
        'address', 'state', 'gstin', 'pan_no', 'aadhaar_no', 'is_active',
    ];
}
