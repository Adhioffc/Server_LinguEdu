<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrasiKursus extends Model
{
    protected $table = 'registrasi_kursus';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_admin',
        'id_member',
        'id_course',
        'tgl_trans',
        'metode_bayar',
        'total_byr',
        'bukti_byr',
        'progress',
        'level'
    ];

    public $timestamps = true;
}
