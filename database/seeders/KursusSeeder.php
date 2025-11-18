<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kursus;

class KursusSeeder extends Seeder
{
    public function run(): void
    {
        Kursus::create([
            'deskripsi' => 'Kursus bahasa Inggris tingkat pemula',
        ]);
    }
}
