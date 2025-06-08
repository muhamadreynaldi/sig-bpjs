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
        $query = Penerima::query();
        $searchTerm = $request->input('search_nama_nik');

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $parts = explode(' - ', $searchTerm, 2);
                if (count($parts) === 2) {
                    $q->where('nik', 'like', "%{$parts[0]}%")
                      ->Where('nama', 'like', "%{$parts[1]}%");
                } else {
                    $q->where('nama', 'like', "%{$searchTerm}%")
                      ->orWhere('nik', 'like', "%{$searchTerm}%");
                }
            });
        }

        if ($request->filled('dusun')) {
            $query->where('dusun', $request->input('dusun'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $penerimas = $query->get(['id', 'nik', 'nama', 'status', 'lat', 'lng', 'dusun', 'alamat']);

        $dusunList = Penerima::distinct()->pluck('dusun')->filter()->sort();
        $statusList = Penerima::distinct()->pluck('status')->filter()->sort();
        $defaultLocation = [-0.06961, 109.36765];
        $zoomLevel = $penerimas->count() > 1 ? 15 : 17;

        if ($penerimas->count() === 1 && $penerimas->first()->lat && $penerimas->first()->lng) {
            $defaultLocation = [(float)$penerimas->first()->lat, (float)$penerimas->first()->lng];
            $initialZoomController = 17;
        } elseif ($penerimas->isEmpty() && $searchTerm) {
        } elseif ($penerimas->count() > 1) {
        }

        $searchOptionsListPemetaan = Penerima::orderBy('nama')->get(['id', 'nik', 'nama']);

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