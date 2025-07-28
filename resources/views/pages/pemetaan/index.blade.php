@extends('layouts.master')

@section('title', 'Pemetaan Penerima BPJS - SIG BPJS')

@push('styles')
<style>
    #mapPemetaan { height: 600px; }
    .filter-card { margin-bottom: 20px; }
    .filter-card .select2-container--bootstrap-5 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
    }
    .filter-card .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5rem !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
     .filter-card .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12">
            <h3 class="mb-3"><i class="fas fa-map-marked-alt me-2"></i>Pemetaan Persebaran Penerima BPJS</h3>
        </div>
    </div>

    <div class="card filter-card shadow-sm">
        <div class="card-header"><i class="fas fa-filter me-1"></i>Filter Data Peta</div>
        <div class="card-body">
            {{-- Ganti form filter Anda menjadi seperti ini --}}
<form id="filterForm" method="GET" action="{{ route('pemetaan.index') }}">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="search_nama_nik" class="form-label">Cari Nama/NIK:</label>
            {{-- Tambahkan onchange di sini --}}
            <select name="search_nama_nik" id="searchPemetaanSelect2" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Penerima --</option>
                @if(isset($searchOptionsList))
                    @foreach($searchOptionsList as $option)
                        <option value="{{ $option->nik }} - {{ $option->nama }}"
                                {{ (isset($input['search_nama_nik']) && $input['search_nama_nik'] == ($option->nik . ' - ' . $option->nama)) ? 'selected' : '' }}>
                            {{ $option->nik }} - {{ $option->nama }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-md-3">
            <label for="dusun" class="form-label">Filter Dusun:</label>
            {{-- Tambahkan onchange di sini --}}
            <select name="dusun" id="dusun" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Dusun</option>
                @foreach($dusunList as $itemDusun)
                <option value="{{ $itemDusun }}" {{ ($input['dusun'] ?? '') == $itemDusun ? 'selected' : '' }}>{{ $itemDusun }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label">Filter Status BPJS:</label>
            {{-- Tambahkan onchange di sini --}}
            <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach($statusList as $itemStatus)
                <option value="{{ $itemStatus }}" {{ ($input['status'] ?? '') == $itemStatus ? 'selected' : '' }}>{{ $itemStatus }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            {{-- Tombol ini sekarang opsional, tapi baik untuk tetap ada --}}
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sync-alt"></i> Terapkan
            </button>
            @if( !empty($input['search_nama_nik']) || !empty($input['dusun']) || !empty($input['status']) )
            <a href="{{ route('pemetaan.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                <i class="fas fa-times"></i> Reset
            </a>
            @endif
        </div>
    </div>
</form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if( ($input['search_nama_nik'] ?? false) && $penerimas->isEmpty() )
                <div class="alert alert-warning text-center">
                    Penerima dengan nama/NIK "<b>{{ $input['search_nama_nik'] }}</b>" tidak ditemukan.
                </div>
            @endif
            <div id="mapPemetaan"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const penerimasData = @json($penerimas);
    const defaultLocation = @json($defaultLocation);
    const initialZoom = @json($zoomLevel);
    const isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};

    function getMarkerIconByStatus(status) {
        let iconUrl;
        switch (String(status).toLowerCase()) {
            case 'aktif':
                iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png';
                break;
            case 'nonaktif':
                iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png';
                break;
            case 'meninggal':
                iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-black.png';
                break;
            default:
                iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png';
        }
        return L.icon({
            iconUrl: iconUrl,
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
    }

    function getStatusColorClass(status) {
        switch (String(status).toLowerCase()) {
            case 'aktif': return 'success';
            case 'nonaktif': return 'warning text-dark';
            case 'meninggal': return 'dark';
            default: return 'secondary';
        }
    }

        document.addEventListener('DOMContentLoaded', function() {
        const mapElement = document.getElementById('mapPemetaan');
        if (mapElement) {
            const map = L.map('mapPemetaan').setView(defaultLocation, initialZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const markersLayer = L.layerGroup().addTo(map);

            if (penerimasData && Array.isArray(penerimasData)) {
                penerimasData.forEach(penerima => {
                    if (penerima && typeof penerima.lat !== 'undefined' && typeof penerima.lng !== 'undefined' &&
                        penerima.lat !== null && penerima.lng !== null) {

                        let detailLinkHtml;
                        if (isAdmin) {
                            detailLinkHtml = `<a href="/penerima/${penerima.id}" target="_blank" class="btn btn-sm btn-outline-info mt-1 d-block text-center">Detail (Admin) <i class="fas fa-external-link-alt"></i></a>`;
                        } else {
                            detailLinkHtml = `<button type="button" class="btn btn-sm btn-outline-primary mt-1 d-block w-100 text-center" onclick="showPenerimaDetailModal(${penerima.id})">Lihat Detail <i class="fas fa-eye"></i></button>`;
                        }

                        const popupContent = `
                            <div style="min-width: 200px; font-size: 0.9rem;">
                                <strong>${penerima.nama || 'Nama tidak tersedia'}</strong><br>
                                NIK: ${penerima.nik || '-'}<br>
                                Status: <span class="badge bg-${getStatusColorClass(penerima.status)}">${penerima.status || '-'}</span><br>
                                Dusun: ${penerima.dusun || '-'}<br>
                                ${penerima.alamat ? `Alamat: ${String(penerima.alamat).substring(0, 50)}${String(penerima.alamat).length > 50 ? '...' : ''}<br>` : ''}
                                <hr class="my-1">
                                ${detailLinkHtml}
                            </div>
                        `;
                        const marker = L.marker([parseFloat(penerima.lat), parseFloat(penerima.lng)], {
                            icon: getMarkerIconByStatus(penerima.status)
                        }).bindPopup(popupContent);
                        markersLayer.addLayer(marker);
                    }
                });
            }

            if (markersLayer.getLayers().length > 0) {
                map.fitBounds(markersLayer.getBounds(), { padding: [40, 40] });
            } else if (penerimasData && penerimasData.length === 1 && penerimasData[0] &&
                       typeof penerimasData[0].lat !== 'undefined' && typeof penerimasData[0].lng !== 'undefined' &&
                       penerimasData[0].lat !== null && penerimasData[0].lng !== null) {
                map.setView([parseFloat(penerimasData[0].lat), parseFloat(penerimasData[0].lng)], 16);
            }
        }
    });

    $(function() {
    $('#searchPemetaanSelect2').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ketik atau pilih Nama/NIK...',
        allowClear: true
    });
    });
</script>
@endpush