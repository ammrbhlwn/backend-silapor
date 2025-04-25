<?php

namespace App\Http\Controllers;

use App\Models\TransaksiBooking;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function lihat_data_profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Data profil pengguna',
            'data' => $user,
        ]);
    }

    public function lihat_data_favorite(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites()->with('pengelola')->get();

        return response()->json([
            'message' => 'Daftar lapangan favorit',
            'data' => $favorites,
        ]);
    }

    public function tambah_favorite(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangans,id',
        ]);

        try {
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        $user = $request->user();

        $user->favorites()->syncWithoutDetaching([$request->lapangan_id]);

        return response()->json([
            'message' => 'Lapangan berhasil ditambahkan ke favorit',
        ], 201);
    }

    public function hapus_favorite(Request $request, $id)
    {
        $user = $request->user();

        $user->favorites()->detach($id);

        return response()->json([
            'message' => 'Lapangan berhasil dihapus dari favorit',
        ], 200);
    }

    public function lihat_daftar_transaksi(Request $request)
    {
        $user = $request->user();
        $transaksis = $user->transaksiBookings()->with('lapangan')->get();

        return response()->json([
            'message' => 'Daftar transaksi pengguna',
            'data' => $transaksis,
        ]);
    }

    public function lihat_detail_transaksi(Request $request, $id)
    {
        $user = $request->user();
        $transaksi = $user->transaksiBookings()->with('lapangan')->findOrFail($id);

        return response()->json([
            'message' => 'Detail transaksi pengguna',
            'data' => $transaksi,
        ]);
    }

    public function buat_transaksi(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangans,id',
            'tanggal_booking' => 'required|date',
            'jam_booking' => 'required|date_format:H:i',
            'total_harga' => 'required|numeric',
            'bukti_pembayaran' => 'required|string',
        ]);

        try {
            $user = $request->user();

            $transaksi = TransaksiBooking::create([
                'lapangan_id' => $request->lapangan_id,
                'user_id' => $user->id,
                'tanggal_booking' => $request->tanggal_booking,
                'jam_booking' => $request->jam_booking,
                'total_harga' => $request->total_harga,
                'bukti_pembayaran' => $request->bukti_pembayaran,
                'status_transaksi' => 'menunggu',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Transaksi berhasil dibuat',
            'data' => $transaksi,
        ], 201);
    }
}
