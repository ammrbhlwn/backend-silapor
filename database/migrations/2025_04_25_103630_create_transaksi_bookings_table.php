<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_bookings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_booking');
            $table->time('jam_booking');
            $table->integer('total_harga');
            $table->string('bukti_pembayaran');
            $table->enum('status_transaksi', ['menunggu', 'disetujui', 'bermain', 'selesai', 'dibatalkan']);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lapangan_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_bookings');
    }
};
