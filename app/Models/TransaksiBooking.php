<?php

namespace App\Models;

use App\Enums\StatusTransaksi;
use Illuminate\Database\Eloquent\Model;

class TransaksiBooking extends Model
{
    protected $fillable = [
        'nama',
        'nomor',
        'tanggal_booking',
        'jam_mulai',
        'jam_selesai',
        'total_harga',
        'bukti_pembayaran',
        'status_transaksi',
        'lapangan_id',
        'booking_trx_id',
    ];

    protected $casts = [
        'status_transaksi' => StatusTransaksi::class,
    ];

    public static function generateUniqueTrxId()
    {
        $prefix = 'SLP';
        do {
            $randomString = $prefix . mt_rand(1000, 9999);
        } while (self::where('booking_trx_id', $randomString)->exists());

        return $randomString;
    }

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }
}
