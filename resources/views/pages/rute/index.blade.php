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
            <div id="routeResultInfo" class="mt-4">
                {{-- Elemen untuk menampilkan alamat --}}
                <div class="address-info mb-2">
                    <p class="mb-0"><strong><i class="fas fa-map-marker-alt text-danger me-1"></i>Dari:</strong> <span id="routeStartAddress">-</span></p>
                    <p class="mb-0"><strong><i class="fas fa-map-marker-alt text-primary me-1"></i>Ke:</strong> <span id="routeDestinationAddress">-</span></p>
                </div>
                <hr class="my-2">
                <p class="mb-0">Total Jarak: <strong id="totalRouteDistance">- km</strong></p>
                {{-- Tempat untuk info rute alternatif jika ditambahkan nanti --}}
                {{-- <p class="mb-0" id="alternativeRouteInfo" style="display:none;">
                    Total Jarak (Rute Alternatif): <strong id="totalAlternativeRouteDistance">- km</strong>
                </p> --}}
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
    const kantorDesaFixedCoords = @json($kantorDesaCoords); // Pastikan ini benar: [-0.06173637665163168, 109.36675978082265]
    const defaultMapCenter = @json($defaultMapCenter);
    const defaultZoom = @json($defaultZoomLevel);

    $(document).ready(function() {
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
        // let alternativeRoutePolyline = null; // Untuk rute alternatif nanti

        const totalDistanceEl = document.getElementById('totalRouteDistance');
        const routeSearchForm = document.getElementById('routeSearchForm');
        
        // --- TAMBAHAN: Ambil elemen untuk alamat ---
        const routeStartAddressEl = document.getElementById('routeStartAddress');
        const routeDestinationAddressEl = document.getElementById('routeDestinationAddress');
        // const alternativeRouteInfoEl = document.getElementById('alternativeRouteInfo'); // Untuk rute alternatif nanti
        // const totalAlternativeRouteDistanceEl = document.getElementById('totalAlternativeRouteDistance'); // Untuk rute alternatif nanti
        // --- ------------------------------------

        $('#destination_penerima').on('select2:select', function (e) {
            if (destinationMarker) {
                mapRute.removeLayer(destinationMarker);
                destinationMarker = null;
            }
            const selectedData = e.params.data;
            const selectedOptionElement = selectedData.element;

            if (selectedOptionElement && selectedOptionElement.value) {
                const lat = parseFloat(selectedOptionElement.dataset.lat);
                const lng = parseFloat(selectedOptionElement.dataset.lng);
                const namaPenerima = selectedOptionElement.text.split(' - ')[1] || selectedOptionElement.text;

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

        $('#destination_penerima').on('select2:unselect', function (e) {
            if (destinationMarker) mapRute.removeLayer(destinationMarker);
            if (routePolyline) mapRute.removeLayer(routePolyline);
            // if (alternativeRoutePolyline) mapRute.removeLayer(alternativeRoutePolyline); // Untuk rute alternatif nanti

            destinationMarker = null;
            routePolyline = null;
            // alternativeRoutePolyline = null; // Untuk rute alternatif nanti

            totalDistanceEl.textContent = '- km';
            routeStartAddressEl.textContent = '-'; // Reset alamat
            routeDestinationAddressEl.textContent = '-'; // Reset alamat
            // alternativeRouteInfoEl.style.display = 'none'; // Untuk rute alternatif nanti
        });


        routeSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (routePolyline) mapRute.removeLayer(routePolyline);
            // if (alternativeRoutePolyline) mapRute.removeLayer(alternativeRoutePolyline); // Reset rute alternatif juga

            routePolyline = null;
            // alternativeRoutePolyline = null;

            totalDistanceEl.textContent = '- km';
            routeStartAddressEl.textContent = 'Memuat...'; // Tampilkan status loading
            routeDestinationAddressEl.textContent = 'Memuat...';
            // alternativeRouteInfoEl.style.display = 'none';

            const penerimaId = $('#destination_penerima').val();

            if (!penerimaId) {
                alert('Silakan pilih penerima tujuan.');
                routeStartAddressEl.textContent = '-'; // Reset jika tidak ada tujuan
                routeDestinationAddressEl.textContent = '-';
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
                // --- TAMBAHAN: Set teks alamat ---
                routeStartAddressEl.textContent = data.start_address_display || 'Tidak diketahui';
                routeDestinationAddressEl.textContent = data.destination_address_display || 'Tidak diketahui';
                // --- -----------------------------

                if (data.path && data.path.length > 0) {
                    routePolyline = L.polyline(data.path, { color: 'darkblue', weight: 6, opacity: 0.8 }).addTo(mapRute);
                    mapRute.fitBounds(routePolyline.getBounds(), { padding: [50, 50] });
                    totalDistanceEl.textContent = `${data.distance.toFixed(2)} km`;

                    // Logika untuk rute alternatif (jika ada)
                    // if (data.alternative_route && data.alternative_route.path && data.alternative_route.path.length > 0) {
                    //     alternativeRoutePolyline = L.polyline(data.alternative_route.path, { color: 'green', weight: 5, opacity: 0.7, dashArray: '5, 10' }).addTo(mapRute);
                    //     totalAlternativeRouteDistanceEl.textContent = `${data.alternative_route.distance.toFixed(2)} km`;
                    //     alternativeRouteInfoEl.style.display = 'block';
                    // }

                } else if (data.error) {
                    alert('Error: ' + data.error);
                     totalDistanceEl.textContent = '- km'; // Reset jarak jika error
                } else {
                    alert('Tidak dapat menghitung rute.');
                    totalDistanceEl.textContent = '- km'; // Reset jarak jika error
                }
            })
            .catch(error => {
                console.error('Error calculating route:', error);
                let errorMessage = 'Terjadi kesalahan saat menghitung rute.';
                if (error && error.error) { // Jika error adalah objek dengan properti 'error'
                    errorMessage = error.error;
                } else if (error && error.message) { // Jika error adalah objek Error JS standar
                     errorMessage = error.message;
                }
                
                alert(errorMessage);
                routeStartAddressEl.textContent = '-'; // Reset jika error
                routeDestinationAddressEl.textContent = '-';
                totalDistanceEl.textContent = '- km';
            });
        });
    });
</script>
@endpush