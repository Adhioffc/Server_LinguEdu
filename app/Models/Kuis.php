<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuis extends Model
{
    protected $table = 'kuis';
    protected $primaryKey = 'id_kuis';

    protected $fillable = [
        'id_member',
        'id_materi',
        'id_course',
        'id_admin',
    ];

    public $timestamps = true;
}
