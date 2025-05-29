<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DijkstraService;
use App\Models\Penerima;
use Illuminate\View\View; // Tambahkan ini
use Illuminate\Http\JsonResponse; // Tambahkan ini

class RouteController extends Controller
{
    private DijkstraService $dijkstraService;

    public function __construct(DijkstraService $dijkstraService)
    {
        $this->middleware('auth');
        $this->dijkstraService = $dijkstraService;
    }

    /**
     * Display the route planning page.
     */
    public function indexPage(Request $request): View // Metode baru untuk menampilkan halaman rute
    {
        // Ambil semua penerima untuk dropdown tujuan
        $allPenerimas = Penerima::orderBy('nama')->get(['id', 'nik', 'nama', 'lat', 'lng']);

        // Koordinat default Kantor Desa Sungai Raya (HARUS DISESUAIKAN)
        $defaultLocation = [-0.06961, 109.36765]; 
        $kantorDesaCoords = [-0.06173637665163168, 109.36675978082265]; // SESUAIKAN INI
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

        // 1. Snap titik aktual ke node terdekat di graf
        $startNodeId = $this->dijkstraService->findNearestNode($startLat, $startLng);
        $endNodeId = $this->dijkstraService->findNearestNode($endLat, $endLng);

        if (!$startNodeId || !$endNodeId) {
            return response()->json(['error' => 'Tidak dapat menemukan node jalan terdekat untuk titik awal atau tujuan.'], 400);
        }

        // Jika node snap sama, langsung hitung jarak lurus dan buat polyline sederhana
        if ($startNodeId === $endNodeId) {
            $directPathCoords = [ [$startLat, $startLng], [$endLat, $endLng] ];
            $directDistance = $this->dijkstraService->haversineDistance($startLat, $startLng, $endLat, $endLng);
            return response()->json([
                'path' => $directPathCoords,
                'distance' => round($directDistance, 2) // Jarak dalam KM
            ]);
        }

        // 2. Jalankan Algoritma Dijkstra menggunakan service yang sudah diupdate
        $dijkstraResult = $this->dijkstraService->calculateDijkstraPath($startNodeId, $endNodeId);

        if (!$dijkstraResult->route_available) {
            return response()->json(['error' => $dijkstraResult->message ?? 'Rute tidak ditemukan.'], 404);
        }

        // 3. Siapkan Polyline Lengkap dan Total Jarak Perjalanan
        // Polyline dari Dijkstra adalah antar node graf. Kita perlu menyambungkannya dengan titik aktual.
        $graphPolyline = $dijkstraResult->polyline; // Ini sudah berupa array koordinat [lat,lng]
        $distanceOnGraph = (float) $dijkstraResult->total_distance; // Jarak murni pada graf (dalam KM)

        $finalPolyline = [];
        $totalTravelDistance = 0;

        // Tambahkan titik awal aktual ke polyline
        $finalPolyline[] = [$startLat, $startLng];

        // Koordinat node snap awal
        $startSnappedNodeCoords = $this->dijkstraService->getNodeCoordinatesById($startNodeId);

        // Tambah jarak dari titik awal aktual ke node snap awal
        if ($startSnappedNodeCoords) {
            $totalTravelDistance += $this->dijkstraService->haversineDistance(
                $startLat, $startLng,
                $startSnappedNodeCoords['lat'], $startSnappedNodeCoords['lng']
            );
            // Jika node snap awal berbeda dari titik pertama di graphPolyline (seharusnya sama), tambahkan
            // Namun, $graphPolyline sudah berisi node snap awal.
        }

        // Gabungkan dengan polyline dari graf
        if (!empty($graphPolyline)) {
            // Jika titik awal aktual berbeda signifikan dari titik pertama di graphPolyline,
            // (yang merupakan node snap awal), garis dari titik aktual ke node snap awal sudah diwakili
            // oleh jarak yang dihitung di atas. Untuk polyline visual, kita pastikan nyambung.
            if ($startSnappedNodeCoords && (abs($startLat - $graphPolyline[0][0]) > 1e-6 || abs($startLng - $graphPolyline[0][1]) > 1e-6)) {
                 // Jika titik snap berbeda dari awal polyline dijkstra (seharusnya tidak jika logic benar)
                 // atau jika polyline tidak diawali titik snap
            }
             $finalPolyline = array_merge($finalPolyline, $graphPolyline);
        }


        // Tambah jarak pada graf
        $totalTravelDistance += $distanceOnGraph;

        // Koordinat node snap akhir
        $endSnappedNodeCoords = $this->dijkstraService->getNodeCoordinatesById($endNodeId);

        // Tambah jarak dari node snap akhir ke titik tujuan aktual
        if ($endSnappedNodeCoords) {
            $totalTravelDistance += $this->dijkstraService->haversineDistance(
                $endSnappedNodeCoords['lat'], $endSnappedNodeCoords['lng'],
                $endLat, $endLng
            );

            // Tambahkan titik akhir aktual ke polyline jika berbeda dari titik terakhir di graphPolyline
            if (!empty($graphPolyline)) {
                $lastPointInGraphPolyline = end($graphPolyline);
                 if (abs($endLat - $lastPointInGraphPolyline[0]) > 1e-6 || abs($endLng - $lastPointInGraphPolyline[1]) > 1e-6) {
                    // Tidak perlu menambahkan lagi karena $finalPolyline sudah berisi $graphPolyline
                }
            }
        }
         $finalPolyline[] = [$endLat, $endLng];

        // Hapus duplikat titik berurutan yang mungkin muncul jika titik aktual sama dengan node snap
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
            'distance' => round($totalTravelDistance, 2) // Total jarak perjalanan dalam KM
        ]);
    }
}