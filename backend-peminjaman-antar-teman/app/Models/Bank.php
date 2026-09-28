<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';

    protected $fillable = [
        'user_id',
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
    ];

    // Pemilik rekening (sesuai kolom banks.user_id)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi Many-to-Many ke User (saldo disimpan di pivot)
    public function users()
    {
        return $this->belongsToMany(User::class, 'bank_user')
                    ->withPivot('saldo', 'jumlah_topup')
                    ->withTimestamps();
    }
}