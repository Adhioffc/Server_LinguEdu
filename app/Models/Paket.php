<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paket extends Model
{
    use HasFactory;

    protected $table = 'paket';
    protected $primaryKey = 'id_paket';

    protected $fillable = [
        'nama_paket',
        'desc',
        'harga',
    ];

    public function kursus()
    {
        return $this->hasMany(Kursus::class, 'id_paket', 'id_paket');
    }
}
