<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json(['message' => 'Welcome to Admin Dashboard'], 200);
    }

    public function getAllUsers()
    {
        $users = User::all();

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Users retrieved successfully',
            ],
            'data' => $users
        ], 200);
    }
}
