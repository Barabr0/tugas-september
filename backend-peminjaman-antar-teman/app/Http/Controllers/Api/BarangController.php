<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class BarangController extends Controller
{
    public function index(Request $request)
{
    try {
        $query = Barang::with(['pemilik', 'kategori'])
            ->where('status', 'T');

        if ($request->user()->role === 'admin') {
            // admin lihat semua barang
        } else {
            $query->where('user_id', $request->user()->id);
        }

        $barang = $query->oldest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $barang
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    // Dipakai untuk form pinjaman - lihat barang milik teman tertentu
    public function getBarangTersedia(Request $request)
    {
        try {
            $query = Barang::with(['pemilik', 'kategori'])
                ->where('status', 'T');

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $barang = $query->latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => $barang
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
                'kategori_id' => 'required|exists:kategoris,id',
                'nama_barang' => 'required|string|max:225',
                'deskripsi' => 'required|string',
                'kondisi' => 'nullable|in:B,R,P'
            ]);

            $barang = Barang::create([
                ...$validated,
                'user_id' => $request->user()->id,
            ]);
            $barang->load(['pemilik', 'kategori']);

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil dibuat',
                'data' => $barang
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $barang = Barang::find($id);

            if (!$barang) {
                return response()->json(['status' => false, 'message' => 'Data barang tidak ada'], 404);
            }

            if ($barang->user_id !== $request->user()->id) {
                return response()->json(['status' => false, 'message' => 'Anda tidak berhak mengubah barang ini.'], 403);
            }

            $validated = $request->validate([
                'kategori_id' => 'sometimes|required|exists:kategoris,id',
                'nama_barang' => 'sometimes|required|string|max:225',
                'deskripsi' => 'sometimes|required|string',
                'kondisi' => 'nullable|in:B,R,P',
            ]);

            $barang->update($validated);
            $barang->load(['pemilik', 'kategori']);

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diperbarui',
                'data' => $barang
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $barang = Barang::find($id);

            if (!$barang) {
                return response()->json(['status' => false, 'message' => 'Data barang tidak ada'], 404);
            }

            if ($barang->user_id !== $request->user()->id) {
                return response()->json(['status' => false, 'message' => 'Anda tidak berhak menghapus data ini'], 403);
            }

            $adaTransaksiAktif = $barang->peminjaman()
                ->whereIn('status', ['M', 'Ds', 'A'])
                ->exists();

            if ($adaTransaksiAktif) {
                return response()->json([
                    'status' => false,
                    'message' => 'Barang memiliki transaksi peminjaman aktif, tidak bisa dihapus.'
                ], 409);
            }

            $barang->delete();

            return response()->json(['status' => true, 'message' => 'Data berhasil dihapus'], 200);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(Request $request, string $id)
    {
        try {
            $barang = Barang::find($id);

            if (!$barang) {
                return response()->json(['status' => false, 'message' => 'Data barang tidak ada'], 404);
            }

            $barang->delete();

            return response()->json(['status' => true, 'message' => 'Barang berhasil dihapus paksa'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}