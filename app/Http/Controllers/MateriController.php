<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Kursus;
use App\Models\Paket;
use App\Models\Bahasa;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    // GET /api/admin/materi
    public function index()
    {
        $materi = Materi::with('course.bahasa', 'course.paket')->get();

        return response()->json([
            'data' => $materi,
        ]);
    }

    // POST /api/admin/materi
    public function store(Request $request)
    {
        $data = $request->validate([
            // ⬅️ perbaiki: tabel paket & bahasa pakai kolom id
            'id_paket' => 'required|exists:paket,id',
            'id_bahasa' => 'required|exists:bahasa,id',
            'level' => 'required|integer|min:1|max:3',
            'judul' => 'required|string|max:255',
            'url_video' => 'nullable|string|max:255',
            'teks_teori' => 'nullable|string',
        ]);

        // Ambil paket & bahasa
        $paket = Paket::findOrFail($data['id_paket']);
        $bahasa = Bahasa::findOrFail($data['id_bahasa']);

        // Sama kayak registrasiKursus → bikin / ambil kursus
        $kursus = Kursus::firstOrCreate(
            [
                'id_paket' => $paket->id,
                'id_bahasa' => $bahasa->id,
            ],
            [
                'deskripsi' => "Kursus {$bahasa->nama_bahasa} - Paket {$paket->nama_paket}",
            ]
        );

        $materiData = [
            'id_course' => $kursus->id_course,
            'level' => $data['level'],
            'judul' => $data['judul'],
            'url_video' => $data['url_video'] ?? null,
            'teks_teori' => $data['teks_teori'] ?? null,
        ];

        // isi tipe otomatis
        $materiData['tipe'] = $this->resolveTipe(
            $materiData['url_video'],
            $materiData['teks_teori'],
        );

        $materi = Materi::create($materiData);
        $materi->load('course.bahasa', 'course.paket');

        return response()->json([
            'message' => 'Materi created',
            'data' => $materi,
        ], 201);
    }

    // PUT /api/admin/materi/{id_materi}
    public function update(Request $request, $id_materi)
    {
        $materi = Materi::findOrFail($id_materi);

        $data = $request->validate([
            // kalau mau ganti paket/bahasa, kirim dua-duanya
            'id_paket' => 'sometimes|exists:paket,id',
            'id_bahasa' => 'sometimes|exists:bahasa,id',
            'level' => 'sometimes|integer|min:1|max:3',
            'judul' => 'sometimes|string|max:255',
            'url_video' => 'sometimes|nullable|string|max:255',
            'teks_teori' => 'sometimes|nullable|string',
        ]);

        // Kalau user ganti paket/bahasa → tentukan kursus baru
        if (array_key_exists('id_paket', $data) || array_key_exists('id_bahasa', $data)) {
            // ambil id paket & bahasa final
            $materi->load('course.bahasa', 'course.paket');

            $idPaket = $data['id_paket'] ?? $materi->course->id_paket;
            $idBahasa = $data['id_bahasa'] ?? $materi->course->id_bahasa;

            $paket = Paket::findOrFail($idPaket);
            $bahasa = Bahasa::findOrFail($idBahasa);

            $kursus = Kursus::firstOrCreate(
                [
                    'id_paket' => $paket->id,
                    'id_bahasa' => $bahasa->id,
                ],
                [
                    'deskripsi' => "Kursus {$bahasa->nama_bahasa} - Paket {$paket->nama_paket}",
                ]
            );

            $data['id_course'] = $kursus->id_course;

            // buang id_paket, id_bahasa dari array (kolom ini memang nggak ada di materi)
            unset($data['id_paket'], $data['id_bahasa']);
        }

        // hitung tipe berdasarkan data baru+lama
        $url = $data['url_video'] ?? $materi->url_video;
        $teks = $data['teks_teori'] ?? $materi->teks_teori;

        $data['tipe'] = $this->resolveTipe($url, $teks);

        $materi->update($data);
        $materi->load('course.bahasa', 'course.paket');

        return response()->json([
            'message' => 'Materi updated',
            'data' => $materi,
        ]);
    }

    // DELETE /api/admin/materi/{id_materi}
    public function destroy($id_materi)
    {
        $materi = Materi::findOrFail($id_materi);
        $materi->delete();

        return response()->json([
            'message' => 'Materi deleted',
        ]);
    }

    /**
     * tipe otomatis:
     * - teori     : cuma teks
     * - video     : cuma url_video
     * - campuran  : kedua-duanya ada
     * - kosong    : dua-duanya null
     */
    private function resolveTipe(?string $urlVideo, ?string $teksTeori): string
    {
        $hasUrl = !empty($urlVideo);
        $hasText = !empty($teksTeori);

        if ($hasUrl && $hasText)
            return 'campuran';
        if ($hasUrl)
            return 'video';
        if ($hasText)
            return 'teori';
        return 'kosong';
    }
}
