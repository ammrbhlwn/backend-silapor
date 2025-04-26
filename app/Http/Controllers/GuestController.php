<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function daftar_lapangan_badminton()
    {
        try {
            $lapangans = Lapangan::select(
                'id',
                'nama',
                'foto',
                'harga',
                'jam_buka',
                'jam_tutup',
                'kota'
            )
                ->where('tipe_lapangan', 'badminton')
                ->get();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Daftar lapangan badminton',
            'data' => $lapangans
        ]);
    }

    public function daftar_lapangan_futsal()
    {
        try {
            $lapangans = Lapangan::select(
                'id',
                'nama',
                'foto',
                'harga',
                'jam_buka',
                'jam_tutup',
                'kota'
            )
                ->where('tipe_lapangan', 'futsal')
                ->get();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Daftar lapangan futsal',
            'data' => $lapangans
        ]);
    }

    public function lihat_detail_lapangan($id)
    {
        try {
            $lapangan = Lapangan::with(['jadwals'])->find($id);

            $groupedJadwal = $lapangan->jadwals->groupBy('tanggal')->map(function ($items) {
                return $items->map(function ($jadwal) {
                    return [
                        'jam' => $jadwal->jam,
                        'jadwal_tersedia' => $jadwal->jadwal_tersedia,
                    ];
                });
            });

            $data = [
                'id' => $lapangan->id,
                'nama' => $lapangan->nama,
                'foto' => $lapangan->foto,
                'harga' => $lapangan->harga,
                'jam_buka' => $lapangan->jam_buka,
                'jam_tutup' => $lapangan->jam_tutup,
                'kota' => $lapangan->kota,
                'lokasi' => $lapangan->lokasi,
                'link_lokasi' => $lapangan->link_lokasi,
                'jadwal' => $groupedJadwal,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Detail lapangan',
            'data' => $data
        ]);
    }

    public function search_lapangan(Request $request)
    {
        try {
            $request->validate([
                'kota' => 'required|string',
            ]);

            $query = Lapangan::query();
            $query->where('kota', 'like', '%' . $request->kota . '%');
            $lapangans = $query->get();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Hasil pencarian lapangan',
            'data' => $lapangans
        ], 200);
    }
}
