<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BantuanRequest extends Model
{
    protected $table = 'bantuan_requests';
   protected $fillable = [
        'peminta_id',
        'target_id',
        'tipe_request',
        'deskripsi',
        'status',
        'alasan',
    ];

    public function peminta()
    {
        return $this->belongsTo(User::class, 'peminta_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}