<?php

namespace App\Enums;

enum StatusTransaksi: string
{
    case Menunggu = 'menunggu';
    case Disetujui = 'disetujui';
    case Bermain = 'bermain';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';
}
