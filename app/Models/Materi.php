<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $table = 'materi';
    protected $primaryKey = 'id_materi';

    protected $fillable = [
        'id_course',
        'judul',
        'tipe',
        'url_video',
        'teks_teori',
    ];

    public $timestamps = true;
}
