@extends('layouts.master')

@section('title', 'Detail Penerima: ' . $penerima->nama . ' - SIG BPJS')

@push('styles')
<style>
    #mapDetail { height: 300px; }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-user-tag me-2"></i>Detail Penerima: {{ $penerima->nama }}
            </h5>
            <a href="{{ route('penerima.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke List
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-7">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th style="width: 30%;">NIK</th>
                                <td>{{ $penerima->nik }}</td>
                            </tr>
                            <tr>
                                <th>Nama Lengkap</th>
                                <td>{{ $penerima->nama }}</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>{{ $penerima->alamat ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Dusun</th>
                                <td>{{ $penerima->dusun }}</td>
                            </tr>
                            <tr>
                                <th>Status BPJS</th>
                                <td>
                                    @if($penerima->status == 'Aktif')
                                        <span class="badge bg-success">{{ $penerima->status }}</span>
                                    @elseif($penerima->status == 'Nonaktif')
                                        <span class="badge bg-warning text-dark">{{ $penerima->status }}</span>
                                    @elseif($penerima->status == 'Meninggal')
                                        <span class="badge bg-dark">{{ $penerima->status }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $penerima->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Latitude</th>
                                <td>{{ $penerima->lat }}</td>
                            </tr>
                            <tr>
                                <th>Longitude</th>
                                <td>{{ $penerima->lng }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Diperbarui</th>
                                <td>{{ $penerima->updated_at->format('d M Y, H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <a href="{{ route('penerima.edit', $penerima->id) }}" class="btn btn-primary mt-2">
                        <i class="fas fa-edit me-1"></i> Edit Data Ini
                    </a>
                </div>
                <div class="col-md-5">
                    <h6>Peta Lokasi:</h6>
                    <div id="mapDetail"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const latDetail = {{ $penerima->lat ?? 0 }};
    const lngDetail = {{ $penerima->lng ?? 0 }};

    if (latDetail && lngDetail) {
        const mapDetail = L.map('mapDetail').setView([latDetail, lngDetail], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapDetail);
        L.marker([latDetail, lngDetail]).addTo(mapDetail)
            .bindPopup('<b>{{ $penerima->nama }}</b><br>Status: {{ $penerima->status }}')
            .openPopup();
    } else {
        document.getElementById('mapDetail').innerHTML = '<p class="text-center text-muted">Koordinat tidak tersedia.</p>';
    }
</script>
@endpush