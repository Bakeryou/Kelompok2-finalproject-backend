<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $token = auth()->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($token)
        {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Successfully logged in',
                ],
                'data' => [
                    'user' => auth()->user(),
                    'access_token' => [
                        'token' => $token,
                        'type' => 'Bearer',
                        'expires_in' => Auth::factory()->getTTL() * 1,
                    ],
                ],
            ]);
        }
    }

    public function register(Request $request)
{
    // Validasi input
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'username' => 'required|string|max:255|unique:users',
        'password' => 'required|string|min:6|max:255',
        'role' => 'nullable|string|in:user,admin',
    ]);

    // Jika validasi gagal, kembalikan pesan kesalahan
    if ($validator->fails()) {
        return response()->json($validator->messages(), 422);
    }

    // Buat pengguna baru
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'username' => $request->username,
        'password' => Hash::make($request->password),
        'role' => $request->role ?? 'user',
    ]);

    // Buat token otentikasi untuk pengguna yang baru dibuat
    $token = auth()->login($user);

    return response()->json([
        'meta' => [
            'code' => 200,
            'status' => 'success',
            'message' => 'Successfully registered',
        ],
        'data' => [
            'user' => $user,
            'access_token' => [
                'token' => $token,
                'type' => 'Bearer',
                // 'expires_in' => auth('api')->factory()->getTTL() * 60, 
            ],
        ],
    ]);
}

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15',
        ]);

        $user->update($request->only('address', 'postal_code', 'city', 'phone_number'));

        return response()->json(['message' => 'Profile updated successfully']);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    // protected function respondWithToken($token)
    // {
    //     return response()->json([
    //         'access_token' => $token,
    //         'token_type' => 'bearer',
    //         'expires_in' => JWTAuth::factory()->getTTL() * 60
    //     ]);
    // }
}
