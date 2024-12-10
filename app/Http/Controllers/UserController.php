<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        if (User::count() > 40) {
            return response()->json(['code' => 400, 'status' => 'Bad Request', 'errors' => 'The user limit of 40 has been exceeded'], 400);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|max:100',
            'password' => 'required|min:4',
            'name' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'Bad Request', 'errors' => $validator->errors()], 400);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'role' => 'user', // default role
        ]);

        return response()->json(['code' => 201, 'status' => 'Created', 'data' => $user], 201);
    }

    // Login
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['code' => 401, 'status' => 'Unauthorized', 'message' => 'Invalid credentials'], 401);
        }

        $user->token = User::generateToken();
        $user->save();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => ['token' => $user->token]
        ], 200);
    }

    // Get current user
    public function current(Request $request)
    {
        $user = User::where('token', $request->header('Authorization'))->first();

        if (!$user) {
            return response()->json(['code' => 401, 'status' => 'Unauthorized', 'message' => 'Invalid token'], 401);
        }

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => [
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
            ]
        ], 200);
    }

    // Logout
    public function logout(Request $request)
    {
        $user = User::where('token', $request->header('Authorization'))->first();

        if (!$user) {
            return response()->json(['code' => 401, 'status' => 'Unauthorized', 'message' => 'Invalid token'], 401);
        }

        $user->token = null;
        $user->save();

        return response()->json(['code' => 200, 'status' => 'OK'], 200);
    }
}
