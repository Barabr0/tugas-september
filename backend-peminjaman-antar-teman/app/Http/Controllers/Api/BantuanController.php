<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BantuanRequest;
use Illuminate\Http\Request;
use Exception;

class BantuanController extends Controller
{
    // User mengajukan request bantuan
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipe_request' => 'required|in:barang,uang',
                'referensi_id' => 'required|integer',
                'aksi_diminta' => 'required|in:edit,hapus,batalkan',
                'alasan' => 'required|string'
            ]);

            $bantuan = BantuanRequest::create([
                ...$validated,
                'user_id' => $request->user()->id,
                'status' => 'pending'
            ]);

            // TODO: Kirim notifikasi ke Admin (jika sistem notifikasi sudah jalan)

            return response()->json([
                'status' => true,
                'message' => 'Permintaan bantuan terkirim ke Admin',
                'data' => $bantuan
            ], 201);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Admin melihat semua request bantuan
        public function index(Request $request)
    {
        // Cek langsung di controller kalau middleware bermasalah
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Hanya admin.'], 403);
        }

        $requests = BantuanRequest::with('user')->where('status', 'pending')->latest()->get();
        return response()->json(['status' => true, 'data' => $requests], 200);
    }

    // Admin menyelesaikan/memproses request
    public function process(Request $request, $id)
    {
        $bantuan = BantuanRequest::findOrFail($id);
        $bantuan->update(['status' => 'processed']);

        // TODO: Kirim notifikasi ke User bahwa request sudah diproses

        return response()->json(['status' => true, 'message' => 'Request telah ditandai selesai'], 200);
    }
}