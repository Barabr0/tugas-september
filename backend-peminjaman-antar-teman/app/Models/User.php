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
}
