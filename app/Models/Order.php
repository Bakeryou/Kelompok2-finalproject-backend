<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cart_id',
        'order_number',
        'order_type',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_postal_code',
        'customer_city',
        'notes',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'status',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function calculateTotals()
    {
        $this->subtotal = $this->cart->items->sum('total_price');
        $this->tax = $this->subtotal * 0.1; // 10% tax

        if ($this->order_type == 'delivery') {
            $this->shipping = $this->cart->total_weight > 1000? 25000 : 15000; // shipping cost based on weight
        } else {
            $this->shipping = 0; // no shipping cost for pickup
        }

        $this->total = $this->subtotal + $this->tax + $this->shipping;
        $this->save();
    }
}
