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

    /**
     * Gate sederhana berbasis API Key di header X-API-KEY.
     */
    private function checkApiKey(Request $request)
    {
        $apiKey = env('API_KEY');
        $clientKey = $request->header('X-API-KEY');

        if (!$clientKey || $clientKey !== $apiKey) {
            return response()->json([
                'code'    => 403,
                'status'  => 'Forbidden',
                'message' => 'Invalid or missing API Key'
            ], 403);
        }
        return null;
    }

    /**
     * Ambil bearer token dari Authorization / query / X-Api-Token.
     */
    private function extractToken(Request $request): ?string
    {
        $auth = $request->header('Authorization');
        if ($auth && Str::startsWith($auth, 'Bearer ')) {
            return Str::after($auth, 'Bearer ');
        }
        if ($request->has('token')) {
            return (string) $request->query('token');
        }
        if ($request->hasHeader('X-Api-Token')) {
            return $request->header('X-Api-Token');
        }
        return null;
    }

    /**
     * Temukan user dari token (jika valid); auto-clear jika expired.
     */
    private function findUserFromRequest(Request $request): ?User
    {
        $token = $this->extractToken($request);
        if (!$token) return null;

        $user = User::where('token', $token)->first();
        if (!$user) return null;

        if (!$user->isTokenValid()) {
            // otomatis hapus token kadaluarsa agar DB tetap rapi
            $user->clearToken();
            return null;
        }

        return $user;
    }

    public function register(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) return $resp;

        if (self::USER_LIMIT > 0 && User::count() >= self::USER_LIMIT) {
            return response()->json([
                'code'   => 400,
                'status' => 'Bad Request',
                'errors' => "User limit of " . self::USER_LIMIT . " has been exceeded"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:6',
            'name'     => 'nullable|string|max:255',
            'role'     => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'   => 400,
                'status' => 'Bad Request',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::create([
            'username' => $request->string('username'),
            'password' => Hash::make($request->string('password')),
            'name'     => $request->string('name', ''),
            'role'     => $request->string('role', 'user'),
        ]);

        return response()->json([
            'code'   => 201,
            'status' => 'Created',
            'data'   => [
                'id'       => $user->id,
                'username' => $user->username,
                'name'     => $user->name,
                'role'     => $user->role,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) return $resp;

        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'code'    => 401,
                'status'  => 'Unauthorized',
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Generate token + set expired 4 jam (di method model)
        $token = $user->generateToken();

        return response()->json([
            'code'   => 200,
            'status' => 'OK',
            'data'   => [
                'token'       => $token,
                'token_type'  => 'Bearer',
                'expires_at'  => optional($user->token_expires_at)->toDateTimeString(),
                'user'        => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'name'     => $user->name,
                    'role'     => $user->role,
                ],
            ],
        ]);
    }

    public function current(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) return $resp;

        $user = $this->findUserFromRequest($request);
        if (!$user) {
            return response()->json([
                'code'    => 401,
                'status'  => 'Unauthorized',
                'message' => 'Invalid or expired token'
            ], 401);
        }

        return response()->json([
            'code'   => 200,
            'status' => 'OK',
            'data'   => [
                'id'                => $user->id,
                'username'          => $user->username,
                'name'              => $user->name,
                'role'              => $user->role,
                'token_expires_at'  => optional($user->token_expires_at)->toDateTimeString(),
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) return $resp;

        $user = $this->findUserFromRequest($request);
        if (!$user) {
            return response()->json([
                'code'    => 401,
                'status'  => 'Unauthorized',
                'message' => 'Invalid or expired token'
            ], 401);
        }

        $user->clearToken();

        return response()->json([
            'code'   => 200,
            'status' => 'OK',
            'data'   => ['message' => 'Logged out']
        ], 200);
    }
}
