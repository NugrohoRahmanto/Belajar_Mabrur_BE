<?php

namespace App\Http\Controllers;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    
    public function index(Request $request)
    {
        $apiKey = env('API_KEY');  // pastikan API_KEY ada di file .env
        if ($request->header('X-API-KEY') !== $apiKey) {
            return response()->json(['code' => 403, 'status' => 'Forbidden', 'message' => 'Invalid API Key']);
        }
        // Mengambil semua konten dari database
        $contents = Content::all();

        // Mengembalikan response dalam format JSON
        return response()->json([
            'success' => true,
            'data' => $contents
        ], 200);
    }

    public function index1(Request $request)
    {
        $apiKey = env('API_KEY');  // pastikan API_KEY ada di file .env
        if ($request->header('X-API-KEY') !== $apiKey) {
            return response()->json(['code' => 403, 'status' => 'Forbidden', 'message' => 'Invalid API Key']);
        }

        $category = $request->input('category');
        $contents = Content::where('category', $category)->get();

        return response()->json(['code' => 200, 'status' => 'OK', 'data' => $contents]);
    }
}
