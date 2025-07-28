<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penerima;
use Illuminate\View\View;

class PemetaanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
{
    // Ambil SEMUA opsi untuk dropdown filter dusun dan status
    // INI BAGIAN YANG DIPERBAIKI DARI ERROR SEBELUMNYA
    $dusunList = Penerima::distinct()->pluck('dusun')->filter()->sort();
    $statusList = Penerima::distinct()->pluck('status')->filter()->sort();

    // Buat query dasar yang akan kita gunakan untuk memfilter data peta dan daftar nama
    $baseQuery = Penerima::query();

    if ($request->filled('dusun')) {
        $baseQuery->where('dusun', $request->input('dusun'));
    }
    if ($request->filled('status')) {
        $baseQuery->where('status', $request->input('status'));
    }

    // Ambil daftar nama/NIK dari query yang SUDAH DIFILTER berdasarkan dusun/status
    $searchOptionsListPemetaan = $baseQuery->clone()->orderBy('nama')->get(['id', 'nik', 'nama']);

    // Terapkan filter NAMA/NIK jika ada, untuk data di PETA
    $petaQuery = $baseQuery->clone();
    $searchTerm = $request->input('search_nama_nik');
    if ($searchTerm) {
        $petaQuery->where(function ($q) use ($searchTerm) {
            $parts = explode(' - ', $searchTerm, 2);
            if (count($parts) === 2) {
                $q->where('nik', $parts[0])->where('nama', $parts[1]);
            }
        });
    }

    $penerimas = $petaQuery->get(['id', 'nik', 'nama', 'status', 'lat', 'lng', 'dusun', 'alamat']);

    // Logika untuk menentukan pusat peta dan zoom
    $defaultLocation = [-0.06961, 109.36765];
    $zoomLevel = 15;
    if ($penerimas->count() === 1 && $penerimas->first()->lat && $penerimas->first()->lng) {
        $defaultLocation = [(float)$penerimas->first()->lat, (float)$penerimas->first()->lng];
        $zoomLevel = 17;
    }

    return view('pages.pemetaan.index', [
        'penerimas' => $penerimas,
        'dusunList' => $dusunList,
        'statusList' => $statusList,
        'defaultLocation' => $defaultLocation,
        'zoomLevel' => $zoomLevel,
        'input' => $request->all(),
        'searchOptionsList' => $searchOptionsListPemetaan
    ]);
}
}