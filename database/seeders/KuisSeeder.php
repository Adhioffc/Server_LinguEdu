<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kuis;

class KuisSeeder extends Seeder
{
    public function run(): void
    {
        Kuis::create([
            'id_member' => 1,
            'id_materi' => 1,
            'id_course' => 1,
            'id_admin' => 1,
        ]);
    }
}
