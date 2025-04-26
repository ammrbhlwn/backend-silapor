<?php

namespace App\Http\Controllers;

use App\Enums\TipeLapangan;
use App\Models\Lapangan;
use App\Models\TransaksiBooking;
use Illuminate\Http\Request;

class PengelolaController extends Controller
{
    public function lihat_daftar_transaksi(Request $request)
    {
        try {
            $user = $request->user();;
            $transaksi = TransaksiBooking::with('user', 'lapangan')
                ->whereHas('lapangan', function ($query) use ($user) {
                    $query->where('user_id', $user);
                })
                ->get();
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
            $transaksi = TransaksiBooking::with('user', 'lapangan')->find($id);
            $this->authorize('view', $transaksi->lapangan);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json($transaksi);
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

        try {
            $path = $request->file('foto')->store('foto-lapangan', 'public');

            $lapangan = Lapangan::create([
                'user_id' => $request->user()->id,
                'nama' => $request->nama,
                'foto' => $path, // SIMPAN PATH-NYA
                'harga' => $request->harga,
                'jam_buka' => $request->jam_buka,
                'jam_tutup' => $request->jam_tutup,
                'kota' => $request->kota,
                'lokasi' => $request->lokasi,
                'link_lokasi' => $request->link_lokasi,
                'tipe_lapangan' => TipeLapangan::from($request->tipe_lapangan),
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
            $user = $request->user()->id;
            $lapangan = Lapangan::where('id', $id)->where('user_id', $user)->first();

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
            $user = $request->user()->id;
            $lapangan = Lapangan::where('id', $id)->where('user_id', $user)->first();
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

    public function edit_status_transaksi(Request $request, $id)
    {
        $request->validate([
            'status_transaksi' => 'required|in:menunggu,disetujui,bermain,selesai,dibatalkan',
        ]);

        try {
            $user = $request->user();
            $transaksi = TransaksiBooking::with('lapangan')->find($id);

            if ($transaksi->lapangan->user_id !== $user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $transaksi->status_transaksi = $request->status_transaksi;
            $transaksi->save();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Status transaksi diperbarui',
            'data' => $transaksi
        ], 200);
    }
}
