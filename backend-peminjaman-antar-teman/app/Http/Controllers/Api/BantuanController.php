<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BantuanRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BantuanRequestController extends Controller
{
    // User membuat request ke teman
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'target_id' => 'required|exists:users,id',
                'tipe_request' => 'required|string',
                'deskripsi' => 'required|string',
            ]);

            $userRequest = BantuanRequest::create([
                'peminta_id' => $request->user()->id,
                'target_id' => $validated['target_id'],
                'tipe_request' => $validated['tipe_request'],
                'deskripsi' => $validated['deskripsi'],
                'status' => 'pending'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Request berhasil dikirim ke teman.',
                'data' => $userRequest
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

    // User melihat request yang dia buat dan request yang masuk ke dia
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        
        // Ambil request yang kita buat DAN request yang ditujukan ke kita
        $requests = BantuanRequest::with(['peminta', 'target'])
            ->where('peminta_id', $userId)
            ->orWhere('target_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $requests
        ], 200);
    }

    // User menyetujui/menolak request dari teman
    public function respond(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:disetujui,ditolak',
                'alasan' => 'nullable|string',
            ]);

            $userRequest = BantuanRequest::findOrFail($id);

            // Pastikan hanya target user yang bisa merespons
            if ($userRequest->target_id !== $request->user()->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak berhak merespons request ini.'
                ], 403);
            }

            $userRequest->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Request berhasil direspons.',
                'data' => $userRequest
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
}