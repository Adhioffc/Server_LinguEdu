<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\UjiSertifikasi;
use Illuminate\Http\Request;

class UjiSertifikasiController extends Controller
{
    // GET /api/admin/sertifikasi/tes
    // List semua uji sertifikasi + materi + course + jumlah soal
    public function index()
    {
        $uji = UjiSertifikasi::with([
            'materi.course.bahasa',
            'materi.course.paket',
            'soalSertifikasi',
        ])->get();

        // tambahkan field jumlah_soal biar enak di FE
        $uji->each(function ($item) {
            $item->jumlah_soal = $item->soalSertifikasi->count();
        });

        return response()->json([
            'data' => $uji,
        ]);
    }

    // POST /api/admin/sertifikasi/tes
    // body:
    // {
    //   "id_materi": 4,
    //   "kkm": 70  // optional, 0-100
    // }
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_materi' => 'required|exists:materi,id_materi',
            'kkm' => 'nullable|integer|min:0|max:100',
        ]);

        $materi = Materi::with('course')->findOrFail($data['id_materi']);

        $uji = UjiSertifikasi::create([
            'id_materi' => $materi->id_materi,
            'id_course' => $materi->id_course,
            'tgl' => now()->toDateString(),
            'skor' => $data['kkm'] ?? 70,   // dipakai sebagai passing score (KKM)
        ]);

        $uji->load('materi.course.bahasa', 'materi.course.paket', 'soalSertifikasi');

        return response()->json([
            'message' => 'Uji sertifikasi dibuat',
            'data' => $uji,
        ], 201);
    }

    // DELETE /api/admin/sertifikasi/tes/{kode_tes}
    // Hapus uji + semua soal sertifikasinya
    public function destroy($kode_tes)
    {
        $uji = UjiSertifikasi::with('soalSertifikasi')->findOrFail($kode_tes);

        $uji->soalSertifikasi()->delete();
        $uji->delete();

        return response()->json([
            'message' => 'Uji sertifikasi dihapus',
        ]);
    }
}
