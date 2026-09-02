<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\ValidationException;

class PeminjamanController extends Controller
{
    public function index(Request $request) {
        try{
            $userId = $request->user()->id;

        $peminjaman = Peminjaman::with(['barang','peminjam','pemilik'])
            ->where(function ($query) use ($userId) {
                $query->where('peminjam_id', $userId)
                      ->orWhere('pemilik_id', $userId);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
        ->latest()
        ->get();

         return response()->json([
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => $peminjaman
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request) {
        try {
            
            $validated = $request->validate([
                'barang_id' => 'required|exists:barangs,id',
                'tgl_pinjam' => 'required|date',
                'tgl_tenggat' => 'required|date',
                ]);
                
                $barang = Barang::find($validated['barang_id']);

                if ($barang !== 'T') {
                    return response()->json([
                        'status' => false,
                        'message' => 'barang sedang tidak tersedia',
                    ],409);
                }
                if ($barang->user_id === $request->user()->id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Anda tidak bisa meminjam barang milik sendiri.'
                    ], 422);
                }
                $peminjaman = Peminjaman::create([
                'barang_id' => $barang->id,
                'peminjam_id' => $request->user()->id,
                'pemilik_id' => $barang->user_id,
                'tgl_pinjam' => $validated['tgl_pinjam'],
                'tgl_tenggat' => $validated['tgl_tenggat'],
                'status' => 'M',
            ]);

            $peminjaman->load(['barang', 'peminjam', 'pemilik']);

            return response()->json([
            'status' => true,
            'message' => 'Pengajuan peminjaman berhasil dibuat',
            'data' => $peminjaman
        ], 201);
        }catch(ValidationException $e) {
             return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
