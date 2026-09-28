<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BantuanRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BantuanController extends Controller
{
    // ==========================
    // USER
    // ==========================

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

    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $requests = BantuanRequest::with(['peminta', 'target'])
                ->where(function ($query) use ($userId) {
                    $query->where('peminta_id', $userId)
                          ->orWhere('target_id', $userId);
                })
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'data' => $requests
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Target user merespons request (setujui / tolak)
    public function respond(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:disetujui,ditolak',
                'alasan' => 'nullable|string',
            ]);

            $userRequest = BantuanRequest::findOrFail($id);

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

    public function destroy(Request $request, $id)
    {
        try {
            $userRequest = BantuanRequest::find($id);

            if (!$userRequest) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            if ($userRequest->peminta_id !== $request->user()->id) {
                return response()->json(['status' => false, 'message' => 'Anda tidak berhak menghapus ini.'], 403);
            }

            if ($userRequest->status !== 'pending') {
                return response()->json(['status' => false, 'message' => 'Hanya request yang masih menunggu yang bisa dibatalkan.'], 409);
            }

            $userRequest->delete();

            return response()->json(['status' => true, 'message' => 'Permintaan berhasil dibatalkan'], 200);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================
    // ADMIN
    // ==========================

    // Lihat semua request (opsional filter ?status=pending)
    public function indexAdmin(Request $request)
    {
        try {
            $query = BantuanRequest::with([
                'peminta:id,name,email',
                'target:id,name,email',
            ])->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return response()->json([
                'status' => true,
                'data' => $query->get()
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Admin setujui / tolak tanpa cek target_id
    public function adminRespond(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:disetujui,ditolak',
                'alasan' => 'nullable|string',
            ]);

            $userRequest = BantuanRequest::findOrFail($id);
            $userRequest->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Request berhasil diproses admin.',
                'data' => $userRequest->load(['peminta', 'target'])
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}