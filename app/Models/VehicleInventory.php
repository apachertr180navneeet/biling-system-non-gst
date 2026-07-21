<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleInventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_po_id', 'vehicle_description', 'chassis_number', 'engine_number',
        'color_name', 'mfg_year', 'quantity', 'purchase_price', 'status', 'is_active',
        'motor_number', 'battery_number', 'charger_number', 'controller_number',
        'convertor_number', 'manual_number'
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(VehiclePurchaseOrder::class, 'vehicle_po_id');
    }

    public function vehicleSalesInvoices()
    {
        return $this->hasMany(VehicleSalesInvoice::class, 'vehicle_inventory_id');
    }
}
