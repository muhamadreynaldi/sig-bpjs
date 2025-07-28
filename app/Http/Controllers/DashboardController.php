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
        $search = $request->input('search');
        $penerimasFound = collect();

        if ($search) {
            $penerimasFound = Penerima::query()
                ->where(function ($query) use ($search) {
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

        $totalPenerima = Penerima::count();
        $totalAktif = Penerima::where('status', 'Aktif')->count();
        $totalNonaktif = Penerima::where('status', 'Non-Aktif')->count();
        $totalMeninggal = Penerima::where('status', 'non jkn')->count();
        $jumlahDusun = Penerima::distinct()->count('dusun');

        $user = Auth::user();

        $searchOptionsList = Penerima::orderBy('nama')->get(['id', 'nik', 'nama']);

        return view('pages.dashboard', compact(
            'search',
            'penerimasFound',
            'user',
            'totalPenerima',
            'totalAktif',
            'totalNonaktif',
            'totalMeninggal',
            'jumlahDusun',
            'searchOptionsList'
        ));
    }
}