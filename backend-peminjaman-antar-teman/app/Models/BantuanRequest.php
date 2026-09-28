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

    // Relasi selalu ikut ter-load
    protected $with = ['peminta:id,name,email', 'target:id,name,email'];

    // Field tambahan yang otomatis masuk ke JSON
    protected $appends = ['peminta_nama', 'target_nama'];

    public function peminta()
    {
        return $this->belongsTo(User::class, 'peminta_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function getPemintaNamaAttribute()
    {
        return $this->peminta?->name;
    }

    public function getTargetNamaAttribute()
    {
        return $this->target?->name;
    }
}