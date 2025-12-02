<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RegistrasiKursus;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * GET /api/admin/dashboard/summary
     */
    public function summary()
    {
        // 1) Member belum aktif
        $pendingVerifications = User::where('role', 'member')
            ->whereNull('email_verified_at')
            ->count();

        // 2) Total member
        $totalMembers = User::where('role', 'member')->count();

        // 3) Member baru 7 hari terakhir
        $newMembersThisWeek = User::where('role', 'member')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        // 4) Statistik paket yang diambil
        $paketStats = RegistrasiKursus::selectRaw('paket.nama_paket AS label, COUNT(*) AS total')
            ->join('kursus', 'registrasi_kursus.id_course', '=', 'kursus.id_course')
            ->join('paket', 'kursus.id_paket', '=', 'paket.id')
            ->groupBy('paket.nama_paket')
            ->orderByDesc('total')
            ->get();

        $paketLabels = $paketStats->pluck('label');
        $paketData   = $paketStats->pluck('total');

        // 5) Statistik bahasa yang diambil
        $bahasaStats = RegistrasiKursus::selectRaw('bahasa.nama_bahasa AS label, COUNT(*) AS total')
            ->join('kursus', 'registrasi_kursus.id_course', '=', 'kursus.id_course')
            ->join('bahasa', 'kursus.id_bahasa', '=', 'bahasa.id')
            ->groupBy('bahasa.nama_bahasa')
            ->orderByDesc('total')
            ->get();

        $bahasaLabels = $bahasaStats->pluck('label');
        $bahasaData   = $bahasaStats->pluck('total');

        // 6) Statistik kombinasi Bahasa + Paket (kursus)
        $courseStats = RegistrasiKursus::selectRaw("
                CONCAT(bahasa.nama_bahasa, ' - ', paket.nama_paket) AS label,
                COUNT(*) AS total
            ")
            ->join('kursus', 'registrasi_kursus.id_course', '=', 'kursus.id_course')
            ->join('bahasa', 'kursus.id_bahasa', '=', 'bahasa.id')
            ->join('paket', 'kursus.id_paket', '=', 'paket.id')
            ->groupBy('bahasa.nama_bahasa', 'paket.nama_paket')
            ->orderByDesc('total')
            ->get();

        $courseLabels = $courseStats->pluck('label');
        $courseData   = $courseStats->pluck('total');

        return response()->json([
            'pending_verifications' => $pendingVerifications,
            'total_members'         => $totalMembers,
            'new_members_this_week' => $newMembersThisWeek,

            'paket' => [
                'labels' => $paketLabels,
                'data'   => $paketData,
            ],
            'bahasa' => [
                'labels' => $bahasaLabels,
                'data'   => $bahasaData,
            ],
            'kursus' => [
                'labels' => $courseLabels,
                'data'   => $courseData,
            ],
        ]);
    }
}
