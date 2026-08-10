<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_type',
        'bill_type',
        'bill_id',
        'party_type',
        'party_id',
        'party_name',
        'payment_date',
        'amount',
        'payment_mode',
        'type',
        'rollback_reason',
        'reversed_payment_id',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'party_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedPayment()
    {
        return $this->belongsTo(PaymentTransaction::class, 'reversed_payment_id');
    }

    public function rollbacks()
    {
        return $this->hasMany(PaymentTransaction::class, 'reversed_payment_id');
    }

    public function isRolledBack()
    {
        return $this->rollbacks()->exists();
    }
}
