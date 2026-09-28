<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LaporanController extends Controller
{
    // ==========================
    // USER
    // ==========================

    // Semua laporan yang saya buat ATAU yang ditujukan ke saya
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $laporans = Laporan::where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)->orWhere('target_id', $userId);
                })
                ->latest()
                ->get();

            return response()->json(['status' => true, 'data' => $laporans], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Ajukan laporan ke user lain
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'target_id' => ['required', 'exists:users,id', Rule::notIn([$user->id])],
                'tipe_laporan' => ['required', Rule::in(Laporan::TIPE)],
                'peminjaman_id' => 'nullable|integer',
                'barang_baru_id' => 'nullable|integer|required_if:tipe_laporan,ganti_barang',
                'deskripsi' => 'required|string|max:1000',
            ], [
                'target_id.not_in' => 'Kamu tidak bisa mengajukan laporan ke diri sendiri.',
                'barang_baru_id.required_if' => 'Barang pengganti wajib diisi untuk tipe Ganti Barang.',
            ]);

            // Cegah pengajuan ganda yang masih menunggu
            $duplikat = Laporan::where('user_id', $user->id)
                ->where('target_id', $validated['target_id'])
                ->where('tipe_laporan', $validated['tipe_laporan'])
                ->where('peminjaman_id', $validated['peminjaman_id'] ?? null)
                ->where('status', 'pending')
                ->exists();

            if ($duplikat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengajuan serupa masih menunggu respons.',
                ], 409);
            }

            $laporan = Laporan::create($validated + [
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Laporan berhasil dikirim.',
                'data' => $laporan->fresh(),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Target menyetujui / menolak
    public function respond(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:disetujui,ditolak',
                'alasan_respons' => 'nullable|string|max:500|required_if:status,ditolak',
            ], [
                'alasan_respons.required_if' => 'Alasan wajib diisi saat menolak.',
            ]);

            $laporan = Laporan::findOrFail($id);

            if ((int) $laporan->target_id !== (int) $request->user()->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak berhak merespons laporan ini.',
                ], 403);
            }

            if ($laporan->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Laporan ini sudah direspons.',
                ], 409);
            }

            $laporan->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Laporan berhasil direspons.',
                'data' => $laporan->fresh(),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.'], 404);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Pelapor membatalkan pengajuannya (hanya saat masih pending)
    public function destroy(Request $request, $id)
    {
        try {
            $laporan = Laporan::find($id);

            if (!$laporan) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }

            if ((int) $laporan->user_id !== (int) $request->user()->id) {
                return response()->json(['status' => false, 'message' => 'Anda tidak berhak membatalkan ini.'], 403);
            }

            if ($laporan->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Hanya laporan yang masih menunggu yang bisa dibatalkan.',
                ], 409);
            }

            $laporan->delete();

            return response()->json(['status' => true, 'message' => 'Laporan dibatalkan.'], 200);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================
    // ADMIN (monitoring saja)
    // ==========================

    public function indexAdmin(Request $request)
    {
        try {
            $query = Laporan::latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return response()->json(['status' => true, 'data' => $query->get()], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}