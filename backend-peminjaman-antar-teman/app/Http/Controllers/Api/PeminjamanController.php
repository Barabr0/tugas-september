<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Notifikasi;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $peminjaman = Peminjaman::with(['barangs', 'peminjam', 'pemilik'])
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

    public function show(Request $request, string $id)
    {
        try {
            $peminjaman = Peminjaman::with(['barangs', 'peminjam', 'pemilik'])->find($id);

            if (!$peminjaman) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data peminjaman tidak ada'
                ], 404);
            }

            $userId = $request->user()->id;
            if (!in_array($userId, [$peminjaman->pemilik_id, $peminjaman->peminjam_id])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak berhak melihat data ini.'
                ], 403);
            }

            return response()->json([
                'status' => true,
                'message' => 'Detail berhasil diambil',
                'data' => $peminjaman
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
                'barang_ids' => 'required|array|min:1',
                'barang_ids.*' => 'required|exists:barangs,id',
                'tgl_pinjam' => 'required|date',
                'tgl_tenggat' => 'required|date|after_or_equal:tgl_pinjam',
            ]);

            $barangs = Barang::whereIn('id', $validated['barang_ids'])->get();

            $tidakTersedia = $barangs->where('status', '!=', 'T');
            if ($tidakTersedia->isNotEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ada barang yang sedang tidak tersedia'
                ], 409);
            }

            $pemilikUnik = $barangs->pluck('user_id')->unique();
            if ($pemilikUnik->count() > 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Barang harus dari pemilik yang sama'
                ], 422);
            }
            $pemilikId = $pemilikUnik->first();

            if ($pemilikId === $request->user()->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak bisa pinjam barang sendiri'
                ], 422);
            }

            // Bungkus dengan transaction: kalau salah satu proses gagal,
            // semua dibatalkan (tidak ada data nyangkut setengah jadi)
            $peminjaman = DB::transaction(function () use ($validated, $pemilikId, $request) {
                $peminjaman = Peminjaman::create([
                    'peminjam_id' => $request->user()->id,
                    'pemilik_id' => $pemilikId,
                    'tgl_pinjam' => $validated['tgl_pinjam'],
                    'tgl_tenggat' => $validated['tgl_tenggat'],
                    'status' => 'M',
                ]);

                $peminjaman->barangs()->attach($validated['barang_ids']);

                Notifikasi::create([
                    'user_id' => $pemilikId,
                    'peminjaman_id' => $peminjaman->id,
                    'judul' => 'Pengajuan Peminjaman Baru',
                    'pesan' => $request->user()->name . ' mengajukan pinjam barang Anda.',
                ]);

                return $peminjaman;
            });

            $peminjaman->load(['barangs', 'peminjam', 'pemilik']);

            return response()->json([
                'status' => true,
                'message' => 'Pengajuan berhasil dibuat',
                'data' => $peminjaman
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

    public function setujui(Request $request, string $id)
    {
        try {
            $peminjaman = Peminjaman::find($id);

            if (!$peminjaman) {
                return response()->json(['status' => false, 'message' => 'Data peminjaman tidak ada'], 404);
            }

            if ($peminjaman->pemilik_id !== $request->user()->id) {
                return response()->json(['status' => false, 'message' => 'Anda tidak bisa menyetujui pengajuan ini.'], 403);
            }

            if ($peminjaman->status !== 'M') {
                return response()->json(['status' => false, 'message' => 'Pengajuan sudah diproses sebelumnya.'], 409);
            }

            DB::transaction(function () use ($peminjaman) {
                $peminjaman->update(['status' => 'Ds']);

                Notifikasi::create([
                    'user_id' => $peminjaman->peminjam_id,
                    'peminjaman_id' => $peminjaman->id,
                    'judul' => 'Pengajuan Disetujui',
                    'pesan' => 'Pengajuan peminjaman Anda telah disetujui.',
                ]);
            });

            $peminjaman->load(['barangs', 'peminjam', 'pemilik']);

            return response()->json([
                'status' => true,
                'message' => 'Pengajuan berhasil disetujui.',
                'data' => $peminjaman
            ], 200);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function tolak(Request $request, string $id)
    {
        try {
            $peminjaman = Peminjaman::find($id);

            if (!$peminjaman) {
                return response()->json(['status' => false, 'message' => 'Data peminjaman tidak ada'], 404);
            }

            if ($peminjaman->pemilik_id !== $request->user()->id) {
                return response()->json(['status' => false, 'message' => 'Anda tidak bisa menolak pengajuan ini.'], 403);
            }

            if ($peminjaman->status !== 'M') {
                return response()->json(['status' => false, 'message' => 'Pengajuan sudah diproses sebelumnya.'], 409);
            }

            DB::transaction(function () use ($peminjaman) {
                $peminjaman->update(['status' => 'Dt']);

                Notifikasi::create([
                    'user_id' => $peminjaman->peminjam_id,
                    'peminjaman_id' => $peminjaman->id,
                    'judul' => 'Pengajuan Ditolak',
                    'pesan' => 'Pengajuan peminjaman Anda telah ditolak oleh pemilik barang.',
                ]);
            });

            $peminjaman->load(['barangs', 'peminjam', 'pemilik']);

            return response()->json([
                'status' => true,
                'message' => 'Pengajuan berhasil ditolak.',
                'data' => $peminjaman
            ], 200);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function batalkan(Request $request, string $id)
    {
        try {
            $peminjaman = Peminjaman::find($id);

            if (!$peminjaman) {
                return response()->json(['status' => false, 'message' => 'Data peminjaman tidak ada'], 404);
            }

            if ($peminjaman->peminjam_id !== $request->user()->id) {
                return response()->json(['status' => false, 'message' => 'Anda tidak bisa membatalkan pengajuan ini.'], 403);
            }

            if ($peminjaman->status !== 'M') {
                return response()->json(['status' => false, 'message' => 'Pengajuan tidak dapat dibatalkan.'], 409);
            }

            DB::transaction(function () use ($peminjaman) {
                $peminjaman->update(['status' => 'B']);

                Notifikasi::create([
                    'user_id' => $peminjaman->pemilik_id,
                    'peminjaman_id' => $peminjaman->id,
                    'judul' => 'Pengajuan Dibatalkan',
                    'pesan' => 'Peminjam membatalkan pengajuan peminjaman.',
                ]);
            });

            $peminjaman->load(['barangs', 'peminjam', 'pemilik']);

            return response()->json([
                'status' => true,
                'message' => 'Pengajuan berhasil dibatalkan.',
                'data' => $peminjaman
            ], 200);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function aktifkan(Request $request, string $id)
    {
        try {
            $peminjaman = Peminjaman::with('barangs')->find($id);

            if (!$peminjaman) {
                return response()->json(['status' => false, 'message' => 'Data peminjaman tidak dapat ditemukan'], 404);
            }

            if ($peminjaman->pemilik_id !== $request->user()->id) {
                return response()->json(['status' => false, 'message' => 'Anda tidak dapat mengaktifkan pengajuan ini.'], 403);
            }

            if ($peminjaman->status !== 'Ds') {
                return response()->json(['status' => false, 'message' => 'Permintaan anda belum disetujui.'], 409);
            }

            DB::transaction(function () use ($peminjaman) {
                $peminjaman->update(['status' => 'A']);

                foreach ($peminjaman->barangs as $barang) {
                    $barang->update(['status' => 'D']);
                }

                Notifikasi::create([
                    'user_id' => $peminjaman->peminjam_id,
                    'peminjaman_id' => $peminjaman->id,
                    'judul' => 'Barang Diserahkan',
                    'pesan' => 'Barang telah diserahkan, peminjaman kini aktif.',
                ]);
            });

            $peminjaman->load(['barangs', 'peminjam', 'pemilik']);

            return response()->json([
                'status' => true,
                'message' => 'Pengajuan berhasil diaktifkan.',
                'data' => $peminjaman
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function kembalikan(Request $request, string $id, string $barangId)
    {
        try {
            $peminjaman = Peminjaman::with('barangs')->find($id);

            if (!$peminjaman) {
                return response()->json(['status' => false, 'message' => 'Data peminjaman tidak ada'], 404);
            }

            if (!in_array($request->user()->id, [$peminjaman->pemilik_id, $peminjaman->peminjam_id])) {
                return response()->json(['status' => false, 'message' => 'Anda tidak berhak melakukan ini.'], 403);
            }

            if ($peminjaman->status !== 'A') {
                return response()->json(['status' => false, 'message' => 'Peminjaman tidak sedang aktif.'], 409);
            }

            $barang = $peminjaman->barangs->firstWhere('id', $barangId);
            if (!$barang) {
                return response()->json(['status' => false, 'message' => 'Barang tidak ada dalam peminjaman ini.'], 404);
            }

            if ($barang->pivot->tgl_kembali !== null) {
                return response()->json(['status' => false, 'message' => 'Barang ini sudah dikembalikan.'], 409);
            }

            $terlambat = now()->toDateString() > $peminjaman->tgl_tenggat;
            $sisaBelumKembali = 0;

            DB::transaction(function () use ($peminjaman, $barang, $barangId, $terlambat, &$sisaBelumKembali) {
                $peminjaman->barangs()->updateExistingPivot($barangId, [
                    'tgl_kembali' => now(),
                    'status_kembali' => $terlambat ? 'Terlambat' : 'Selesai',
                ]);

                $barang->update(['status' => 'T']);

                if ($terlambat) {
                    $peminjaman->peminjam->decrement('skor_reputasi', 10);
                }

                $sisaBelumKembali = $peminjaman->barangs()->wherePivotNull('tgl_kembali')->count();

                if ($sisaBelumKembali === 0) {
                    $peminjaman->update(['status' => 'S']);
                }

                Notifikasi::create([
                    'user_id' => $peminjaman->pemilik_id,
                    'peminjaman_id' => $peminjaman->id,
                    'judul' => 'Barang Dikembalikan',
                    'pesan' => $barang->nama_barang . ' telah dikembalikan' . ($terlambat ? ' (terlambat).' : '.'),
                ]);
            });

            $peminjaman->load(['barangs', 'peminjam', 'pemilik']);

            return response()->json([
                'status' => true,
                'message' => $sisaBelumKembali === 0
                    ? 'Barang dikembalikan, seluruh peminjaman selesai.'
                    : 'Barang berhasil dikembalikan.',
                'data' => $peminjaman
            ], 200);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}