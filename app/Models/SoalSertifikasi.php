<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoalSertifikasi extends Model
{
    protected $table = 'soal_sertifikasi';
    protected $primaryKey = 'id_soal';

    protected $fillable = [
        'kode_tes',
        'pertanyaan',
        'jawaban_benar',
    ];

    public $timestamps = true;
}
