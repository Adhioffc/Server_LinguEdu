<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SoalSertifikasi;

class SoalSertifikasiSeeder extends Seeder
{
    public function run(): void
    {
        SoalSertifikasi::create([
            'kode_tes' => 1,
            'pertanyaan' => 'Translate: "Good Morning"',
            'jawaban_benar' => 'Selamat Pagi',
        ]);
    }
}
