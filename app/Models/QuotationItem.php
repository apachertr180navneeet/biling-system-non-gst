<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'spare_part_id',
        'quantity',
        'rate',
        'tax_percentage',
        'tax_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'amount',
        'serial_no_warranty_notes',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'spare_part_id');
    }
}
