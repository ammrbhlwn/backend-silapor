<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function daftar_lapangan_badminton()
    {
        try {
            $lapangans = Lapangan::where('tipe_lapangan', 'badminton')->get();
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
            $lapangans = Lapangan::where('tipe_lapangan', 'futsal')->get();
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
            $lapangan = Lapangan::with(['pengelola', 'jadwals'])->find($id);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Detail lapangan',
            'data' => $lapangan
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
