<?php

namespace App\Http\Controllers;

use App\Models\Materi;
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
            'id_course' => 'required|exists:kursus,id_course',
            'level' => 'required|integer|min:1',
            'judul' => 'required|string|max:255',
            'tipe' => 'required|in:video,teori',
            'url_video' => 'nullable|string|max:255',
            'teks_teori' => 'nullable|string',
        ]);

        // Pastikan cuma salah satu yang terisi
        if ($data['tipe'] === 'video') {
            $data['teks_teori'] = null;
        } else {
            $data['url_video'] = null;
        }

        $materi = Materi::create($data);
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
            'id_course' => 'sometimes|required|exists:kursus,id_course',
            'level' => 'sometimes|required|integer|min:1',
            'judul' => 'sometimes|required|string|max:255',
            'tipe' => 'sometimes|required|in:video,teori',
            'url_video' => 'nullable|string|max:255',
            'teks_teori' => 'nullable|string',
        ]);

        if (isset($data['tipe'])) {
            if ($data['tipe'] === 'video') {
                $data['teks_teori'] = null;
            } else {
                $data['url_video'] = null;
            }
        }

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
}
