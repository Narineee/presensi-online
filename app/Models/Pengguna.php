<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'username',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function pembimbing()
    {
        return $this->hasOne(Pembimbing::class);
    }

    public function magang()
    {
        return $this->hasOne(Magang::class);
    }

    public function cs()
    {
        return $this->hasOne(Cs::class);
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

    public function aktivitas()
    {
        return $this->hasMany(Aktivitas::class);
    }

    public function pengajuanIzin()
    {
        return $this->hasMany(PengajuanIzin::class);
    }
}
