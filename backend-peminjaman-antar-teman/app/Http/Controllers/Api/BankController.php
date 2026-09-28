<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Exception;

class BankController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['status' => true, 'data' => $request->user()->banks], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bank' => 'required|string',
            'nomor_rekening' => 'required|string',
            'atas_nama' => 'required|string',
        ]);

        $bank = $request->user()->banks()->create($validated);
        return response()->json(['status' => true, 'message' => 'Bank ditambahkan', 'data' => $bank], 201);
    }

    public function destroy($id)
    {
        Bank::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Bank dihapus'], 200);
    }
}