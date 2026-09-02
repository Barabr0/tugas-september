<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;

class KategoriController extends Controller
{
    public function index() {
        try {
            $kategori = Kategori::latest()->get();
            return response()->json([
                'status'=> true,
                'message' => 'Data berhasil diambil',
                'data' => $kategori,
            ],200);

        } catch (Exception $e) {
            return response()->json([
                'status'=> false,
                'message' => 'Data tidak dapat diambil',
            ],500);
        }
    }
    public function store(Request $request){
        try {
            $request->validate([
            'nama_kategori' => 'required|unique:kategoris,nama_kategori'
            ]);

            $kategori = new Kategori();
            $kategori->nama_kategori = $request['nama_kategori'];
            $kategori->slug          = Str::slug($request['nama_kategori']) . Str::random(10);
            $kategori->save();

             return response()->json([
                'status' => true,
                'message' => "data berhasi dibuat",
                'data' => $kategori,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'=> false,
                'message' => 'Data tidak dapat dibuat',
            ],500);
        }
    }
      public function update(Request $request, string $id)
    {
        $kategori = Kategori::find($id);
        try {
            if (! $kategori) {
                return response()->json([
                'status' => false,
            'message' => "data kategori tidak ada",
        ],404);
            }
             $request->validate([
                'nama_kategori' => 'required|unique:kategoris,nama_kategori,'. $id,   
            ]);
            
            $kategori->nama_kategori = $request->nama_kategori;
            $kategori->slug        = Str::slug($request->nama_kategori) . Str::random(10);
            $kategori->save();

            return response()->json([
                'status' => true,
                'message' => "data berhasi dibuat",
                'data' => $kategori,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
            'message' => $e->getMessage(),
        ],500);
        }
    }
     public function destroy(string $id)
    {
        try {
            $kategori = Kategori::find($id);
            if (! $kategori) {
                return response()->json([
                'status' => false,
            'message' => "data Kategori tidak ada",
        ],404);
            }
            $kategori->delete();
            return response()->json([
                'status' => true,
            'message' => "data Kategori berhasi dihapus",
        ],200);
        } catch (\Exception $e) {
             return response()->json([
                'status' => false,
            'message' => $e->getMessage(),
        ],500);
        }
    }
}
