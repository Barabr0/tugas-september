<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    protected $table = 'peminjamen';
    protected $guarded = ['id'];

     public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function peminjam()
    {
        return $this->belongsTo(User::class, 'peminjam_id');
    }

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }
}
