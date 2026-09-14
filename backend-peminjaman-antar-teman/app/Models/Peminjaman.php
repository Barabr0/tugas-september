<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    protected $table = 'peminjamen';
    protected $guarded = ['id'];
    public $timestamps = true;

    public function barangs()
    {
        return $this->belongsToMany(
            Barang::class,
            'barang_peminjaman',
            'peminjaman_id',
            'barang_id'
        )->withPivot('tgl_kembali', 'status_kembali')->withTimestamps();
    }

    public function peminjam()
    {
        return $this->belongsTo(User::class, 'peminjam_id', 'id');
    }

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id', 'id');
    }
}