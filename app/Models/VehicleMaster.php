<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleMaster extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'variant_name', 'color_name',
        'fuel_type', 'transmission',
        'ex_showroom_price', 'min_stock', 'is_active',
        'battery_type', 'battery_make',
    ];
}
