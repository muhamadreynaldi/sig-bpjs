<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DijkstraService;
use App\Models\Penerima;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class RouteController extends Controller
{
    private DijkstraService $dijkstraService;

    public function __construct(DijkstraService $dijkstraService)
    {
        $this->middleware('auth');
        $this->dijkstraService = $dijkstraService;
    }

    public function indexPage(Request $request): View
    {
        $allPenerimas = Penerima::orderBy('nama')->get(['id', 'nik', 'nama', 'lat', 'lng']);
        // Ganti dengan koordinat aktual Kantor Desa Sungai Raya Anda
        $kantorDesaCoords = [-0.06173637665163168, 109.36675978082265]; // PASTIKAN INI SUDAH BENAR
        
        return view('pages.rute.index', [
            'allPenerimas' => $allPenerimas,
            'kantorDesaCoords' => $kantorDesaCoords,
            'defaultMapCenter' => $kantorDesaCoords,
            'defaultZoomLevel' => 14
        ]);
    }

    public function calculateRoute(Request $request): JsonResponse
    {
        $request->validate([
            'start_lat' => 'required|numeric',
            'start_lng' => 'required|numeric',
            'destination_penerima_id' => 'required|exists:penerimas,id',
        ]);

        $startLat = (float) $request->input('start_lat');
        $startLng = (float) $request->input('start_lng');

        $penerimaTujuan = Penerima::findOrFail($request->input('destination_penerima_id'));
        $endLat = (float) $penerimaTujuan->lat;
        $endLng = (float) $penerimaTujuan->lng;

        // --- TAMBAHAN: Informasi Alamat ---
        // Anda bisa membuat ini lebih dinamis jika perlu, tapi untuk Kantor Desa Sungai Raya, ini statis.
        // Pastikan alamat ini sesuai dengan yang ada di dokumen skripsi atau data aktual.
        // Saya akan menggunakan placeholder, silakan disesuaikan.
        $alamatKantorDesa = "Kantor Desa Sungai Raya, Kabupaten Kubu Raya"; // GANTI DENGAN ALAMAT LENGKAP & BENAR
        $alamatTujuan = $penerimaTujuan->alamat ?: ($penerimaTujuan->dusun ? 'Dusun ' . $penerimaTujuan->dusun : $penerimaTujuan->nama);
        // ------------------------------------

        $startNodeId = $this->dijkstraService->findNearestNode($startLat, $startLng);
        $endNodeId = $this->dijkstraService->findNearestNode($endLat, $endLng);

        if (!$startNodeId || !$endNodeId) {
            return response()->json([
                'error' => 'Tidak dapat menemukan node jalan terdekat untuk titik awal atau tujuan.',
                'start_address_display' => $alamatKantorDesa, // Kirim juga jika ada error
                'destination_address_display' => $alamatTujuan
            ], 400);
        }

        if ($startNodeId === $endNodeId) {
            $directPathCoords = [ [$startLat, $startLng], [$endLat, $endLng] ];
            $directDistance = $this->dijkstraService->haversineDistance($startLat, $startLng, $endLat, $endLng);
            return response()->json([
                'path' => $directPathCoords,
                'distance' => round($directDistance, 2),
                'start_address_display' => $alamatKantorDesa, // TAMBAHKAN
                'destination_address_display' => $alamatTujuan // TAMBAHKAN
            ]);
        }

        $dijkstraResult = $this->dijkstraService->calculateDijkstraPath($startNodeId, $endNodeId);

        if (!$dijkstraResult->route_available) {
            return response()->json([
                'error' => $dijkstraResult->message ?? 'Rute tidak ditemukan.',
                'start_address_display' => $alamatKantorDesa, // Kirim juga jika ada error
                'destination_address_display' => $alamatTujuan
            ], 404);
        }

        $graphPolyline = $dijkstraResult->polyline;
        $distanceOnGraph = (float) $dijkstraResult->total_distance;

        $finalPolyline = [];
        $totalTravelDistance = 0;

        $finalPolyline[] = [$startLat, $startLng];
        $startSnappedNodeCoords = $this->dijkstraService->getNodeCoordinatesById($startNodeId);

        if ($startSnappedNodeCoords) {
            $totalTravelDistance += $this->dijkstraService->haversineDistance(
                $startLat, $startLng,
                $startSnappedNodeCoords['lat'], $startSnappedNodeCoords['lng']
            );
        }

        if (!empty($graphPolyline)) {
            $finalPolyline = array_merge($finalPolyline, $graphPolyline);
        }

        $totalTravelDistance += $distanceOnGraph;
        $endSnappedNodeCoords = $this->dijkstraService->getNodeCoordinatesById($endNodeId);

        if ($endSnappedNodeCoords) {
            $totalTravelDistance += $this->dijkstraService->haversineDistance(
                $endSnappedNodeCoords['lat'], $endSnappedNodeCoords['lng'],
                $endLat, $endLng
            );
        }
        $finalPolyline[] = [$endLat, $endLng];

        $uniqueFinalPolyline = [];
        if (!empty($finalPolyline)) {
            $uniqueFinalPolyline[] = $finalPolyline[0];
            for ($i = 1; $i < count($finalPolyline); $i++) {
                if (abs($finalPolyline[$i][0] - $finalPolyline[$i-1][0]) > 1e-7 || abs($finalPolyline[$i][1] - $finalPolyline[$i-1][1]) > 1e-7) {
                    $uniqueFinalPolyline[] = $finalPolyline[$i];
                }
            }
        }

        return response()->json([
            'path' => $uniqueFinalPolyline,
            'distance' => round($totalTravelDistance, 2),
            'start_address_display' => $alamatKantorDesa, // TAMBAHKAN
            'destination_address_display' => $alamatTujuan, // TAMBAHKAN
            // Jika nanti ada rute alternatif, tambahkan di sini juga
            // 'alternative_route' => [...]
        ]);
    }
}