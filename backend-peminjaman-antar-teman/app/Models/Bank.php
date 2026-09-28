<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';
    
    protected $fillable = [
        'nama_bank',
        'nomor_rekening',
        'atas_nama'
    ];

    // Relasi Many-to-Many ke User
    public function users()
    {
        return $this->belongsToMany(User::class, 'bank_user')
                    ->withPivot('saldo', 'jumlah_topup') // Bisa diakses di frontend
                    ->withTimestamps();
    }
}