<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'image',
        'stock',
        'weight',
        'description',
        'category_id', // Menambahkan category_id ke fillable agar dapat diset secara massal
    ];

    // public function storeImage($image)
    // {
    //     $imageName = time() . '.' . $image->getClientOriginalExtension();
    //     $image->move(public_path('uploads/images'), $imageName);
    //     $this->image = $imageName;
    //     $this->save();
    // }

    // Relasi dengan Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
