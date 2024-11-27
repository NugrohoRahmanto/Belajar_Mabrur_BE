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
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|max:100',
            'password' => 'required|min:4',
            'name' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'Bad Request', 'errors' => $validator->errors()]);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'role' => 'user', // default role
        ]);

        return response()->json(['code' => 201, 'status' => 'Created', 'data' => $user]);
    }

    // Login
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        
        $user = User::where('username', $request->username)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['code' => 401, 'status' => 'Unauthorized', 'message' => 'Invalid credentials']);
        }

        $user->token = User::generateToken();
        $user->save();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => ['token' => $user->token]
        ]);
    }

    // Get current user
    public function current(Request $request)
    {
        $user = User::where('token', $request->header('Authorization'))->first();
        
        if (!$user) {
            return response()->json(['code' => 401, 'status' => 'Unauthorized', 'message' => 'Invalid token']);
        }

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => [
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
            ]
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $user = User::where('token', $request->header('Authorization'))->first();
        
        if (!$user) {
            return response()->json(['code' => 401, 'status' => 'Unauthorized', 'message' => 'Invalid token']);
        }

        $user->token = null;
        $user->save();

        return response()->json(['code' => 200, 'status' => 'OK']);
    }
}
