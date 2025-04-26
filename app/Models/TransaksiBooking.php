<?php

namespace App\Models;

use App\Enums\StatusTransaksi;
use Illuminate\Database\Eloquent\Model;

class TransaksiBooking extends Model
{
    protected $fillable = [
        'tanggal_booking',
        'jam_mulai',
        'jam_selesai',
        'total_harga',
        'bukti_pembayaran',
        'status_transaksi',
        'user_id',
        'lapangan_id',
    ];

    protected $casts = [
        'status_transaksi' => StatusTransaksi::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }
}
