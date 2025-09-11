<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // Bisa diubah sesuai kebutuhan (atau ambil dari .env)
    private const USER_LIMIT = 0; // set 0 jika tidak ingin limit
    private function checkApiKey(Request $request)
    {
        $apiKey = env('API_KEY');
        $clientKey = $request->header('X-API-KEY');

        if (!$clientKey || $clientKey !== $apiKey) {
            return response()->json([
                'code' => 403,
                'status' => 'Forbidden',
                'message' => 'Invalid or missing API Key'
            ], 403);
        }

        return null;
    }

    public function register(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) return $resp;

        if (self::USER_LIMIT > 0 && User::count() >= self::USER_LIMIT) {
            return response()->json([
                'code' => 400,
                'status' => 'Bad Request',
                'errors' => "User limit of " . self::USER_LIMIT . " has been exceeded"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|max:100',
            'password' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => 'Bad Request',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'user', // default role
        ]);

        return response()->json([
            'code' => 201,
            'status' => 'Created',
            'data' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) return $resp;

        $credentials = $request->only('username', 'password');
        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'code' => 401,
                'status' => 'Unauthorized',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user->token = User::generateToken();
        $user->save();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => ['token' => $user->token]
        ], 200);
    }

    public function current(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) return $resp;

        $auth = $request->header('Authorization');
        if (Str::startsWith($auth, 'Bearer ')) {
            $auth = substr($auth, 7);
        }

        $user = User::where('token', $auth)->first();
        if (!$user) {
            return response()->json([
                'code' => 401,
                'status' => 'Unauthorized',
                'message' => 'Invalid token'
            ], 401);
        }

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => [
                'username' => $user->username,
                'role'     => $user->role,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) return $resp;

        $auth = $request->header('Authorization');
        if (Str::startsWith($auth, 'Bearer ')) {
            $auth = substr($auth, 7);
        }

        $user = User::where('token', $auth)->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'status' => 'Unauthorized',
                'message' => 'Invalid token'
            ], 401);
        }

        $user->token = null;
        $user->save();

        return response()->json([
            'code' => 200,
            'status' => 'OK'
        ], 200);
    }
}
