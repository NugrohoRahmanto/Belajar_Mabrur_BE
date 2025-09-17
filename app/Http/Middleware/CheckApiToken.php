<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class CheckApiToken
{
    public function handle(Request $request, Closure $next)
    {
        // Ambil token dari Authorization: Bearer <token>
        $bearer = $request->bearerToken();

        // (opsional) fallback dari query ?token= atau header X-Api-Token
        $token = $bearer ?: ($request->query('token') ?: $request->header('X-Api-Token'));

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        // Cari user dengan token yang cocok
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Cek kadaluarsa 4 jam (model helper)
        if (!$user->isTokenValid()) {
            // optional: bersihkan token biar DB rapi
            $user->clearToken();
            return response()->json(['message' => 'Token expired'], 401);
        }

        // Simpan user ke request agar controller bisa akses
        $request->attributes->set('auth_user', $user);

        return $next($request);
    }
}
