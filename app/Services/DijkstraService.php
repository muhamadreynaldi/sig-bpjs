<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;

class DijkstraService
{
    private array $graph_array_data = [];
    private array $node_coordinates_map = [];
    private array $edge_polylines_map = [];

        public function __construct(string $graphDataPhpFilePath = 'gis/graph_data.php')
    {
        $path = storage_path('app/' . $graphDataPhpFilePath);
        if (!file_exists($path)) {
            throw new \Exception("File data graf PHP tidak ditemukan: {$path}.");
        }
        $data = require $path;
        $this->graph_array_data = $data['graph_array'] ?? [];
        $this->node_coordinates_map = $data['node_coordinates'] ?? [];
        $this->edge_polylines_map = $data['edge_polylines'] ?? [];
    }
    
    /**
     * FUNGSI PUBLIK UTAMA
     * Menerima koordinat asli dan mengembalikan hasil rute yang sudah jadi.
     */
    public function getFinalRoute(float $startLat, float $startLng, float $endLat, float $endLng): ?array
    {
        // 1. "Snap" titik awal dan akhir ke ruas jalan terdekat
        $startSnap = $this->snapPointToRoad($startLat, $startLng);
        $endSnap = $this->snapPointToRoad($endLat, $endLng);

        if (!$startSnap || !$endSnap) {
            Log::error("Gagal melakukan snap ke jalan untuk titik awal atau akhir.");
            return null;
        }
        
        // Handle kasus di mana titik awal dan akhir berada di ruas jalan yang sama
        if ($startSnap['edge_key'] === $endSnap['edge_key']) {
            $path = [$startSnap['snap_coords'], $endSnap['snap_coords']];
            $distance = $this->haversineDistance($startSnap['snap_coords'][0], $startSnap['snap_coords'][1], $endSnap['snap_coords'][0], $endSnap['snap_coords'][1]);
            return [
                'path' => $path,
                'distance' => $distance,
                'nodes' => []
            ];
        }

        // 2. Tentukan rute Dijkstra terbaik antar 4 kemungkinan node (u1->u2, u1->v2, v1->u2, v1->v2)
        $bestRoute = null;
        $minTotalDistance = PHP_FLOAT_MAX;

        $startNodes = [$startSnap['u_node'], $startSnap['v_node']];
        $endNodes = [$endSnap['u_node'], $endSnap['v_node']];
        
        foreach ($startNodes as $startNode) {
            foreach ($endNodes as $endNode) {
                $dijkstraResult = $this->calculateDijkstraPath($startNode, $endNode);
                if (!$dijkstraResult->route_available) continue;

                $costToStartNode = ($startNode === $startSnap['u_node']) ? $startSnap['cost_to_u'] : $startSnap['cost_to_v'];
                $costFromEndNode = ($endNode === $endSnap['u_node']) ? $endSnap['cost_to_u'] : $endSnap['cost_to_v'];
                
                $currentTotalDistance = $costToStartNode + $dijkstraResult->total_distance_on_graph + $costFromEndNode;

                if ($currentTotalDistance < $minTotalDistance) {
                    $minTotalDistance = $currentTotalDistance;
                    $bestRoute = [
                        'dijkstra_result' => $dijkstraResult,
                        'start_node_used' => $startNode,
                        'end_node_used' => $endNode,
                        'start_snap_info' => $startSnap,
                        'end_snap_info' => $endSnap,
                    ];
                }
            }
        }
        
        if ($bestRoute === null) {
            Log::error("Tidak ditemukan rute Dijkstra yang valid antara node snap.");
            return null;
        }

        // 3. Rakit polyline final dari rute terbaik yang ditemukan
        return $this->assembleFinalPolyline($bestRoute);
    }
    
