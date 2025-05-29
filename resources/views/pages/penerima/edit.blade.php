@extends('layouts.master')

@section('title', 'Edit Penerima BPJS - SIG BPJS')

{{-- ... (bagian @section('content') tetap sama) ... --}}
@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-edit me-2"></i>Edit Data Penerima: {{ $penerima->nama }}
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('penerima.update', $penerima->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('pages.penerima.partials.form-fields', [
                    'penerima' => $penerima,
                    'statusOptions' => $statusOptions ?? ['Aktif', 'Nonaktif', 'Meninggal']
                ])
                <div class="row">
                     <div class="col-md-6 offset-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('penerima.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');

        // Ambil koordinat awal dari data penerima yang diedit
        // Fallback ke default jika tidak ada (seharusnya selalu ada untuk edit)
        const initialLat = parseFloat(latInput.value) || {{ config('leaflet.map_center_latitude', -0.025) }};
        const initialLng = parseFloat(lngInput.value) || {{ config('leaflet.map_center_longitude', 109.330) }};
        const initialZoom = {{ config('leaflet.zoom_level_edit', 16) }}; // Zoom bisa lebih detail untuk edit

        const map = L.map('mapInputCoordinate').setView([initialLat, initialLng], initialZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        let marker;

        // Fungsi untuk update input dan marker
        function updateMarkerAndInputs(latlng) {
            latInput.value = latlng.lat.toFixed(7);
            lngInput.value = latlng.lng.toFixed(7);

            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker(latlng).addTo(map);
            map.panTo(latlng); // Pusatkan peta ke marker baru
        }

        // Event listener saat peta diklik
        map.on('click', function(e) {
            updateMarkerAndInputs(e.latlng);
        });

        // Opsional: Jika ingin marker muncul saat input lat/lng diubah manual
        function onInputChange() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                 // Hanya update jika berbeda dari posisi marker saat ini untuk menghindari loop
                if (!marker || marker.getLatLng().lat.toFixed(7) !== lat.toFixed(7) || marker.getLatLng().lng.toFixed(7) !== lng.toFixed(7)) {
                    updateMarkerAndInputs(L.latLng(lat, lng));
                }
            }
        }
        latInput.addEventListener('change', onInputChange);
        lngInput.addEventListener('change', onInputChange);

        // Tempatkan marker awal pada koordinat yang ada saat halaman edit dimuat
        if (latInput.value && lngInput.value) {
            const currentLatLng = L.latLng(initialLat, initialLng);
            marker = L.marker(currentLatLng).addTo(map);
        }
    });
</script>
@endpush