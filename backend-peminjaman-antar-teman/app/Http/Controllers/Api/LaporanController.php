<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laporan; // Pastikan buat model ini nanti
use Illuminate\Http\Request;
use Exception;

class LaporanController extends Controller
{
    // User buat laporan
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe_laporan' => 'required|string',
            'deskripsi' => 'required|string',
        ]);

        $laporan = Laporan::create([
            'user_id' => $request->user()->id,
            ...$validated,
            'status' => 'pending'
        ]);

        return response()->json(['status' => true, 'message' => 'Laporan terkirim ke admin', 'data' => $laporan], 201);
    }

    // User lihat laporannya sendiri + alasan admin
    public function myReports(Request $request)
    {
        return response()->json(['status' => true, 'data' => $request->user()->laporans], 200);
    }

    // Admin lihat semua laporan
    public function index()
    {
        $laporans = Laporan::with('user')->where('status', 'pending')->latest()->get();
        return response()->json(['status' => true, 'data' => $laporans], 200);
    }

    // Admin proses laporan (beri alasan)
    public function process(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'alasan_admin' => 'nullable|string'
        ]);

        $laporan = Laporan::findOrFail($id);
        $laporan->update($validated);

        return response()->json(['status' => true, 'message' => 'Laporan diproses', 'data' => $laporan], 200);
    }
}