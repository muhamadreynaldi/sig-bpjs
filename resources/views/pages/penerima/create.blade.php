@extends('layouts.master')

@section('title', 'Tambah Penerima BPJS - SIG BPJS')

{{-- ... (bagian @section('content') tetap sama) ... --}}
@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-plus-circle me-2"></i>Tambah Data Penerima Baru
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('penerima.store') }}" method="POST">
                @csrf
                @include('pages.penerima.partials.form-fields', [
                    'penerima' => new App\Models\Penerima(),
                    'statusOptions' => $statusOptions ?? ['Aktif', 'Nonaktif', 'Meninggal']
                ])
                <div class="row">
                    <div class="col-md-6 offset-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan Data
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

        // Koordinat default untuk pusat peta (misal: Kantor Desa Sungai Raya atau Pontianak)
        // Ganti dengan koordinat yang lebih relevan untuk area input Anda
        const defaultCenterLat = {{ config('leaflet.map_center_latitude', -0.06961) }}; // Ambil dari config atau hardcode
        const defaultCenterLng = {{ config('leaflet.map_center_longitude', 109.36765) }};
        const initialZoom = {{ config('leaflet.zoom_level', 15) }};

        const map = L.map('mapInputCoordinate').setView([defaultCenterLat, defaultCenterLng], initialZoom);

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
                updateMarkerAndInputs(L.latLng(lat, lng));
            }
        }
        latInput.addEventListener('change', onInputChange);
        lngInput.addEventListener('change', onInputChange);

        // Inisialisasi marker jika input sudah ada nilainya (misal dari old input)
        if (latInput.value && lngInput.value) {
            onInputChange();
        }
    });
</script>
@endpush