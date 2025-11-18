<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bahasa extends Model
{
    use HasFactory;

    protected $table = 'bahasa';
    protected $primaryKey = 'id_bahasa';

    protected $fillable = [
        'nama_bahasa',
        'desc',
    ];

    public function kursus()
    {
        return $this->hasMany(Kursus::class, 'id_bahasa', 'id_bahasa');
    }
}