    /**
     * HELPER: Merakit semua potongan polyline menjadi satu rute utuh.
     */
    private function assembleFinalPolyline(array $bestRoute): array
    {
        $dijkstraPolyline = $bestRoute['dijkstra_result']->polyline_on_graph;
        
        // Tentukan polyline parsial untuk titik awal dan akhir
        $startPartialPolyline = ($bestRoute['start_node_used'] === $bestRoute['start_snap_info']['u_node'])
            ? $bestRoute['start_snap_info']['polyline_to_u']
            : $bestRoute['start_snap_info']['polyline_to_v'];
            
        $endPartialPolyline = ($bestRoute['end_node_used'] === $bestRoute['end_snap_info']['u_node'])
            ? $bestRoute['end_snap_info']['polyline_to_u']
            : $bestRoute['end_snap_info']['polyline_to_v'];

        // Balik polyline awal agar arahnya dari node -> titik snap
        $startPartialPolyline = array_reverse($startPartialPolyline);

        // Gabungkan semua bagian
        // Hapus titik sambung pertama dari rute utama untuk menghindari duplikasi
        if (!empty($dijkstraPolyline) && $dijkstraPolyline[0] === $startPartialPolyline[count($startPartialPolyline)-1]) {
            array_shift($dijkstraPolyline);
        }
        // Hapus titik sambung pertama dari segmen akhir
        if (!empty($endPartialPolyline) && $endPartialPolyline[0] === $dijkstraPolyline[count($dijkstraPolyline)-1]) {
            array_shift($endPartialPolyline);
        }

        $finalGraphPolyline = array_merge($startPartialPolyline, $dijkstraPolyline, $endPartialPolyline);
        
        // Siapkan data node untuk marker
        $nodes_for_marker = [];
        foreach ($bestRoute['dijkstra_result']->path_nodes as $nodeId) {
            $coords = $this->getNodeCoordinatesById($nodeId);
            if ($coords) {
                $nodes_for_marker[] = ['id' => $nodeId, 'coords' => $coords];
            }
        }

        return [
            'path' => $finalGraphPolyline,
            'distance' => $bestRoute['dijkstra_result']->total_distance_on_graph, // Ini bisa disempurnakan lagi
            'nodes' => $nodes_for_marker,
        ];
    }
    
    /**
     * HELPER: "Snap to Road" yang paling canggih.
     * Mencari ruas jalan terdekat dan mengembalikan informasi lengkap tentangnya.
     */
    private function snapPointToRoad(float $targetLat, float $targetLng): ?array
    {
        $minDistance = PHP_FLOAT_MAX;
        $snapResult = null;

        foreach ($this->graph_array_data as $edge) {
            $u_node_id = $edge[0];
            $v_node_id = $edge[1];
            $edgeKey = "$u_node_id|$v_node_id";

            if (!isset($this->edge_polylines_map[$edgeKey])) continue;
            $polyline = $this->edge_polylines_map[$edgeKey];

            for ($i = 0; $i < count($polyline) - 1; $i++) {
                $p1 = $polyline[$i];
                $p2 = $polyline[$i + 1];
                
                $distanceInfo = $this->getPerpendicularDistance($targetLat, $targetLng, $p1[0], $p1[1], $p2[0], $p2[1]);

                if ($distanceInfo['distance'] < $minDistance) {
                    $minDistance = $distanceInfo['distance'];
                    
                    // Potong polyline dari u -> snap_point dan v -> snap_point
                    $poly_to_snap = array_slice($polyline, 0, $i + 1);
                    $poly_to_snap[] = $distanceInfo['point'];
                    
                    $cost_to_u = 0;
                    for ($j=0; $j<count($poly_to_snap)-1; $j++) {
                        $cost_to_u += $this->haversineDistance($poly_to_snap[$j][0], $poly_to_snap[$j][1], $poly_to_snap[$j+1][0], $poly_to_snap[$j+1][1]);
                    }

                    $poly_from_snap = array_slice($polyline, $i + 1);
                    array_unshift($poly_from_snap, $distanceInfo['point']);
                    
                    $cost_to_v = 0;
                     for ($j=0; $j<count($poly_from_snap)-1; $j++) {
                        $cost_to_v += $this->haversineDistance($poly_from_snap[$j][0], $poly_from_snap[$j][1], $poly_from_snap[$j+1][0], $poly_from_snap[$j+1][1]);
                    }

                    $snapResult = [
                        'edge_key' => $edgeKey,
                        'u_node' => $u_node_id,
                        'v_node' => $v_node_id,
                        'snap_coords' => $distanceInfo['point'],
                        'polyline_to_u' => $poly_to_snap,
                        'polyline_to_v' => array_reverse($poly_from_snap),
                        'cost_to_u' => $cost_to_u,
                        'cost_to_v' => $cost_to_v
                    ];
                }
            }
        }
        return $snapResult;
    }
    public function parseCoordinatesString(string $coordString): ?array
    {
        if (preg_match('/^\(\s*([-+]?\d*\.?\d+)\s*,\s*([-+]?\d*\.?\d+)\s*\)$/', $coordString, $matches)) {
            return ['lng' => (float)$matches[1], 'lat' => (float)$matches[2]];
        }
        return null;
    }

