<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DijkstraService;
use App\Models\Penerima;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RouteController extends Controller
{
    private DijkstraService $dijkstraService;

    public function __construct(DijkstraService $dijkstraService)
    {
        $this->middleware('auth');
        $this->dijkstraService = $dijkstraService;
    }

    public function indexPage(): View
    {
        $allPenerimas = Penerima::orderBy('nama')->get(['id', 'nik', 'nama', 'lat', 'lng']);
        $kantorDesaCoords = [-0.06163903050132983, 109.3666303306687];
        
        $allGraphNodes = $this->dijkstraService->getAllNodes();

        return view('pages.rute.index', [
            'allPenerimas' => $allPenerimas,
            'kantorDesaCoords' => $kantorDesaCoords,
            'defaultMapCenter' => $kantorDesaCoords,
            'defaultZoomLevel' => 14,
            'allGraphNodes' => $allGraphNodes,
        ]);
    }

    public function calculateRoute(Request $request): JsonResponse
    {
        $request->validate([
            'start_lat' => 'required|numeric',
            'start_lng' => 'required|numeric',
            'destination_penerima_id' => 'required|exists:penerimas,id',
        ]);

        try {
            $startLat = (float) $request->input('start_lat');
            $startLng = (float) $request->input('start_lng');

            $penerimaTujuan = Penerima::findOrFail($request->input('destination_penerima_id'));
            $endLat = (float) $penerimaTujuan->lat;
            $endLng = (float) $penerimaTujuan->lng;

            // Panggil satu fungsi utama dari service
            $routeResult = $this->dijkstraService->getFinalRoute($startLat, $startLng, $endLat, $endLng);
            
            if ($routeResult === null) {
                return response()->json(['error' => 'Rute tidak dapat ditemukan atau terjadi kesalahan.'], 404);
            }

            // Rakit polyline akhir untuk peta
            $finalPath = $routeResult['path'];
            array_unshift($finalPath, [$startLat, $startLng]); // Tambah titik awal asli
            $finalPath[] = [$endLat, $endLng]; // Tambah titik akhir asli

            // Hitung jarak total
            $totalDistance = 0;
            for ($i = 0; $i < count($finalPath) - 1; $i++) {
                $totalDistance += $this->dijkstraService->haversineDistance($finalPath[$i][0], $finalPath[$i][1], $finalPath[$i+1][0], $finalPath[$i+1][1]);
            }

            return response()->json([
                'path' => $finalPath,
                'distance' => round($totalDistance, 2),
                'nodes' => $routeResult['nodes'],
                'start_address_display' => "Kantor Desa Sungai Raya",
                'destination_address_display' => $penerimaTujuan->alamat ?: $penerimaTujuan->nama,
            ]);

        } catch (\Exception $e) {
            Log::error('Route Calculation Error: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile());
            return response()->json(['error' => 'Terjadi kesalahan internal saat menghitung rute.'], 500);
        }
    }
}