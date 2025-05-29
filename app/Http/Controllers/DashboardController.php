<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penerima;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $search = $request->input('search'); // Ini akan berisi "NIK - Nama" atau teks ketikan
        $penerimasFound = collect();

        if ($search) {
            // Logika pencarian backend tetap bisa menggunakan LIKE
            // karena value dari Select2 (jika dipilih) adalah string "NIK - Nama"
            // atau teks yang diketik pengguna jika tags:true digunakan (tapi kita akan hilangkan tags:true)
            $penerimasFound = Penerima::query()
                ->where(function ($query) use ($search) {
                    // Coba cari berdasarkan NIK atau Nama secara terpisah jika formatnya "NIK - Nama"
                    // Atau cari berdasarkan NIK saja, atau Nama saja jika format value dari select lebih spesifik
                    // Untuk saat ini, kita asumsikan $search adalah string yang bisa cocok dengan NIK atau Nama
                    $parts = explode(' - ', $search, 2);
                    if (count($parts) === 2) {
                        $query->where('nik', 'like', "%{$parts[0]}%")
                              ->orWhere('nama', 'like', "%{$parts[1]}%");
                    } else {
                        $query->where('nama', 'like', "%{$search}%")
                              ->orWhere('nik', 'like', "%{$search}%");
                    }
                })
                ->limit(10)
                ->get(['id', 'nik', 'nama', 'dusun', 'status']);
        }

        // Statistik untuk Summary Cards (tetap sama)
        $totalPenerima = Penerima::count();
        $totalAktif = Penerima::where('status', 'Aktif')->count();
        $totalNonaktif = Penerima::where('status', 'Nonaktif')->count();
        $totalMeninggal = Penerima::where('status', 'Meninggal')->count();
        $jumlahDusun = Penerima::distinct()->count('dusun');

        $user = Auth::user();

        // Data untuk mengisi Select2 pencarian
        $searchOptionsList = Penerima::orderBy('nama')->get(['id', 'nik', 'nama']);

        return view('pages.dashboard', compact(
            'search', // nilai search saat ini
            'penerimasFound',
            'user',
            'totalPenerima',
            'totalAktif',
            'totalNonaktif',
            'totalMeninggal',
            'jumlahDusun',
            'searchOptionsList' // Kirim data ini ke view
        ));
    }
}