<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartSalesInvoiceItem extends Model
{
    protected $table = 'part_sales_invoice_items';

    protected $fillable = [
        'part_sales_invoice_id',
        'spare_part_id',
        'quantity',
        'rate',
        'tax_percentage',
        'tax_amount',
        'amount',
        'serial_no_warranty_notes',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PartSalesInvoice::class, 'part_sales_invoice_id');
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'spare_part_id');
    }
}