    public function findNearestRoadNode(float $targetLat, float $targetLng): ?string
    {
        $minDistance = PHP_FLOAT_MAX;
        $bestNode = null;

        // Iterasi melalui setiap ruas jalan (edge) di graf
        foreach ($this->graph_array_data as $edge) {
            $edgeKey = "{$edge[0]}|{$edge[1]}";
            if (!isset($this->edge_polylines_map[$edgeKey])) continue;

            $polyline = $this->edge_polylines_map[$edgeKey];

            // Iterasi melalui setiap segmen kecil di dalam polyline jalan
            for ($i = 0; $i < count($polyline) - 1; $i++) {
                $p1 = $polyline[$i]; // [lat, lng]
                $p2 = $polyline[$i+1]; // [lat, lng]
                
                $distanceInfo = $this->getPerpendicularDistance(
                    $targetLat, $targetLng,
                    $p1[0], $p1[1],
                    $p2[0], $p2[1]
                );

                if ($distanceInfo['distance'] < $minDistance) {
                    $minDistance = $distanceInfo['distance'];
                    
                    // Tentukan node ujung mana yang paling dekat dengan titik snap di garis
                    $distToP1 = $this->haversineDistance($distanceInfo['point'][0], $distanceInfo['point'][1], $p1[0], $p1[1]);
                    $distToP2 = $this->haversineDistance($distanceInfo['point'][0], $distanceInfo['point'][1], $p2[0], $p2[1]);

                    // Pilih node ujung dari segmen jalan yang terdekat dengan titik snap
                    $bestNode = ($distToP1 < $distToP2) ? $edge[0] : $edge[1];
                }
            }
        }
        return $bestNode;
    }

    /**
     * Helper Function: Menghitung jarak tegak lurus dari sebuah titik ke segmen garis.
     * Ini adalah inti dari logika "snap-to-road".
     */
    private function getPerpendicularDistance(float $pLat, float $pLng, float $l1Lat, float $l1Lng, float $l2Lat, float $l2Lng): array
    {
        $earthRadius = 6371;
        $px = deg2rad($pLng) * $earthRadius * cos(deg2rad($pLat));
        $py = deg2rad($pLat) * $earthRadius;
        $l1x = deg2rad($l1Lng) * $earthRadius * cos(deg2rad($l1Lat));
        $l1y = deg2rad($l1Lat) * $earthRadius;
        $l2x = deg2rad($l2Lng) * $earthRadius * cos(deg2rad($l2Lat));
        $l2y = deg2rad($l2Lat) * $earthRadius;

        $dx = $l2x - $l1x;
        $dy = $l2y - $l1y;

        if ($dx == 0 && $dy == 0) {
            $closestX = $l1x;
            $closestY = $l1y;
        } else {
            $t = (($px - $l1x) * $dx + ($py - $l1y) * $dy) / (max(0.00000001, $dx * $dx + $dy * $dy));
            if ($t < 0) {
                $closestX = $l1x; $closestY = $l1y;
            } elseif ($t > 1) {
                $closestX = $l2x; $closestY = $l2y;
            } else {
                $closestX = $l1x + $t * $dx;
                $closestY = $l1y + $t * $dy;
            }
        }
        
        $closestLat = rad2deg($closestY / $earthRadius);
        $closestLng = rad2deg($closestX / ($earthRadius * cos(deg2rad($closestLat))));
        
        $distance = $this->haversineDistance($pLat, $pLng, $closestLat, $closestLng);

        return [
            'distance' => $distance,
            'point' => [$closestLat, $closestLng]
        ];
    }

