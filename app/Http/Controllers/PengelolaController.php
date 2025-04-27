<?php

namespace App\Http\Controllers;

use App\Models\JadwalLapangan;
use App\Models\Lapangan;
use App\Models\TransaksiBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengelolaController extends Controller
{
    public function lihat_daftar_transaksi(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $transaksi = TransaksiBooking::with('user', 'lapangan')
                ->whereHas('lapangan', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'tanggal_booking' => $item->tanggal_booking,
                        'jam_mulai' => $item->jam_mulai,
                        'jam_selesai' => $item->jam_selesai,
                        'status_transaksi' => $item->status_transaksi,
                        'nama_lapangan' => $item->lapangan->nama,
                        'foto' => $item->lapangan->foto,
                    ];
                });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json($transaksi);
    }

    public function lihat_detail_transaksi($id)
    {
        try {
            $transaksi = TransaksiBooking::with('lapangan')->find($id);

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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function tambah_lapangan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'tipe_lapangan' => 'required|in:futsal,badminton',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'harga' => 'required|integer',
            'jam_buka' => 'required',
            'jam_tutup' => 'required',
            'kota' => 'required|string',
            'lokasi' => 'required|string',
            'link_lokasi' => 'required|string',
        ]);

        // Validasi  harga
        if ($request->harga <= 0) {
            return response()->json([
                'message' => 'Harga harus lebih dari 0',
            ], 422);
        }

        // Validasi jam buka & jam tutup
        $jamBuka = $request->jam_buka;
        $jamTutup = $request->jam_tutup;

        if ($jamBuka == $jamTutup) {
            return response()->json([
                'message' => 'Jam buka dan jam tutup tidak boleh sama',
            ], 422);
        }

        if ($jamBuka > $jamTutup) {
            return response()->json([
                'message' => 'Jam buka tidak boleh setelah jam tutup',
            ], 422);
        }

        try {
            $path = $request->file('foto')->store('foto-lapangan', 'public');

            $lapangan = Lapangan::create([
                'user_id' => $request->user()->id,
                'nama' => $request->nama,
                'foto' => $path,
                'harga' => $request->harga,
                'jam_buka' => $request->jam_buka,
                'jam_tutup' => $request->jam_tutup,
                'kota' => $request->kota,
                'lokasi' => $request->lokasi,
                'link_lokasi' => $request->link_lokasi,
                'tipe_lapangan' => $request->tipe_lapangan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Lapangan berhasil ditambahkan',
            'data' => $lapangan
        ], 201);
    }

    public function edit_data_lapangan(Request $request, $id)
    {
        $request->validate([
            'nama' => 'nullable|string',
            'tipe_lapangan' => 'nullable|in:futsal,badminton',
            'harga' => 'nullable|integer',
            'jam_buka' => 'nullable',
            'jam_tutup' => 'nullable',
            'kota' => 'nullable|string',
            'lokasi' => 'nullable|string',
            'link_lokasi' => 'nullable|string',
        ]);

        try {
            $userId = $request->user()->id;
            $lapangan = Lapangan::where('id', $id)->where('user_id', $userId)->first();

            if (!$lapangan) {
                return response()->json([
                    'message' => 'Lapangan tidak ditemukan.',
                ], 404);
            }

            // Validasi harga
            if ($request->has('harga') && $request->harga <= 0) {
                return response()->json([
                    'message' => 'Harga harus lebih dari 0',
                ], 422);
            }

            // Validasi jam buka dan jam tutup
            if ($request->has('jam_buka') && $request->has('jam_tutup')) {
                $jamBuka = $request->jam_buka;
                $jamTutup = $request->jam_tutup;

                if ($jamBuka === $jamTutup) {
                    return response()->json([
                        'message' => 'Jam buka dan jam tutup tidak boleh sama',
                    ], 422);
                }

                if ($jamBuka > $jamTutup) {
                    return response()->json([
                        'message' => 'Jam buka tidak boleh setelah jam tutup',
                    ], 422);
                }
            }

            $lapangan->update($request->only([
                'nama',
                'tipe_lapangan',
                'harga',
                'jam_buka',
                'jam_tutup',
                'kota',
                'lokasi',
                'link_lokasi',
            ]));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Lapangan berhasil diperbarui',
            'data' => $lapangan
        ], 200);
    }

    public function hapus_lapangan(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;
            $lapangan = Lapangan::where('id', $id)->where('user_id', $userId)->first();

            if (!$lapangan) {
                return response()->json([
                    'message' => 'Lapangan tidak ditemukan',
                ], 404);
            }

            $lapangan->delete();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'message' => 'Lapangan berhasil dihapus'
        ], 200);
    }

    public function tambah_jadwal(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam' => 'required|array',
            'jam.*' => 'required|date_format:H:i',
        ]);

        try {
            $lapangan = Lapangan::find($id);

            if (!$lapangan) {
                return response()->json([
                    'message' => 'Lapangan tidak ditemukan.',
                ], 404);
            }

            // Ambil jam buka dan tutup lapangan
            $jamBuka = substr($lapangan->jam_buka, 0, 5);
            $jamTutup = substr($lapangan->jam_tutup, 0, 5);

            foreach ($request->jam as $jam) {
                // Validasi bahwa jam yang diminta ada di antara jam buka dan jam tutup
                if ($jam < $jamBuka || $jam > $jamTutup) {
                    return response()->json([
                        'message' => 'Jam ' . $jam . ' diluar jam operasional lapangan.',
                    ], 422);
                }

                // Cek apakah jadwal sudah ada di database
                $cek = JadwalLapangan::where('lapangan_id', $id)
                    ->where('tanggal', $request->tanggal)
                    ->where('jam', $jam)
                    ->first();

                if (!$cek) {
                    // Create kalau belum ada
                    JadwalLapangan::create([
                        'lapangan_id' => $id,
                        'tanggal' => $request->tanggal,
                        'jam' => $jam,
                        'jadwal_tersedia' => 'tersedia',
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat jadwal',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Jadwal lapangan berhasil dibuat',
        ], 201);
    }

    public function hapus_jadwal($id)
    {
        try {
            $jadwal = JadwalLapangan::find($id);

            if (!$jadwal) {
                return response()->json([
                    'message' => 'Jadwal tidak ditemukan',
                ], 404);
            }

            // Hapus jadwal
            $jadwal->delete();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Jadwal berhasil dihapus',
        ], 200);
    }

    public function edit_status_transaksi(Request $request, $id)
    {
        $request->validate([
            'status_transaksi' => 'required|in:menunggu,disetujui,bermain,selesai,dibatalkan',
        ]);

        try {
            $userId = $request->user()->id;
            $transaksi = TransaksiBooking::with('lapangan')->find($id);

            if (!$transaksi) {
                return response()->json([
                    'message' => 'Transaksi tidak ditemukan',
                ], 404);
            }

            if ($transaksi->lapangan->user_id !== $userId) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Update jadwal lapangan
            if ($request->status_transaksi === 'dibatalkan') {
                $jamInterval = [];
                $jamMulai = Carbon::parse($transaksi->jam_mulai);
                $jamSelesai = Carbon::parse($transaksi->jam_selesai);

                for ($jam = $jamMulai->copy(); $jam->lt($jamSelesai); $jam->addHour()) {
                    $jamInterval[] = $jam->format('H:i:s');
                }

                // Update jadwal lapangan menjadi tersedia
                JadwalLapangan::where('lapangan_id', $transaksi->lapangan_id)
                    ->where('tanggal', $transaksi->tanggal_booking)
                    ->whereIn('jam', $jamInterval)
                    ->update(['jadwal_tersedia' => 'tersedia']);
            }

            // Update status transaksi
            $transaksi->status_transaksi = $request->status_transaksi;
            $transaksi->save();

            $responseData = [
                'id' => $transaksi->id,
                'tanggal_booking' => $transaksi->tanggal_booking,
                'jam_mulai' => $transaksi->jam_mulai,
                'jam_selesai' => $transaksi->jam_selesai,
                'total_harga' => $transaksi->total_harga,
                'status_transaksi' => $transaksi->status_transaksi,
                'nama_lapangan' => $transaksi->lapangan->nama,
                'foto' => $transaksi->lapangan->foto,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Status transaksi diperbarui',
            'data' => $responseData
        ], 200);
    }
}
