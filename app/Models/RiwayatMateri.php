<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatMateri extends Model
{
    use HasFactory;

    // 👇 INI YANG KURANG TADI
    protected $fillable = [
        'id_member',
        'id_materi',
        'is_completed',
        'has_passed_quiz',
    ];

    // (Opsional) Relasi kalau nanti butuh
    public function member()
    {
        return $this->belongsTo(Member::class, 'id_member');
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'id_materi');
    }
}
