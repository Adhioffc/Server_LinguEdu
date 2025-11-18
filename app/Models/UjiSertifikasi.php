<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UjiSertifikasi extends Model
{
    protected $table = 'uji_sertifikasi';
    protected $primaryKey = 'kode_tes';

    protected $fillable = [
        'id_member',
        'id_materi',
        'id_course',
        'id_admin',
        'tgl',
        'skor',
    ];

    public $timestamps = true;
}
