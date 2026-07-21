<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleSalesInvoice extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_sales_invoices';

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'customer_id',
        'customer_name',
        'customer_age',
        'customer_occupation',
        'customer_mobile',
        'customer_address',
        'customer_residence_phone',
        'vehicle_inventory_id',
        'rate',
        'sub_total',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'total',
        'nemmp_incentive',
        'discount',
        'grand_total',
        'igst_amount',
        'payment_mode',
        'finance_name',
        'tax_regime',
        'received_amount',
        'balance',
        'previous_balance',
        'current_balance',
        'warranty_notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicleInventory(): BelongsTo
    {
        return $this->belongsTo(VehicleInventory::class, 'vehicle_inventory_id');
    }
}
