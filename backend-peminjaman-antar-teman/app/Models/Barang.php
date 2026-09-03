<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barangs';
    protected $guarded = ['id'];
    public $timestamps = true;

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id');
    }

    public function peminjaman()
    {
        return $this->belongsToMany(
            Peminjaman::class,
            'barang_peminjaman',
            'barang_id',
            'peminjaman_id'
        )->withPivot('tgl_kembali', 'status_kembali')->withTimestamps();
    }
}