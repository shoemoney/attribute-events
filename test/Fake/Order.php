<?php

namespace Kleemans\Test\Fake;

use Illuminate\Database\Eloquent\Model;
use Kleemans\AttributeEvents;

class Order extends Model
{
    use AttributeEvents;

    protected $attributes = [
        'status' => 'processing',
        'shipping_address' => '',
        'note' => '',
        'total' => 0.00,
        'paid_amount' => 0.00,
        'discount_percentage' => 0,
        'tax_free' => false,
        'payment_gateway' => 'credit_card'
    ];

    protected $casts = [
        'total' => 'float',
        'paid_amount' => 'float',
        'discount_percentage' => 'integer',
        'tax_free' => 'boolean',
    ];

    protected $dispatchesEvents = [
        'created' => Events\OrderPlaced::class,
        'updated' => Events\OrderUpdated::class,
        'deleted' => Events\OrderDeleted::class,
        'note:*' => Events\OrderNoteUpdated::class,
        'status:shipped' => Events\OrderShipped::class,
        'status:canceled' => Events\OrderCanceled::class,
        'status:returned' => Events\OrderReturned::class,
        'discount_percentage:100' => Events\OrderMadeFree::class,
        'paid_amount:2.99' => Events\OrderPaidHandlingFee::class,
        'tax_free:true' => Events\OrderTaxCleared::class,
        'shipping_country:*' => Events\OrderShippingCountryChanged::class,
        'is_paid:true' => Events\OrderPaid::class,
        'payment_gateway:cash' => Events\OrderPaidWithCash::class,
    ];

    public function getShippingCountryAttribute(): string
    {
        return substr($this->shipping_address, -2); // Last 2 characters of the address contain the country code.
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->paid_amount >= $this->total;
    }

    public function getPaymentGatewayAttribute($value): string
    {
        $cashGateways = ['cash', 'direct', 'invoice'];
        if (in_array($value, $cashGateways)) {
            return 'cash';
        }

        return $value;
    }
}
