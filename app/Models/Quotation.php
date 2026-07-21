<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'quotation_number',
        'quotation_date',
        'customer_id',
        'customer_name',
        'customer_mobile',
        'customer_address',
        'customer_gstin',
        'customer_pan',
        'place_of_supply',
        'tax_regime',
        'vehicle_master_id',
        'rate',
        'sub_total',
        'cgst_rate',
        'sgst_rate',
        'igst_rate',
        'nemmp_incentive',
        'discount',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'round_off',
        'total_amount',
        'remarks',
        'created_by',
        'is_active',

        // Vehicle Technical Specs
        'model_maker_name',
        'gross_weight',
        'charging_time',
        'performance',
        'charger_output',
        'motor_output',
        'seating_capacity',
        'type_of_break',
        'roof_top_abs',
        'front_fiber_wind_shield',
        'meter_type',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $dateStr = date('Ymd');
                $prefix = 'QT-' . $dateStr . '-';

                $quotation->quotation_number = DB::transaction(function () use ($prefix) {
                    $lastQuotation = static::where('quotation_number', 'like', $prefix . '%')
                        ->lockForUpdate()
                        ->orderBy('id', 'desc')
                        ->first();

                    $nextNumber = 1;
                    if ($lastQuotation) {
                        $parts = explode('-', $lastQuotation->quotation_number);
                        $lastNum = (int) end($parts);
                        $nextNumber = $lastNum + 1;
                    }

                    return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                });
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicleMaster(): BelongsTo
    {
        return $this->belongsTo(VehicleMaster::class, 'vehicle_master_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
