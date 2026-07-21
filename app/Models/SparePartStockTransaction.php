<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartStockTransaction extends Model
{
    protected $fillable = [
        'spare_part_id',
        'transaction_type',
        'quantity',
        'reference_no',
        'notes',
    ];

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'spare_part_id');
    }
}
