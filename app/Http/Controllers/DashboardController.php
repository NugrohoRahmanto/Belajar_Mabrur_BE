<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // jumlah per halaman bisa diubah via ?per_page=25 (default 10)
        $perPage = (int) $request->query('per_page', 10);

        $users = User::query()
            ->select('id', 'username', 'role', 'token', 'created_at')
            // Urutkan user aktif (punya token) di atas
            ->orderByRaw("CASE WHEN token IS NULL OR token = '' THEN 1 ELSE 0 END ASC")
            // Lalu urut created_at terbaru
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString(); // jaga query string saat next/prev

        return view('dashboard', compact('users'));
    }
}
