<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanUang;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class PeminjamanUangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $data = PeminjamanUang::with(['pemberi', 'peminjam'])
                ->where(fn($q) => $q->where('peminjam_id', $userId)->orWhere('pemberi_id', $userId))
                ->latest()
                ->get();

            return response()->json(['status' => true, 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
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

        if ((int)$validated['teman_id'] === $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Tidak bisa transaksi dengan diri sendiri'], 422);
        }

        // tentukan arah: piutang = saya pemberi, hutang = saya peminjam
        if ($validated['arah'] === 'piutang') {
            $pemberiId = $request->user()->id;
            $peminjamId = $validated['teman_id'];
        } else {
            $pemberiId = $validated['teman_id'];
            $peminjamId = $request->user()->id;
        }

        $peminjaman = PeminjamanUang::create([
            'pemberi_id' => $pemberiId,
            'peminjam_id' => $peminjamId,
            'nominal' => $validated['nominal'],
            'tgl_pinjam' => $validated['tgl_pinjam'],
            'tgl_tenggat' => $validated['tgl_tenggat'],
            'status' => 'M',
        ]);

        $peminjaman->load(['pemberi', 'peminjam']);
        return response()->json(['status' => true, 'data' => $peminjaman], 201);

    } catch (ValidationException $e) {
        return response()->json(['status' => false, 'errors' => $e->errors()], 422);
    } catch (Exception $e) {
        return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
}

    public function setujui(Request $request, string $id)
    {
        return $this->ubahStatus($request, $id, 'M', 'Ds', 'pemberi_id', 'Pengajuan disetujui');
    }

    public function tolak(Request $request, string $id)
    {
        return $this->ubahStatus($request, $id, 'M', 'Dt', 'pemberi_id', 'Pengajuan ditolak');
    }

    public function batalkan(Request $request, string $id)
    {
        return $this->ubahStatus($request, $id, 'M', 'B', 'peminjam_id', 'Pengajuan dibatalkan');
    }

    public function aktifkan(Request $request, string $id)
    {
        return $this->ubahStatus($request, $id, 'Ds', 'A', 'pemberi_id', 'Uang telah diserahkan');
    }

    public function lunas(Request $request, string $id)
    {
        try {
            $p = PeminjamanUang::find($id);
            if (!$p) return response()->json(['status' => false, 'message' => 'Tidak ditemukan'], 404);
            if (!in_array($request->user()->id, [$p->pemberi_id, $p->peminjam_id])) {
                return response()->json(['status' => false, 'message' => 'Tidak berhak'], 403);
            }
            if ($p->status !== 'A') {
                return response()->json(['status' => false, 'message' => 'Status tidak valid'], 409);
            }

            $p->update(['status' => 'S', 'tgl_lunas' => now()]);
            $p->load(['pemberi', 'peminjam']);
            return response()->json(['status' => true, 'data' => $p], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function ubahStatus(Request $request, string $id, string $statusAwal, string $statusBaru, string $kolomPengakses, string $pesan)
    {
        try {
            $p = PeminjamanUang::find($id);
            if (!$p) return response()->json(['status' => false, 'message' => 'Tidak ditemukan'], 404);
            if ($p->{$kolomPengakses} !== $request->user()->id) {
                return response()->json(['status' => false, 'message' => 'Tidak berhak'], 403);
            }
            if ($p->status !== $statusAwal) {
                return response()->json(['status' => false, 'message' => 'Status tidak valid untuk aksi ini'], 409);
            }

            $p->update(['status' => $statusBaru]);
            $p->load(['pemberi', 'peminjam']);
            return response()->json(['status' => true, 'message' => $pesan, 'data' => $p], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}