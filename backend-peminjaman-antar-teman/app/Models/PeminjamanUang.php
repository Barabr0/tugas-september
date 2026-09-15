<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeminjamanUang extends Model
{
    protected $table = 'peminjaman_uangs';
    protected $guarded = ['id'];

    public function pemberi()
    {
        return $this->belongsTo(User::class, 'pemberi_id');
    }

    public function peminjam()
    {
        return $this->belongsTo(User::class, 'peminjam_id');
    }
}