<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['username', 'password', 'role', 'token', 'token_expires_at'];
    protected $hidden   = ['password', 'token'];
    protected $casts    = ['token_expires_at' => 'datetime'];

    public function generateToken(): string
    {
        // kamu bisa ganti jadi random_bytes/Str::random untuk lebih kuat
        $token = 'tok_' . bin2hex(random_bytes(32)); // 64 hex chars

        $this->token = $token; // simpan plain (sederhana). Kalau mau lebih aman: simpan hash.
        $this->token_expires_at = Carbon::now()->addHours(4);
        dd(Carbon::now());
        $this->save();

        return $token;
    }

    public function isTokenValid(): bool
    {
        return $this->token
            && $this->token_expires_at
            && now()->lessThanOrEqualTo($this->token_expires_at);
    }

    public function clearToken(): void
    {
        $this->token = null;
        $this->token_expires_at = null;
        $this->save();
    }
}
