<?php

namespace App\Models;

use App\Enums\JadwalTersedia;
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
        'jadwal_tersedia' => JadwalTersedia::class,
    ];

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }
}
