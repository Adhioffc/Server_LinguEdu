<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoalKuis extends Model
{
    protected $table = 'soal_kuis';
    protected $primaryKey = 'id_soal_kuis';

    protected $fillable = [
        'id_kuis',
        'pertanyaan',
        'jawaban_bnr',
    ];

    public $timestamps = true;
}
