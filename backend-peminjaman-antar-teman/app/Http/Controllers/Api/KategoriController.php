<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class KategoriController extends Controller
{
    public function index()
    {
        try {
            $kategoris = Kategori::latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Data kategori berhasil diambil',
                'data' => $kategoris
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
                'nama_kategori' => 'required|string|max:255|unique:kategoris,nama_kategori'
            ]);

            $kategori = Kategori::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil dibuat',
                'data' => $kategori
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

    public function update(Request $request, string $id)
    {
        try {
            $kategori = Kategori::find($id);

            if (!$kategori) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            $validated = $request->validate([
                'nama_kategori' => 'sometimes|required|string|max:255|unique:kategoris,nama_kategori,' . $id
            ]);

            $kategori->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil diperbarui',
                'data' => $kategori
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

    public function destroy(string $id)
    {
        try {
            $kategori = Kategori::find($id);

            if (!$kategori) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            $kategori->delete();

            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil dihapus'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}