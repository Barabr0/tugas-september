<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class BarangController extends Controller
{
    public function index()
    {
        try {
            $barang = Barang::with(['pemilik', 'kategori'])
                ->where('status', 'T')
                ->latest()
                ->get();

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
    public function Update(Request $request,string $id) {
        $barang = Barang::find($id);
        try {
            if (! $barang) {
                return response()->json([
                'status' => false,
                'message' => 'data barang tidak ada'
            ], 404);
            }
            if ($barang->user_id !==$request->user()->id) {
                return response()->json([
                'status' => false,
                'message' => 'Anda tidaat mengubah data ini'
                ], 403);
                }
                $validated = $request->validate([
                    'kategori_id' => 'sometimes|required|exists:kategoris,id',
                    'nama_barang' => 'sometimes|required|string|max:225',
                    'deskripsi' => 'sometimes|required|string',
                    'kondisi' => 'nullable|in:B,R,P',
                ]);
                $barang->Update($validated);
                $barang->load(['pemilik','kategori']);

                return response()->json([
                'status' => true,
                'message' => 'Data berhasil diedit',
                'data' => $barang
            ], 200);
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
            public function destroy(Request $request, string $id)
        {
            try {
                $barang = Barang::find($id);

                if (!$barang) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data barang tidak ada'
                    ], 404);
                }

                if ($barang->user_id !== $request->user()->id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Anda tidak berhak menghapus data ini'
                    ], 403);
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

                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil dihapus'
                ], 200);

            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        }
}