    public function getNodeCoordinatesById(string $nodeId): ?array
    {
        if (isset($this->node_coordinates_map[$nodeId])) {
            $parsedCoords = $this->parseCoordinatesString($this->node_coordinates_map[$nodeId]);
            return $parsedCoords ? [$parsedCoords['lat'], $parsedCoords['lng']] : null;
        }
        return null;
    }

    public function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
    
    public function calculateDijkstraPath(string $sourceNodeId, string $targetNodeId): object
    {
        $neighbours = [];
        $allNodeIds = array_keys($this->node_coordinates_map);

        foreach ($this->graph_array_data as $edge) {
            [$start, $end, $cost] = $edge;
            $neighbours[(string)$start][] = ["end" => (string)$end, "cost" => (float)$cost];
            $neighbours[(string)$end][] = ["end" => (string)$start, "cost" => (float)$cost];
        }

        $dist = array_fill_keys($allNodeIds, INF);
        $previous = array_fill_keys($allNodeIds, null);
        $dist[$sourceNodeId] = 0;

        $queue = new \SplPriorityQueue();
        $queue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
        $queue->insert($sourceNodeId, 0);

        while (!$queue->isEmpty()) {
            $u = $queue->extract();
            if ($u === $targetNodeId) break;
            if (!isset($neighbours[$u])) continue;

            foreach ($neighbours[$u] as $neighbor) {
                $v = $neighbor["end"];
                $alt = $dist[$u] + $neighbor["cost"];
                if ($alt < $dist[$v]) {
                    $dist[$v] = $alt;
                    $previous[$v] = $u;
                    $queue->insert($v, -$alt);
                }
            }
        }

        if ($dist[$targetNodeId] === INF) {
            return (object)["route_available" => false, "message" => "Rute ke tujuan tidak dapat ditemukan."];
        }

        $pathNodeIds = [];
        $curr = $targetNodeId;
        while ($curr !== null) {
            array_unshift($pathNodeIds, $curr);
            $curr = $previous[$curr];
        }

        if (empty($pathNodeIds) || $pathNodeIds[0] !== $sourceNodeId) {
            return (object)["route_available" => false, "message" => "Gagal merekonstruksi rute."];
        }
        
        // --- LOGIKA PERAKITAN POLYLINE YANG DISEMPURNAKAN ---
        
        $fullPolyline = [];
        // Jika path hanya 1 node (awal == tujuan), kembalikan koordinat node itu saja
        if (count($pathNodeIds) === 1) {
             $fullPolyline[] = $this->getNodeCoordinatesById($pathNodeIds[0]);
        } else {
            // Iterasi melalui setiap segmen (edge) dari path yang ditemukan
            for ($i = 0; $i < count($pathNodeIds) - 1; $i++) {
                $startNodeId = $pathNodeIds[$i];
                $endNodeId = $pathNodeIds[$i + 1];
                $edgeKey = "{$startNodeId}|{$endNodeId}";
    
                if (isset($this->edge_polylines_map[$edgeKey])) {
                    $segmentPolyline = $this->edge_polylines_map[$edgeKey];
                    
                    // Jika ini BUKAN segmen pertama, hapus titik pertama dari segmen ini
                    // untuk menghindari duplikasi titik sambungan.
                    if ($i > 0) {
                        array_shift($segmentPolyline);
                    }
                    
                    $fullPolyline = array_merge($fullPolyline, $segmentPolyline);
                } else {
                    // Fallback jika polyline edge tidak ditemukan (seharusnya jarang terjadi)
                    // Gambar garis lurus antar node sebagai cadangan
                    $fallbackCoords = $this->getNodeCoordinatesById($endNodeId);
                    if ($fallbackCoords) {
                       $fullPolyline[] = $fallbackCoords;
                    }
                }
            }
        }

        return (object)[
            "route_available" => true,
            "polyline_on_graph" => $fullPolyline, // Polyline detail yang HANYA ada di graf
            "path_nodes" => $pathNodeIds,          // Daftar ID node yang dilalui
            "total_distance_on_graph" => $dist[$targetNodeId] // Jarak total di dalam graf
        ];
    }

