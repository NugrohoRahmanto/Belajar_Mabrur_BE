<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Cek API Key dari header
     */
    private function checkApiKey(Request $request): ?\Illuminate\Http\JsonResponse
    {
        $apiKey = env('API_KEY');  // pastikan API_KEY ada di .env
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

    /**
     * Ambil semua konten
     */
    public function getContent(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) {
            return $resp;
        }

        $contents = Content::all();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $contents
        ], 200);
    }

    /**
     * Ambil konten berdasarkan kategori
     */
    public function getContentByCategory(Request $request)
    {
        if ($resp = $this->checkApiKey($request)) {
            return $resp;
        }

        $category = $request->input('category');

        if (!$category) {
            return response()->json([
                'code' => 400,
                'status' => 'Bad Request',
                'message' => 'Category parameter is required'
            ], 400);
        }

        $contents = Content::where('category', $category)->get();

        if ($contents->isEmpty()) {
            return response()->json([
                'code' => 404,
                'status' => 'Not Found',
                'message' => "No contents found for category: {$category}"
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $contents
        ], 200);
    }

}
