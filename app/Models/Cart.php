<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_qty',
        'total_weight',
        'subtotal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function deleteItems()
    {
        foreach ($this->items as $item) {
            $item->delete();
        }
    }

    public function calculateTotals()
    {
        $this->total_qty = $this->items->sum('qty');
        $this->total_weight = $this->items->sum('total_weight');
        $this->subtotal = $this->items->sum('total_price');
        $this->save();
    }
}
