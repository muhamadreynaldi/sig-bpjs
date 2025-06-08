<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class DijkstraService
{
    private array $graph_array_data = [];
    private array $node_coordinates_map = [];

    public function __construct(string $graphDataPhpFilePath = 'gis/graph_data_from_geojson_with_map.php')
    {
        $path = storage_path('app/' . $graphDataPhpFilePath);

        if (!file_exists($path)) {
            throw new \Exception("File data graf PHP tidak ditemukan: {$path}");
        }

        $data = require $path;
        $this->graph_array_data = $data['graph_array'] ?? [];
        $this->node_coordinates_map = $data['node_coordinates'] ?? [];

        if (empty($this->graph_array_data) || empty($this->node_coordinates_map)) {
            throw new \Exception("Data graf dari file PHP tidak valid atau kosong.");
        }
    }

    /**
     * Helper untuk mem-parsing string koordinat "(lng, lat)" menjadi array.
     * @param string $coordString String koordinat, contoh: "(109.123, -0.456)"
     * @return array|null Array asosiatif ['lng' => float, 'lat' => float] atau null jika gagal parse.
     */
    public function parseCoordinatesString(string $coordString): ?array
    {
        if (preg_match('/^\(\s*([-+]?\d*\.?\d+)\s*,\s*([-+]?\d*\.?\d+)\s*\)$/', $coordString, $matches)) {
            return ['lng' => (float)$matches[1], 'lat' => (float)$matches[2]];
        }
        return null;
    }

    public function findNearestNode(float $targetLat, float $targetLng): ?string
    {
        $nearestNodeId = null;
        $minDistance = PHP_FLOAT_MAX;

        if (empty($this->node_coordinates_map)) return null;

        foreach ($this->node_coordinates_map as $nodeId => $coordString) {
            $coords = $this->parseCoordinatesString($coordString);
            if ($coords) {
                $distance = $this->haversineDistance($targetLat, $targetLng, $coords['lat'], $coords['lng']);
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestNodeId = $nodeId;
                }
            }
        }
        return $nearestNodeId;
    }

    /**
     * Mengambil data koordinat untuk sebuah node ID.
     * @return array|null Array ['lat' => float, 'lng' => float] atau null.
     */
    public function getNodeCoordinatesById(string $nodeId): ?array
    {
        if (isset($this->node_coordinates_map[$nodeId])) {
            return $this->parseCoordinatesString($this->node_coordinates_map[$nodeId]);
        }
        return null;
    }


    /**
     * Mengkonversi path (array node ID) menjadi array koordinat [lat, lng] untuk polyline.
     * @param array $nodeIdsPath Array berisi ID node.
     * @return array Array berisi [latitude, longitude] untuk setiap node.
     */
    private function getPolylineCoordinatesFromNodeIds(array $nodeIdsPath): array
    {
        $coordinates = [];
        foreach ($nodeIdsPath as $nodeId) {
            $parsedCoords = $this->getNodeCoordinatesById($nodeId);
            if ($parsedCoords) {
                $coordinates[] = [$parsedCoords['lat'], $parsedCoords['lng']];
            }
        }
        return $coordinates;
    }

    /**
     * Implementasi Algoritma Dijkstra dari kode Anda.
     * @param string $sourceNodeId ID node awal.
     * @param string $targetNodeId ID node tujuan.
     * @return object Hasil Dijkstra (route_available, polyline, total_distance, dijkstra_result, message).
     */
    public function calculateDijkstraPath(string $sourceNodeId, string $targetNodeId): object
    {
        $graph_array = $this->graph_array_data;
        $vertices = [];
        $neighbours = [];

        foreach ($graph_array as $edge) {
            if (count($edge) < 3) continue;
            [$start, $end, $cost] = $edge;
            $cost = (float) $cost;

            $vertices[] = $start;
            $vertices[] = $end;

            if(isset($this->node_coordinates_map[$start]) && isset($this->node_coordinates_map[$end])) {
                $neighbours[$start][] = ["end" => $end, "cost" => $cost];
                $neighbours[$end][] = ["end" => $start, "cost" => $cost];
            }
        }
        $vertices = array_unique($vertices);

        if (!in_array($sourceNodeId, $vertices) || !isset($this->node_coordinates_map[$sourceNodeId]) ||
            !in_array($targetNodeId, $vertices) || !isset($this->node_coordinates_map[$targetNodeId])) {
            return (object)[
                "route_available" => false, "polyline" => [], "total_distance" => 0,
                "dijkstra_result" => null, "message" => "Node sumber atau tujuan tidak ditemukan dalam data graf yang valid."
            ];
        }

        $dist = [];
        $previous = [];
        foreach ($vertices as $vertex) {
            $dist[$vertex] = INF;
            $previous[$vertex] = null;
        }
        $dist[$sourceNodeId] = 0;

        $queue = new \SplPriorityQueue();
        $queue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
        $queue->insert($sourceNodeId, 0.0);

        $pathFound = false;
        while (!$queue->isEmpty()) {
            $u = $queue->extract();

            if ($u === $targetNodeId) {
                $pathFound = true;
                break;
            }

            if (!isset($neighbours[$u])) continue;

            foreach ($neighbours[$u] as $neighbor) {
                $v = $neighbor["end"];
                if (!isset($dist[$v])) continue;

                $alt = $dist[$u] + $neighbor["cost"];
                if ($alt < $dist[$v]) {
                    $dist[$v] = $alt;
                    $previous[$v] = $u;
                    $queue->insert($v, -$alt);
                }
            }
        }

        if (!$pathFound || $dist[$targetNodeId] === INF) {
            return (object)[
                "route_available" => false, "polyline" => [], "total_distance" => 0,
                "dijkstra_result" => null, "message" => "Rute ke tujuan tidak dapat ditemukan."
            ];
        }

        $pathNodeIds = [];
        $curr = $targetNodeId;
        while ($curr !== null) {
            array_unshift($pathNodeIds, $curr);
            if ($curr === $sourceNodeId) break;
            $curr = $previous[$curr] ?? null;
            if ($curr === null && (empty($pathNodeIds) || $pathNodeIds[0] !== $sourceNodeId)) {
                 return (object)[
                    "route_available" => false, "polyline" => [], "total_distance" => 0,
                    "dijkstra_result" => null, "message" => "Gagal merekonstruksi path ke source."
                ];
            }
        }

        if (empty($pathNodeIds) || $pathNodeIds[0] !== $sourceNodeId) {
             if ($sourceNodeId === $targetNodeId && empty($pathNodeIds)) {
                 $pathNodeIds = [$sourceNodeId];
             } else {
                return (object)[
                    "route_available" => false, "polyline" => [], "total_distance" => 0,
                    "dijkstra_result" => null, "message" => "Path yang direkonstruksi tidak valid."
                ];
             }
        }

        $polylineCoordinates = $this->getPolylineCoordinatesFromNodeIds($pathNodeIds);

        return (object)[
            "route_available" => true,
            "polyline" => $polylineCoordinates,
            "dijkstra_result" => $pathNodeIds,
            "total_distance" => $dist[$targetNodeId]
        ];
    }

    /**
     * Menghitung jarak Haversine antara dua titik.
     * @return float Jarak dalam kilometer.
     */
    public function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}