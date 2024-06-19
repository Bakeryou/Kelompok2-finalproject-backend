<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        if (Auth::user()->role == 'admin') {
            $orders = Order::with('user', 'cart.items.product')->get();
        } else {
            $orders = Order::with('user', 'cart.items.product')->where('user_id', Auth::id())->get();
        }
        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with('user', 'cart.items.product')->findOrFail($id);
        return response()->json($order);
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_type' => 'required|in:pickup,delivery',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|string|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:255',
            'customer_postal_code' => 'required|string|max:20',
            'customer_city' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'shipping' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        // Ambil atau buat pengguna
        $user = $request->user();

        // Update data pengguna berdasarkan input dari formulir checkout
        $user->name = $request->input('customer_name');
        $user->email = $request->input('customer_email');
        $user->phone_number = $request->input('customer_phone');
        $user->address = $request->input('customer_address');
        $user->postal_code = $request->input('customer_postal_code');
        $user->city = $request->input('customer_city');
        $user->save();

        $cart = Cart::where('user_id', Auth::id())->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Cart is empty'], 400);
        }

        $customerCity = $user->city;
        if ($request->order_type == 'delivery' && strtolower($customerCity) !== 'surabaya') {
            return response()->json(['status' => 'error', 'message' => 'Delivery is only available for Surabaya area'], 400);
        }
        
        $order = Order::create([
            'user_id' => $user->id,
            'cart_id' => $cart->id,
            'order_number' => 'ORD' . strtoupper(substr(md5(random_bytes(10)), 0, 6)),
            'order_type' => $request->order_type,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone_number,
            'customer_address' => $user->address,
            'customer_postal_code' => $user->postal_code,
            'customer_city' => $user->city,
            'notes' => $request->notes,
            'subtotal' => $request->subtotal,
            'tax' => $request->tax,
            'shipping' => $request->shipping,
            'total' => $request->total,
            'status' => 'process',
        ]);

        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;
            if ($product->stock < $cartItem->qty) {
                return response()->json(['status' => 'error', 'message' => 'Insufficient stock for product ' . $product->name], 400);
            }
            $product->update(['stock' => $product->stock - $cartItem->qty]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $cartItem->qty,
                'price' => $cartItem->price,
                'total' => $cartItem->qty * $cartItem->price,
            ]);
        }

        $cart->items()->delete(); // Hapus item-item di dalam cart
        $cart->delete();

        return response()->json(['status' => 'success', 'order' => $order]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:process,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json($order);
    }
}
