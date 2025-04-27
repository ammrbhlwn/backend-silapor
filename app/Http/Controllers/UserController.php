<?php

namespace App\Http\Controllers;

use App\Models\JadwalLapangan;
use App\Models\Lapangan;
use App\Models\TransaksiBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function lihat_data_profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Data profil pengguna',
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
            'message' => 'Daftar lapangan favorit',
            'data' => $data,
        ]);
    }

    public function tambah_favorite(Request $request)
    {
        try {
            $request->validate([
                'lapangan_id' => 'required|exists:lapangans,id',
            ]);

            $user = $request->user();
            $user->favorites()->syncWithoutDetaching([$request->lapangan_id]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

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
                'message' => 'Tidak ada transaksi',
            ], 404);
        }

        return response()->json([
            'message' => 'Daftar transaksi pengguna',
            'data' => $data,
        ]);
    }

    public function lihat_detail_transaksi(Request $request, $id)
    {
        $user = $request->user();
        $transaksi = $user->transaksiBookings()->with('lapangan')->find($id);

        if ($transaksi) {
            return response()->json([
                'message' => 'Detail transaksi pengguna',
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
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }
    }

    public function buat_transaksi(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|integer',
            'tanggal_booking' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'bukti_pembayaran' => 'required|file|mimes:jpg,jpeg,png,pdf',
        ]);

        try {
            $user = $request->user();
            $lapangan = Lapangan::find($request->lapangan_id);

            if (!$lapangan) {
                return response()->json([
                    'message' => 'Silahkan pilih lapangan yang tersedia.',
                ], 422);
            }

            $file = $request->file('bukti_pembayaran');
            $path = $file->store('bukti_pembayaran', 'public');

            // Jam booking harus dalam range jam buka - jam tutup
            $jamBuka = $lapangan->jam_buka;
            $jamTutup = $lapangan->jam_tutup;
            $jamMulai = $request->jam_mulai;
            $jamSelesai = $request->jam_selesai;

            if ($jamMulai > $jamBuka || $jamSelesai->gt($jamTutup)) {
                return response()->json([
                    'message' => 'Jam booking harus dalam jam buka dan jam tutup lapangan',
                ], 422);
            }

            if ($jamMulai->gte($jamSelesai) && !$jamMulai->eq($jamSelesai)) {
                return response()->json([
                    'message' => 'Jam mulai harus sebelum jam selesai.',
                ], 422);
            }

            if ($jamMulai->eq($jamSelesai)) {
                return response()->json([
                    'message' => 'Jam mulai dan jam selesai tidak boleh sama',
                ], 422);
            }

            // hitung durasi booking
            $jamInterval = [];
            for ($jam = $jamMulai->copy(); $jam->lt($jamSelesai); $jam->addHour()) {
                $jamInterval[] = $jam->format('H:i:s');
            }

            // Cek tanggal booking di jadwal lapangan
            $cekTanggal = JadwalLapangan::where('lapangan_id', $lapangan->id)
                ->where('tanggal', $request->tanggal_booking)
                ->exists();

            if (!$cekTanggal) {
                return response()->json([
                    'message' => 'Tanggal booking tidak tersedia di jadwal lapangan',
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
                    'message' => 'Jam ' . $request->jam_mulai . ' - ' . $request->jam_selesai . ' sudah dibooking, silahkan pilih jam lain',
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
                'lapangan_id' => $request->lapangan_id,
                'user_id' => $user->id,
                'tanggal_booking' => $request->tanggal_booking,
                'jam_mulai' => $jamMulai->format('H:i'),
                'jam_selesai' => $jamSelesai->format('H:i'),
                'total_harga' => $totalHarga,
                'bukti_pembayaran' => $path,
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
