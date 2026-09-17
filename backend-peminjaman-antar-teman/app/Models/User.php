<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'no_hp', 'role', 'skor_reputasi'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

            public function isAdmin(): bool
        {
            return $this->role === 'admin';
        }
            public function barangs()
        {
            return $this->hasMany(Barang::class, 'user_id');
        }

        public function peminjamanSebagaiPeminjam()
        {
            return $this->hasMany(Peminjaman::class, 'peminjam_id');
        }

        public function peminjamanSebagaiPemilik()
        {
            return $this->hasMany(Peminjaman::class, 'pemilik_id');
        }
        public function pinjamanUangSebagaiPemberi()
        {
            return $this->hasMany(PeminjamanUang::class, 'pemberi_id');
        }

        public function pinjamanUangSebagaiPeminjam()
        {
            return $this->hasMany(PeminjamanUang::class, 'peminjam_id');
        }
            public function banks()
        {
            return $this->belongsToMany(Bank::class, 'bank_user')
                        ->withPivot('saldo', 'jumlah_topup') // Bawa kolom saldo dari pivot
                        ->withTimestamps();
        }
        /**
         * Relasi ke Laporan
         */
        public function laporans()
        {
            return $this->hasMany(Laporan::class);
        }
        // User yang follow saya
        public function followers()
        {
            return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
        }

        // TAMBAHKAN INI: User yang saya follow (teman saya)
        public function followings()
        {
            return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
        }
        public function requestsDibuat()
        {
            return $this->hasMany(BantuanRequest::class, 'peminta_id');
        }

        public function requestsDiterima()
        {
            return $this->hasMany(BantuanRequest::class, 'target_id');
        }
}
