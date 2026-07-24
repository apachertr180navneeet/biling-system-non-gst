<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'name', 'company_name', 'phone', 'email',
        'address', 'state', 'gstin', 'pan_no', 'aadhaar_no', 'is_active',
    ];

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name ?? '', 2)[0] ?? $this->name;
    }

    public function getLastNameAttribute()
    {
        $parts = explode(' ', $this->name ?? '', 2);
        return $parts[1] ?? '';
    }
}
