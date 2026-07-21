<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehiclePurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = ['po_number', 'supplier_id', 'order_date', 'expected_date', 'notes', 'status', 'total_amount', 'received_amount', 'balance', 'is_active'];

    protected function casts(): array
    {
        return ['order_date' => 'date', 'expected_date' => 'date'];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VehiclePoItem::class, 'vehicle_po_id');
    }
}
