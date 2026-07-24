<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartSalesInvoice extends Model
{
    use SoftDeletes;

    protected $table = 'part_sales_invoices';

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'customer_id',
        'customer_name',
        'customer_mobile',
        'customer_address',
        'customer_gstin',
        'customer_pan',
        'place_of_supply',
        'tax_regime',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'round_off',
        'total_amount',
        'received_amount',
        'balance',
        'payment_mode',
        'previous_balance',
        'current_balance',
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

    public function items(): HasMany
    {
        return $this->hasMany(PartSalesInvoiceItem::class, 'part_sales_invoice_id');
    }
}
