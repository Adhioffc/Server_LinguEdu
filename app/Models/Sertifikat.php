<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sertifikat extends Model
{
    protected $table = 'sertifikat';
    protected $primaryKey = 'kode_hasil_sertif';

    protected $fillable = [
        'id_admin',
        'id_course',
        'id_member',
        'kode_tes',
        'format',
    ];

    public $timestamps = true;
}
