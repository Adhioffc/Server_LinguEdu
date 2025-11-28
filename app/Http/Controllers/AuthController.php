<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Paket;
use App\Models\Bahasa;
use App\Models\Kursus;
use App\Models\RegistrasiKursus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function paket()
    {
        $paket = Paket::all();

        return response()->json([
            'data' => $paket
        ]);
    }
    public function bahasa(Request $request)
    {
        // id_paket dikirim via query string: /bahasa?id_paket=1
        $bahasa = Bahasa::all();

        return response()->json([
            'data' => $bahasa
        ]);
    }
    public function registrasiKursus(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'id_course' => 'required|exists:kursus,id_course',
            'metode_bayar' => 'required|string',
            'bukti_byr' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // 1. Buat user baru dengan role MEMBER
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'member',                // <-- ini kunci
            ]);

            // 2. Ambil kursus + paket untuk dapat harga
            $course = Kursus::with('paket')->findOrFail($request->id_course);

            if (!$course->paket) {
                throw new \Exception('Kursus belum terhubung dengan paket (id_paket null).');
            }

            $totalBayar = $course->paket->harga;

            // 3. Ambil admin dari tabel users
            // opsi 1: berdasarkan role
            $admin = User::where('role', 'admin')->first();

            // atau kalau mau spesifik email:
            // $admin = User::where('email', 'Admin@gmail.com')->first();

            // 4. Upload bukti bayar (opsional)
            $pathBukti = null;
            if ($request->hasFile('bukti_byr')) {
                $pathBukti = $request->file('bukti_byr')
                    ->store('bukti_pembayaran', 'public');
            }

            // 5. Insert ke tabel registrasi_kursus
            $registrasi = RegistrasiKursus::create([
                'id_admin' => $admin?->id,           // bisa null kalau belum ada admin
                'id_member' => $user->id,
                'id_course' => $course->id_course,
                'tgl_trans' => now(),
                'metode_bayar' => $request->metode_bayar,
                'total_byr' => $totalBayar,
                'bukti_byr' => $pathBukti,
                'progress' => 0,
                'level' => $course->paket->nama_paket,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Registrasi berhasil',
                'user' => $user,
                'kursus' => $course->load('bahasa', 'paket'),
                'registrasi' => $registrasi,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'member',       // << Tambah ini
        ]);

        return response()->json([
            'message' => 'Register success',
            'user' => $user
        ], 201);
    }

    public function kursus(Request $request)
    {
        $request->validate([
            'id_paket' => 'required|exists:paket,id', // sesuaikan kalau PK paket beda
        ]);

        $idPaket = $request->query('id_paket');

        $kursus = Kursus::with(['bahasa', 'paket'])
            ->where('id_paket', $idPaket)
            ->get();

        return response()->json([
            'data' => $kursus
        ]);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // cek email atau password salah
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // generate token
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout success']);
    }
}
