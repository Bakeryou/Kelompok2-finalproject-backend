<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'qty',
        'price',
        'total_price',
        'weight',
        'total_weight',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function boot()
    {
        parent::boot();

        static::saved(function ($item) {
            $item->cart->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->cart->calculateTotals();
        });
    }
}
