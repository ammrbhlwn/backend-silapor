<?php

namespace App\Models;

use App\Enums\TipeLapangan;
use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    protected $fillable = [
        'user_id',
        'nama',
        'tipe_lapangan',
        'foto',
        'harga',
        'jam_buka',
        'jam_tutup',
        'kota',
        'lokasi',
        'link_lokasi',
    ];

    protected $casts = [
        'tipe_lapangan' => TipeLapangan::class,
    ];

    public function pengelola()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorite', 'lapangan_id', 'user_id');
    }

    public function jadwals()
    {
        return $this->hasMany(JadwalLapangan::class, 'lapangan_id');
    }

    public function transaksiBookings()
    {
        return $this->hasMany(TransaksiBooking::class, 'lapangan_id');
    }
}
