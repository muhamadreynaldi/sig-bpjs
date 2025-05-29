@extends('layouts.master')

@section('title', 'Pencarian Rute - SIG BPJS')

@push('styles')
<style>
    #mapRute { height: 500px; }
    .route-form-card { margin-bottom: 20px; }
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
                        {{-- Koordinat kantor desa akan diambil dari variabel JS --}}
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
            <div id="routeResultInfo" class="mt-3">
                <p class="mb-0">Total Jarak: <strong id="totalRouteDistance">- km</strong></p>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
         <div class="card-header"><i class="fas fa-road me-1"></i>Peta Rute</div>
        <div class="card-body">
            <div id="mapRute"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Variabel dari PHP (pastikan sudah ada dari controller)
    const kantorDesaFixedCoords = @json($kantorDesaCoords);
    const defaultMapCenter = @json($defaultMapCenter);
    const defaultZoom = @json($defaultZoomLevel);
    // const allPenerimasForMap = @json($allPenerimas); // Ini mungkin tidak terlalu dibutuhkan lagi jika Select2 menghandle list

    $(document).ready(function() { // Pastikan DOM sudah siap
        // Inisialisasi Select2 pada dropdown penerima
        $('#destination_penerima').select2({
            theme: 'bootstrap-5', // Gunakan tema Bootstrap 5
            placeholder: '-- Pilih Penerima Tujuan --',
            allowClear: true // Izinkan untuk menghapus pilihan
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
        const totalDistanceEl = document.getElementById('totalRouteDistance');
        const routeSearchForm = document.getElementById('routeSearchForm');
        // const destinationSelect = document.getElementById('destination_penerima'); // Tidak dipakai langsung lagi untuk event

        // Event listener untuk perubahan pada Select2
        $('#destination_penerima').on('select2:select', function (e) {
            if (destinationMarker) {
                mapRute.removeLayer(destinationMarker);
                destinationMarker = null;
            }
            const selectedData = e.params.data; // Data dari option yang terpilih
            const selectedOptionElement = selectedData.element; // Elemen <option> asli

            if (selectedOptionElement && selectedOptionElement.value) {
                const lat = parseFloat(selectedOptionElement.dataset.lat);
                const lng = parseFloat(selectedOptionElement.dataset.lng);
                const namaPenerima = selectedOptionElement.text.split(' - ')[1] || selectedOptionElement.text; // Ambil nama

                if (!isNaN(lat) && !isNaN(lng)) {
                    destinationMarker = L.marker([lat, lng], {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
                        })
                    }).addTo(mapRute).bindPopup(`<b>Tujuan:</b><br>${namaPenerima}`);
                    mapRute.setView([lat, lng], 15);
                }
            }
        });

        // Event listener jika pilihan Select2 dihapus (opsional)
        $('#destination_penerima').on('select2:unselect', function (e) {
            if (destinationMarker) {
                mapRute.removeLayer(destinationMarker);
                destinationMarker = null;
            }
            if (routePolyline) { // Hapus juga rute jika tujuan dikosongkan
                mapRute.removeLayer(routePolyline);
                routePolyline = null;
                totalDistanceEl.textContent = '- km';
            }
            // mapRute.setView(defaultMapCenter, defaultZoom); // Kembali ke view default
        });


        routeSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (routePolyline) {
                mapRute.removeLayer(routePolyline);
                routePolyline = null;
            }
            totalDistanceEl.textContent = '- km';

            // Ambil value dari Select2
            const penerimaId = $('#destination_penerima').val();

            if (!penerimaId) {
                alert('Silakan pilih penerima tujuan.');
                return;
            }

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
                if (!response.ok) { return response.json().then(err => { throw err; }); }
                return response.json();
            })
            .then(data => {
                if (data.path && data.path.length > 0) {
                    routePolyline = L.polyline(data.path, { color: 'darkblue', weight: 6, opacity: 0.8 }).addTo(mapRute);
                    mapRute.fitBounds(routePolyline.getBounds(), { padding: [50, 50] });
                    totalDistanceEl.textContent = `${data.distance.toFixed(2)} km`;
                } else if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    alert('Tidak dapat menghitung rute.');
                }
            })
            .catch(error => {
                console.error('Error calculating route:', error);
                alert(error.error || 'Terjadi kesalahan saat menghitung rute.');
            });
        });
    }); // Akhir dari $(document).ready
</script>
@endpush