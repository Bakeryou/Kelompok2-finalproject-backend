<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        foreach ($products as $product) {
            $product->image_url = url('uploads/images/' . $product->image);
        }
        return response()->json(['status' => 'success', 'data' => $products]);    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'price' => 'required|numeric',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'stock' => 'required|integer',
        'weight' => 'required|numeric',
        'description' => 'required|string',
        'category_id' => 'required|exists:categories,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
    }

    // Menyimpan gambar baru dalam variabel
    $imageName = time() . '.' . $request->image->getClientOriginalExtension();
    $request->image->move(public_path('uploads/images'), $imageName);

    // Membuat produk baru dengan data yang diterima dan menyimpan nama gambar baru
    $product = Product::create(array_merge($request->except('image'), ['image' => $imageName]));

    // Tambahkan URL lengkap untuk gambar produk
    $product->image_url = url('uploads/images/' . $product->image);

    return response()->json(['status' => 'success', 'data' => $product], 201);
}

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $product->image_url = url('uploads/images/' . $product->image);
        return response()->json(['status' => 'success', 'data' => $product]);
    }

    public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'price' => 'required|numeric',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Pastikan validasi gambar ditambahkan
        'stock' => 'required|integer',
        'weight' => 'required|numeric',
        'description' => 'required|string',
        'category_id' => 'required|exists:categories,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
    }

    // Hapus gambar lama jika ada gambar baru yang diunggah
    if ($request->hasFile('image')) {
        // Hapus gambar lama jika ada
        if ($product->image) {
            Storage::delete('public/uploads/images/' . $product->image);
        }
        // Simpan gambar baru dari variabel yang sudah ada
        $imageName = time() . '.' . $request->image->getClientOriginalExtension();
        $request->image->move(public_path('uploads/images'), $imageName);
        $product->image = $imageName;
    }

    $product->update($request->except('image')); // Menyimpan data kecuali gambar

    // Tambahkan URL lengkap untuk gambar produk
    $product->image_url = url('uploads/images/' . $product->image);
    
    return response()->json(['status' => 'success', 'data' => $product]);
}

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['status' => 'success', 'message' => 'Product deleted successfully']);
    }
}