    public function getAllNodes(): array
    {
        $allNodes = [];
        if (empty($this->node_coordinates_map)) {
            return [];
        }

        foreach ($this->node_coordinates_map as $nodeId => $coordString) {
            $coords = $this->parseCoordinatesString($coordString);
            if ($coords) {
                // Format yang dibutuhkan oleh Leaflet: [latitude, longitude]
                $allNodes[] = [
                    'id' => (string)$nodeId,
                    'coords' => [$coords['lat'], $coords['lng']]
                ];
            }
        }
        return $allNodes;
    }

public function trimPolylineToExactPoint(array $polyline, float $targetLat, float $targetLng): array
{
    if (count($polyline) < 2) {
        return $polyline;
    }

    // --- LOGIKA BARU YANG LEBIH CERDAS ---

    // 1. Temukan dulu VERTEX (sudut) terdekat di rute sebagai "jangkar".
    $minVertexDistance = PHP_FLOAT_MAX;
    $closestVertexIndex = -1;

    foreach ($polyline as $index => $vertex) {
        $distance = $this->haversineDistance($targetLat, $targetLng, $vertex[0], $vertex[1]);
        if ($distance < $minVertexDistance) {
            $minVertexDistance = $distance;
            $closestVertexIndex = $index;
        }
    }

    if ($closestVertexIndex === -1) {
        return $polyline; // Failsafe
    }
    
    // Jika vertex terdekat adalah titik pertama, kita tidak bisa mencari segmen sebelumnya.
    if ($closestVertexIndex === 0) {
        $closestVertexIndex = 1; 
    }

    // 2. Batasi pencarian HANYA pada segmen sebelum dan sesudah vertex terdekat.
    // Ini adalah kunci untuk mencegah "salah potong" di dekat simpang.
    $searchStartIndex = $closestVertexIndex - 1;
    $searchEndIndex = $closestVertexIndex;

    $minSnapDistance = PHP_FLOAT_MAX;
    $bestSegmentIndex = -1;
    $closestPointCoords = null;
    
    // Lakukan pencarian presisi hanya pada area lokal yang sudah kita tentukan
    for ($i = $searchStartIndex; $i < $searchEndIndex; $i++) {
        // Pastikan index tidak keluar dari batas array
        if (!isset($polyline[$i]) || !isset($polyline[$i + 1])) continue;

        $p1 = $polyline[$i];
        $p2 = $polyline[$i + 1];
        $distanceInfo = $this->getPerpendicularDistance($targetLat, $targetLng, $p1[0], $p1[1], $p2[0], $p2[1]);
        
        if ($distanceInfo['distance'] < $minSnapDistance) {
            $minSnapDistance = $distanceInfo['distance'];
            $bestSegmentIndex = $i;
            $closestPointCoords = $distanceInfo['point'];
        }
    }
    
    // Jika karena suatu alasan titik snap tidak ditemukan, potong saja di vertex terdekat
    if ($bestSegmentIndex === -1) {
        return array_slice($polyline, 0, $closestVertexIndex + 1);
    }
    
    // 3. Bangun kembali polyline yang sudah dipotong dengan presisi.
    $trimmedPolyline = array_slice($polyline, 0, $bestSegmentIndex + 1);
    $trimmedPolyline[] = $closestPointCoords;
    
    return $trimmedPolyline;
}

        public function getPolylineForEdge(string $startNodeId, string $endNodeId): ?array
    {
        $edgeKey = "{$startNodeId}|{$endNodeId}";
        return $this->edge_polylines_map[$edgeKey] ?? null;
    }
}