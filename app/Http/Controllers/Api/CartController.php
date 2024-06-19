<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())->with('items.product')->first();
        return response()->json(['status' => 'success', 'data' => $cart]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $product = Product::findOrFail($request->product_id);
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        $cartItem = $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'qty' => $request->qty,
                'price' => $product->price,
                'total_price' => $product->price * $request->qty,
                'weight' => $product->weight,
                'total_weight' => $product->weight * $request->qty,
            ]
        );

        $cart->calculateTotals();

        return response()->json(['status' => 'success', 'data' => $cartItem]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $cartItem = CartItem::findOrFail($id);
        $cartItem->qty = $request->qty;
        $cartItem->total_weight = $cartItem->weight * $request->qty;
        $cartItem->total_price = $cartItem->price * $request->qty;
        $cartItem->save();

        $cartItem->cart->calculateTotals();

        return response()->json(['status' => 'success', 'data' => $cartItem]);
    }

    public function destroy($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cart = $cartItem->cart;
        $cartItem->delete();

        $cart->calculateTotals();

        return response()->json(['status' => 'success', 'message' => 'Item removed from cart']);
    }
}
