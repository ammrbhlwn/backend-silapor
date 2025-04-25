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
}
