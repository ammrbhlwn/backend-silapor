<?php

namespace App\Http\Controllers;

use App\Models\JadwalLapangan;
use App\Models\Lapangan;
use App\Models\TransaksiBooking;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function lihat_data_profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Success',
            'data' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function lihat_data_favorite(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites()->with('pengelola')->get();

        $data = $favorites->map(function ($lapangan) {
            return [
                'id' => $lapangan->id,
                'nama' => $lapangan->nama,
                'foto' => $lapangan->foto,
                'harga' => $lapangan->harga,
                'jam_buka' => $lapangan->jam_buka,
                'jam_tutup' => $lapangan->jam_tutup,
                'kota' => $lapangan->kota,
            ];
        });

        return response()->json([
            'message' => 'Success',
            'data' => $data,
        ]);
    }

    public function tambah_favorite(Request $request, $id)
    {
        try {
            $user = $request->user();
            $user->favorites()->syncWithoutDetaching([$id]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Success',
        ], 201);
    }

    public function hapus_favorite(Request $request, $id)
    {
        $user = $request->user();
        $user->favorites()->detach($id);

        return response()->json([
            'message' => 'Success',
        ], 200);
    }

    public function lihat_daftar_transaksi(Request $request)
    {
        $user = $request->user();
        $transaksis = $user->transaksiBookings()->with('lapangan')->get();

        $data = $transaksis->map(function ($transaksi) {
            return [
                'id' => $transaksi->id,
                'tanggal_booking' => $transaksi->tanggal_booking,
                'jam_mulai' => $transaksi->jam_mulai,
                'jam_selesai' => $transaksi->jam_selesai,
                'status_transaksi' => $transaksi->status_transaksi,
                'nama_lapangan' => $transaksi->lapangan->nama,
                'foto' => $transaksi->lapangan->foto,
            ];
        });

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Error',
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'data' => $data,
        ]);
    }

    public function lihat_detail_transaksi(Request $request, $id)
    {
        $user = $request->user();
        $transaksi = $user->transaksiBookings()->with('lapangan')->find($id);

        if ($transaksi) {
            return response()->json([
                'message' => 'Success',
                'data' => [
                    'id' => $transaksi->id,
                    'tanggal_booking' => $transaksi->tanggal_booking,
                    'jam_mulai' => $transaksi->jam_mulai,
                    'jam_selesai' => $transaksi->jam_selesai,
                    'total_harga' => $transaksi->total_harga,
                    'bukti_pembayaran' => $transaksi->bukti_pembayaran,
                    'status_transaksi' => $transaksi->status_transaksi,
                    'nama_penyewa' => $transaksi->user->nama,
                    'nama_lapangan' => $transaksi->lapangan->nama,
                    'foto' => $transaksi->lapangan->foto,
                    'lokasi' => $transaksi->lapangan->lokasi,
                ]
            ]);
        } else {
            return response()->json([
                'message' => 'Error',
            ], 404);
        }
    }

    public function buat_transaksi(Request $request, SupabaseStorageService $supabase)
    {
        $request->validate([
            'nama' => 'required|string',
            'nomor' => 'required|string',
            'lapangan_id' => 'required|integer',
            'tanggal_booking' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $lapangan = Lapangan::find($request->lapangan_id);

            if (!$lapangan) {
                return response()->json([
                    'message' => 'Error',
                ], 422);
            }

            $path = 'transaksi/' . $lapangan->id;
            $fotoUrl = $supabase->uploadImage($request->file('bukti_pembayaran'), $path);

            // Jam booking harus dalam range jam buka - jam tutup
            $jamBuka = substr($lapangan->jam_buka, 0, 5);
            $jamTutup = substr($lapangan->jam_tutup, 0, 5);
            $jamMulai = $request->jam_mulai;
            $jamSelesai = $request->jam_selesai;

            if ($jamMulai > $jamSelesai) {
                return response()->json([
                    'message' => 'Error',
                ], 422);
            }

            if ($jamMulai === $jamSelesai) {
                return response()->json([
                    'message' => 'Error',
                ], 422);
            }

            if ($jamMulai < $jamBuka || $jamSelesai > $jamTutup) {
                return response()->json([
                    'message' => 'Error',
                ], 422);
            }

            // hitung durasi booking
            $jamInterval = [];
            $jamMulai = new \DateTime($request->jam_mulai);
            $jamSelesai = new \DateTime($request->jam_selesai);

            $jamMulaiFormatted = $jamMulai->format('H:i');
            $jamSelesaiFormatted = $jamSelesai->format('H:i');

            while ($jamMulai < $jamSelesai) {
                $jamInterval[] = $jamMulai->format('H:i');
                $jamMulai->add(new \DateInterval('PT1H'));
            }

            // Cek tanggal booking di jadwal lapangan
            $cekTanggal = JadwalLapangan::where('lapangan_id', $lapangan->id)
                ->where('tanggal', $request->tanggal_booking)
                ->exists();

            if (!$cekTanggal) {
                return response()->json([
                    'message' => 'Error',
                ], 422);
            }

            // Cek jam tersedia
            $cekJadwal = JadwalLapangan::where('lapangan_id', $lapangan->id)
                ->where('tanggal', $request->tanggal_booking)
                ->whereIn('jam', $jamInterval)
                ->where('jadwal_tersedia', 'dipesan')
                ->exists();

            if ($cekJadwal) {
                return response()->json([
                    'message' => 'Error',
                ], 422);
            }

            // Update jadwal menjadi dipesan
            JadwalLapangan::where('lapangan_id', $lapangan->id)
                ->where('tanggal', $request->tanggal_booking)
                ->whereIn('jam', $jamInterval)
                ->update(['jadwal_tersedia' => 'dipesan']);

            // Hitung total harga (jumlah jam * harga per jam)
            $jumlahJam = count($jamInterval);
            $totalHarga = $jumlahJam * $lapangan->harga;

            $transaksi = TransaksiBooking::create([
                'nama' => $request->nama,
                'nomor' => $request->nomor,
                'lapangan_id' => $request->lapangan_id,
                'tanggal_booking' => $request->tanggal_booking,
                'jam_mulai' => $jamMulaiFormatted,
                'jam_selesai' => $jamSelesaiFormatted,
                'total_harga' => $totalHarga,
                'bukti_pembayaran' => $fotoUrl,
                'booking_trx_id' => TransaksiBooking::generateUniqueTrxId(),
                'status_transaksi' => 'menunggu',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Success',
            'data' => $transaksi,
        ], 201);
    }

    public function lihat_status_transaksi(Request $request)
    {
        $request->validate([
            'nomor' => 'required|string',
            'booking_trx_id' => 'required|string',
        ]);

        $booking = TransaksiBooking::with('lapangan')
            ->where('nomor', $request->nomor)
            ->where('booking_trx_id', $request->booking_trx_id)
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'Error'
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'data' => [
                'id' => $booking->id,
                'tanggal_booking' => $booking->tanggal_booking,
                'jam_mulai' => $booking->jam_mulai,
                'jam_selesai' => $booking->jam_selesai,
                'total_harga' => $booking->total_harga,
                'bukti_pembayaran' => $booking->bukti_pembayaran,
                'status_transaksi' => $booking->status_transaksi,
                'nama_penyewa' => $booking->nama,
                'nomor_hp' => $booking->nomor,
                'nama_lapangan' => $booking->lapangan->nama,
                'foto' => $booking->lapangan->foto,
                'lokasi' => $booking->lapangan->lokasi,
            ]
        ]);
    }

    public function cek_harga(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|integer',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        $lapangan = Lapangan::find($request->lapangan_id);
        if (!$lapangan) {
            return response()->json([
                'message' => 'Lapangan tidak ditemukan',
            ], 404);
        }

        $jamMulai = new \DateTime($request->jam_mulai);
        $jamSelesai = new \DateTime($request->jam_selesai);

        if ($jamMulai >= $jamSelesai) {
            return response()->json([
                'message' => 'Jam mulai harus sebelum jam selesai',
            ], 422);
        }

        $jumlahJam = $jamSelesai->diff($jamMulai)->h;
        $totalHarga = $jumlahJam * $lapangan->harga;

        return response()->json([
            'message' => 'Success',
            'data' => [
                'jumlah_jam' => $jumlahJam,
                'harga_per_jam' => $lapangan->harga,
                'total_harga' => $totalHarga,
            ],
        ]);
    }
}
