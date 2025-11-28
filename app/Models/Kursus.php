<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kursus extends Model
{
    use HasFactory;

    protected $table = 'kursus';
    protected $primaryKey = 'id_course';

    protected $fillable = [
        'id_bahasa',
        'id_paket',
        'deskripsi',
    ];

    public function bahasa()
    {
        return $this->belongsTo(Bahasa::class, 'id_bahasa', 'id');
    }

    public function paket()
    {
        // foreign key: id_paket (di kursus), owner key: id (di paket)
        return $this->belongsTo(Paket::class, 'id_paket', 'id');
    }
}
