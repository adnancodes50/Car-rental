<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentPurchase extends Model
{
    // Use the real table name
    protected $table = 'equipment_purchase';

    // Allow mass assignment for all columns you create via the controller/migrations
    protected $fillable = [
        'equipment_id',
        'customer_id',
        'location_id',
        'quantity',
        'total_price',
        'deposit_expected',
        'deposit_paid',
        'payment_status',
        'payment_method',
        'stripe_payment_intent_id',
        'stripe_payment_method_id',
        'stripe_charge_id',
        'card_brand',
        'card_last4',
        'card_exp_month',
        'card_exp_year',
        'receipt_url',
        'payfast_payment_id',
        'stock_before',
        'stock_after',
    ];

    // Put types here (not in $fillable)
    protected $casts = [
        'equipment_id'     => 'integer',
        'customer_id'      => 'integer',
        'location_id'      => 'integer',
        'quantity'         => 'integer',
        'total_price'      => 'float',
        'deposit_expected' => 'float',
        'deposit_paid'     => 'float',
        'stock_before'     => 'integer',
        'stock_after'      => 'integer',
    ];

    /* ---------------- Relations ---------------- */
    public function equipment() { return $this->belongsTo(Equipment::class, 'equipment_id'); }
    public function customer()  { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function location()  { return $this->belongsTo(Location::class, 'location_id'); }

    /* ---------------- Helpers used by mailers/UI ---------------- */
    public function itemDisplayName(): string
    {
        return $this->equipment?->name ?? ('Equipment #'.$this->equipment_id);
    }

    public function amountPaidNow(): float
    {
        return (float) ($this->deposit_paid ?? 0);
    }

    public function getRequiredDepositAttribute(): float
    {
        return (float) ($this->deposit_expected ?? $this->equipment?->deposit_amount ?? 0);
    }
}
