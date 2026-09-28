<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporans';

    public const TIPE = ['batal_pinjam', 'ganti_barang'];

    protected $fillable = [
        'user_id',
        'target_id',
        'tipe_laporan',
        'peminjaman_id',
        'barang_id',
        'barang_baru_id',
        'deskripsi',
        'status',
        'alasan_respons',
    ];

    protected $with = [
        'pelapor:id,name,email',
        'target:id,name,email',
        'barangLama:id,nama_barang',
        'barangBaru:id,nama_barang',
    ];

    protected $appends = ['pelapor_nama', 'target_nama', 'barang_lama_nama', 'barang_baru_nama'];

    // Pelapor = peminjam (kolom user_id)
    public function pelapor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Target = pemilik barang
    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function barangLama()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function barangBaru()
    {
        return $this->belongsTo(Barang::class, 'barang_baru_id');
    }

    public function getPelaporNamaAttribute()
    {
        return $this->pelapor?->name;
    }

    public function getTargetNamaAttribute()
    {
        return $this->target?->name;
    }

    public function getBarangLamaNamaAttribute()
    {
        return $this->barangLama?->nama_barang;
    }

    public function getBarangBaruNamaAttribute()
    {
        return $this->barangBaru?->nama_barang;
    }
}