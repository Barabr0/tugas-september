<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanUang;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class PeminjamanUangController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Ambil semua transaksi uang yang melibatkan user yang login
            $userId = $request->user()->id;
            
            $pinjamanUang = PeminjamanUang::with(['pemberi', 'peminjam'])
                ->where('pemberi_id', $userId)
                ->orWhere('peminjam_id', $userId)
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => $pinjamanUang
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'teman_id' => 'required|exists:users,id',
                'arah' => 'required|in:piutang,hutang',
                'nominal' => 'required|numeric|min:1',
                'tgl_pinjam' => 'required|date',
                'tgl_tenggat' => 'required|date|after_or_equal:tgl_pinjam',
            ]);

            // Tentukan siapa pemberi dan peminjam
            if ($validated['arah'] === 'piutang') {
                $pemberi_id = $request->user()->id;
                $peminjam_id = $validated['teman_id'];
            } else { // hutang
                $pemberi_id = $validated['teman_id'];
                $peminjam_id = $request->user()->id;
            }

            $pinjamanUang = PeminjamanUang::create([
                'pemberi_id' => $pemberi_id,
                'peminjam_id' => $peminjam_id,
                'nominal' => $validated['nominal'],
                'tgl_pinjam' => $validated['tgl_pinjam'],
                'tgl_tenggat' => $validated['tgl_tenggat'],
                'status' => 'M', // Menunggu
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Peminjaman uang berhasil diajukan',
                'data' => $pinjamanUang
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function setujui(Request $request, $id)
    {
        try {
            $pinjaman = PeminjamanUang::findOrFail($id);
            $pinjaman->update(['status' => 'Ds']);
            return response()->json(['status' => true, 'message' => 'Disetujui', 'data' => $pinjaman]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function tolak(Request $request, $id)
    {
        try {
            $pinjaman = PeminjamanUang::findOrFail($id);
            $pinjaman->update(['status' => 'Dt']);
            return response()->json(['status' => true, 'message' => 'Ditolak', 'data' => $pinjaman]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function batalkan(Request $request, $id)
    {
        try {
            $pinjaman = PeminjamanUang::findOrFail($id);
            $pinjaman->update(['status' => 'B']);
            return response()->json(['status' => true, 'message' => 'Dibatalkan', 'data' => $pinjaman]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function aktifkan(Request $request, $id)
    {
        try {
            $pinjaman = PeminjamanUang::findOrFail($id);
            $pinjaman->update(['status' => 'A']);
            return response()->json(['status' => true, 'message' => 'Diaktifkan', 'data' => $pinjaman]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function lunas(Request $request, $id)
    {
        try {
            $pinjaman = PeminjamanUang::findOrFail($id);
            $pinjaman->update([
                'status' => 'S',
                'tgl_lunas' => now()
            ]);
            return response()->json(['status' => true, 'message' => 'Lunas', 'data' => $pinjaman]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}