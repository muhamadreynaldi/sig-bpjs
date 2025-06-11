@extends('layouts.master')

@section('title', 'Pencarian Rute - SIG BPJS')

@push('styles')
<style>
    #mapRute { height: 500px; }
    .route-form-card { margin-bottom: 20px; }
    .address-info {
        font-size: 0.9rem;
        color: #555;
    }
    #routeResultInfo {
        display: none; /* Sembunyikan secara default */
    }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12">
            <h3 class="mb-3"><i class="fas fa-route me-2"></i>Pencarian Rute Tercepat (Algoritma Dijkstra)</h3>
        </div>
    </div>

    <div class="card route-form-card shadow-sm">
        <div class="card-header"><i class="fas fa-map-signs me-1"></i>Formulir Pencarian Rute</div>
        <div class="card-body">
            <form id="routeSearchForm">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="start_point_display" class="form-label">Titik Awal:</label>
                        <input type="text" id="start_point_display" class="form-control" value="Kantor Desa Sungai Raya (Fixed)" readonly>
                    </div>
                    <div class="col-md-5">
                        <label for="destination_penerima" class="form-label">Titik Tujuan (Penerima):</label>
                        <select id="destination_penerima" name="destination_penerima_id" class="form-select" required>
                            <option value="">-- Pilih Penerima Tujuan --</option>
                            @foreach($allPenerimas as $penerima)
                                <option value="{{ $penerima->id }}" data-lat="{{ $penerima->lat }}" data-lng="{{ $penerima->lng }}">
                                    {{ $penerima->nik }} - {{ $penerima->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-directions"></i> Cari Rute
                        </button>
                    </div>
                </div>
            </form>
            <div id="routeResultInfo" class="mt-4 alert alert-info">
                <div class="address-info mb-2">
                    <p class="mb-0"><strong><i class="fas fa-map-marker-alt text-danger me-1"></i>Dari:</strong> <span id="routeStartAddress">-</span></p>
                    <p class="mb-0"><strong><i class="fas fa-map-marker-alt text-primary me-1"></i>Ke:</strong> <span id="routeDestinationAddress">-</span></p>
                </div>
                <hr class="my-2">
                <p class="mb-0">Total Jarak: <strong id="totalRouteDistance">- km</strong></p>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
         <div class="card-header"><i class="fas fa-road me-1"></i>Peta Rute</div>
        <div class="card-body p-0">
            <div id="mapRute"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kantorDesaFixedCoords = @json($kantorDesaCoords);
    const defaultMapCenter = @json($defaultMapCenter);
    const defaultZoom = @json($defaultZoomLevel);

    $('#destination_penerima').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Penerima Tujuan --',
        allowClear: true
    });

    const mapRute = L.map('mapRute').setView(defaultMapCenter, defaultZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(mapRute);

    const startMarker = L.marker(kantorDesaFixedCoords, {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        })
    }).addTo(mapRute).bindPopup("<b>Titik Awal:</b><br>Kantor Desa Sungai Raya");

    let destinationMarker = null;
    let routePolyline = null;
    let nodeMarkersLayer = L.layerGroup().addTo(mapRute); // Layer untuk marker sudut (persimpangan)

    const routeResultInfo = document.getElementById('routeResultInfo');
    const totalDistanceEl = document.getElementById('totalRouteDistance');
    const routeStartAddressEl = document.getElementById('routeStartAddress');
    const routeDestinationAddressEl = document.getElementById('routeDestinationAddress');

    function clearMapLayers() {
        if (routePolyline) mapRute.removeLayer(routePolyline);
        if (destinationMarker) mapRute.removeLayer(destinationMarker);
        nodeMarkersLayer.clearLayers();
        routePolyline = null;
        destinationMarker = null;
        routeResultInfo.style.display = 'none';
    }
    
    $('#destination_penerima').on('change', function() {
        clearMapLayers();
    });

    document.getElementById('routeSearchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearMapLayers(); // Hapus rute lama sebelum mencari yang baru

        const destinationSelect = document.getElementById('destination_penerima');
        const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
        const penerimaId = selectedOption.value;
        const namaPenerima = selectedOption.text.split(' - ')[1] || selectedOption.text;

        if (!penerimaId) {
            alert('Silakan pilih penerima tujuan.');
            return;
        }

        const destLat = parseFloat(selectedOption.dataset.lat);
        const destLng = parseFloat(selectedOption.dataset.lng);

        // Tampilkan loading atau status
        routeStartAddressEl.textContent = 'Mencari rute...';
        routeDestinationAddressEl.textContent = 'Mencari rute...';
        totalDistanceEl.textContent = '- km';
        routeResultInfo.style.display = 'block';

        fetch('{{ route("route.calculate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                start_lat: kantorDesaFixedCoords[0],
                start_lng: kantorDesaFixedCoords[1],
                destination_penerima_id: penerimaId
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.error || 'Terjadi kesalahan jaringan'); });
            }
            return response.json();
        })
        .then(data => {
            routeStartAddressEl.textContent = data.start_address_display || 'Tidak diketahui';
            routeDestinationAddressEl.textContent = data.destination_address_display || 'Tidak diketahui';

            if (data.path && data.path.length > 0) {
                // Gambar rute yang akurat
                routePolyline = L.polyline(data.path, { color: '#0d6efd', weight: 6, opacity: 0.8 }).addTo(mapRute);
                mapRute.fitBounds(routePolyline.getBounds(), { padding: [50, 50] });
                
                totalDistanceEl.textContent = `${data.distance} km`;
                routeResultInfo.classList.replace('alert-danger', 'alert-info') || routeResultInfo.classList.add('alert-info');

                // Tambahkan marker tujuan
                destinationMarker = L.marker([destLat, destLng]).addTo(mapRute).bindPopup(`<b>Tujuan:</b><br>${namaPenerima}`);
                
                // --- INI BAGIAN BARU: Tambahkan marker untuk setiap node ---
                if (data.nodes && data.nodes.length > 0) {
                    data.nodes.forEach(node => {
                        L.circleMarker(node.coords, {
                            radius: 6,
                            color: '#FFFFFF',      // Warna border
                            weight: 2,            // Ketebalan border
                            fillColor: '#003366',   // Warna isian
                            fillOpacity: 1
                        })
                        // Tambahkan popup dengan ID node
                        .bindPopup(`<b>Node ID:</b><br>${node.id}`)
                        .addTo(nodeMarkersLayer);
                    });
                }
            } else {
                throw new Error(data.error || 'Tidak dapat menghitung rute.');
            }
        })
        .catch(error => {
            console.error('Error calculating route:', error);
            routeStartAddressEl.textContent = 'Gagal memuat';
            routeDestinationAddressEl.textContent = 'Gagal memuat';
            totalDistanceEl.innerHTML = `<span class="text-danger">${error.message}</span>`;
            routeResultInfo.classList.replace('alert-info', 'alert-danger') || routeResultInfo.classList.add('alert-danger');
        });
    });
});
</script>
@endpush