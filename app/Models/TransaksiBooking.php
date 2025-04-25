<?php

namespace App\Models;

use App\Enums\StatusTransaksi;
use Illuminate\Database\Eloquent\Model;

class TransaksiBooking extends Model
{
    protected $fillable = [
        'tanggal_booking',
        'jam_booking',
        'total_harga',
        'bukti_pembayaran',
        'status_transaksi',
        'user_id',
        'lapangan_id',
    ];

    protected $casts = [
        'status_transaksi' => StatusTransaksi::class,
    ];
}
