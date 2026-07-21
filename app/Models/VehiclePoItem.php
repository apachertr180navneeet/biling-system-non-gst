<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiclePoItem extends Model
{
    protected $fillable = ['vehicle_po_id', 'vehicle_description', 'color_name', 'mfg_year', 'quantity', 'unit_price', 'total_price', 'received_quantity'];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(VehiclePurchaseOrder::class, 'vehicle_po_id');
    }
}
