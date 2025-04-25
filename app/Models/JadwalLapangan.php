<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalLapangan extends Model
{
    protected $fillable = [
        'lapangan_id',
        'tanggal',
        'jam',
        'jadwal_tersedia',
    ];

    protected $casts = [
        'jadwal_tersedia' => JadwalLapangan::class,
    ];
}
