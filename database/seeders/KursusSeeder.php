<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KursusSeeder extends Seeder
{
    public function run(): void
    {
        // asumsi:
        // paket: 1 = Basic, 2 = Intermediate, 3 = Advanced
        // bahasa: 1 = Inggris, 2 = Jepang, 3 = Korea

        DB::table('kursus')->insert([
            [
                'id_bahasa' => 1,
                'id_paket' => 2, // Intermediate
                'deskripsi' => 'Bahasa Inggris Intermediate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_bahasa' => 2,
                'id_paket' => 2,
                'deskripsi' => 'Bahasa Jepang Intermediate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_bahasa' => 3,
                'id_paket' => 2,
                'deskripsi' => 'Bahasa Korea Intermediate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
