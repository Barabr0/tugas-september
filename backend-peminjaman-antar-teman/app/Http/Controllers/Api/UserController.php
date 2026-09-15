<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;

class UserController extends Controller
{
    public function index(Request $request)
{
    try {
        $query = User::query();

        // JIKA YANG LOGIN BUKAN ADMIN, HANYA TAMPILKAN USER LAIN (TEMAN)
        if ($request->user()->role !== 'admin') {
            $query->where('id', '!=', $request->user()->id);
        }

        $users = $query->get();

        return response()->json([
            'status' => true,
            'message' => 'Data user berhasil diambil',
            'data' => $users
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'false',
            'message' => $e->getMessage()
        ], 500);
    }
}
}