<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Kursus;
use App\Models\Paket;
use App\Models\Bahasa;
use App\Models\RiwayatMateri;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    // GET /api/admin/materi/filter?paket=1&bahasa=2&level=1
    public function filter(Request $request)
    {
        $data = $request->validate([
            'paket' => 'required|exists:paket,id',   // PK paket = id
            'bahasa' => 'required|exists:bahasa,id',  // PK bahasa = id
            'level' => 'nullable|integer|min:1|max:3',
        ]);

        $materi = Materi::with('course.bahasa', 'course.paket')
            ->whereHas('course', function ($q) use ($data) {
                $q->where('id_paket', $data['paket'])
                    ->where('id_bahasa', $data['bahasa']);
            })
            ->when(isset($data['level']), function ($q) use ($data) {
                $q->where('level', $data['level']);
            })
            ->orderBy('level')
            ->orderBy('judul')
            ->get();

        return response()->json([
            'data' => $materi,
        ]);
    }

    // ==========================================
    // KHUSUS UNTUK MEMBER (FRONTEND)
    // ==========================================
    public function memberIndex()
    {
        // 1. Ambil semua materi dari Database
        // Kamu bisa memfilter berdasarkan paket/bahasa user nanti.
        // Untuk sekarang, kita ambil semua dulu biar tampil.
        $semuaMateri = Materi::orderBy('level')->get();

        // 2. Pisahkan Level 1, 2, dan 3
        // Kita "Map" (ubah format) datanya supaya cocok sama View kamu yang pakai array ['title']
        $materiLevel1 = $semuaMateri->where('level', 1)->map(function ($item) {
            return [
                'title' => $item->judul,
                'desc'  => Str::limit(strip_tags($item->teks_teori), 100) ?? 'Belajar via Video', // Ambil cuplikan teks
                'img'   => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=800&q=80', // Gambar default dulu
                'progress' => 0, // Nanti kita ambil dari tabel progress, sekarang 0 dulu biar jujur
                'slug'  => Str::slug($item->judul, '-') // Buat link url
            ];
        });

        $materiLevel2 = $semuaMateri->where('level', 2)->map(function ($item) {
            return [
                'title' => $item->judul,
                'desc'  => Str::limit(strip_tags($item->teks_teori), 100) ?? 'Lanjutan',
                'img'   => 'https://images.unsplash.com/photo-1593642634367-d91a135587b5?auto=format&fit=crop&w=800&q=80',
                'progress' => 0,
                'slug'  => Str::slug($item->judul, '-')
            ];
        });

        // 3. Kirim ke View (member/materi/index.blade.php)
        // Pastikan nama view-nya sesuai dengan folder kamu
        return view('member.materi.index', [
            'materiLevel1' => $materiLevel1,
            'materiLevel2' => $materiLevel2
        ]);
    }

    public function showBySlug($slug)
    {
        // 1. Ubah slug URL "introduction-to-programming" jadi "Introduction To Programming"
        $judul = str_replace('-', ' ', $slug);

        // 2. Cari di Database (Pakai ILIKE biar tidak sensitif huruf besar/kecil di Postgres)
        $materi = Materi::where('judul', 'ILIKE', $judul)->first();

        if (!$materi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Materi tidak ditemukan'
            ], 404);
        }

        // 3. Kembalikan data
        return response()->json([
            'status' => 'success',
            'data' => $materi
        ]);
    }

    // ==========================================
    // KHUSUS UNTUK MEMBER (PROGRESS & LIST)
    // ==========================================
    public function getMateriForMember(Request $request)
    {
        // 1. Ambil ID Member
        // Idealnya pakai $request->user()->id dari token.
        // Tapi untuk sementara kita terima input 'id_member' dari frontend biar gampang debug.
        $idMember = $request->query('id_member');

        if (!$idMember) {
            return response()->json(['message' => 'ID Member diperlukan'], 400);
        }

        // 2. Ambil Semua Materi
        $semuaMateri = Materi::with('course')->orderBy('level')->get();

        // 3. Ambil Riwayat User Ini
        $riwayat = RiwayatMateri::where('id_member', $idMember)->get();

        // 4. Gabungkan Data (Mapping)
        $data = $semuaMateri->map(function($item) use ($riwayat) {
            // Cek apakah materi ini ada di riwayat user
            $history = $riwayat->where('id_materi', $item->id_materi)->first();

            // Logic Progress:
            // Kalau ada history & completed = 100%
            // Kalau gak ada = 0%
            $progress = 0;
            if ($history) {
                if ($history->is_completed) $progress += 50; // Bobot Baca
                if ($history->has_passed_quiz) $progress += 50; // Bobot Kuis
            }

            return [
                'id_materi' => $item->id_materi,
                'judul' => $item->judul,
                'level' => $item->level,
                'teks_teori' => $item->teks_teori,
                'url_video' => $item->url_video,
                'progress' => $progress,
                'is_unlocked' => true, // Nanti kita tambah logic level disini (misal: level 2 kebuka kalau level 1 beres)
                'slug' => Str::slug($item->judul, '-')
            ];
        });

        return response()->json(['data' => $data]);
    }

    // POST /api/member/level-up
    public function levelUp(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'id_member' => 'required|integer',
            'current_level' => 'required|integer' // Level yang mau diselesaikan
        ]);

        $idMember = $request->id_member;
        $levelSelesai = $request->current_level;

        // 2. Cari Data Registrasi Kursus
        $reg = \App\Models\RegistrasiKursus::where('id_member', $idMember)->first();

        if (!$reg) {
            return response()->json(['message' => 'Member tidak terdaftar'], 404);
        }

        // 3. Cek Syarat: Apakah semua materi di level ini sudah beres?
        // Ambil ID materi di level ini
        $materiLevelIni = Materi::whereHas('course', function($q) use ($reg) {
            $q->where('id_course', $reg->id_course);
        })->where('level', $levelSelesai)->pluck('id_materi');

        // Hitung berapa yang sudah completed di tabel riwayat
        $riwayatCount = RiwayatMateri::where('id_member', $idMember)
            ->whereIn('id_materi', $materiLevelIni)
            ->where('is_completed', true)
            ->count();

        // Kalau jumlah yang selesai < jumlah total materi, tolak!
        if ($riwayatCount < $materiLevelIni->count()) {
            return response()->json(['message' => 'Eits, selesaikan semua materi & kuis dulu ya!'], 403);
        }

        // 4. SAH! Naik Level (Update Database)
        if ($reg->last_unlocked_level <= $levelSelesai) {
            $reg->last_unlocked_level = $levelSelesai + 1;
            $reg->save();
        }

        return response()->json([
            'message' => 'Selamat! Level ' . ($levelSelesai + 1) . ' berhasil dibuka!',
            'new_level' => $reg->last_unlocked_level
        ]);
    }
}
