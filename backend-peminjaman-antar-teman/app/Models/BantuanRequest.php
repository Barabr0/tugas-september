<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BantuanRequest extends Model
{
    protected $table = 'bantuan_requests';
    protected $guarded = ['id'];

     public function peminta()
    {
        return $this->belongsTo(User::class, 'peminta_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
