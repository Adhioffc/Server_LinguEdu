<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Materi;

class MateriSeeder extends Seeder
{
    public function run(): void
    {
        Materi::create([
            'id_course' => 1,
            'judul' => 'Introduction to English',
            'tipe' => 'video',
            'url_video' => 'https://example.com/video1',
            'teks_teori' => null,
        ]);
    }
}
