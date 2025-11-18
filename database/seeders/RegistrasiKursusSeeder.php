<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RegistrasiKursus;

class RegistrasiKursusSeeder extends Seeder
{
    public function run(): void
    {
        RegistrasiKursus::create([
            'id_admin' => 1,
            'id_member' => 1,
            'id_course' => 1,
            'tgl_trans' => now(),
            'metode_bayar' => 'Transfer',
            'total_byr' => 250000,
            'bukti_byr' => null,
            'progress' => 0,
            'level' => 'Beginner',
        ]);
    }
}
