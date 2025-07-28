@extends('layouts.master')

@section('title', 'Dashboard - SIG BPJS')

@push('styles')
<style>
    .summary-card {
        border-radius: 0.5rem;
        color: #fff;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15)!important;
    }
    .summary-card .card-icon {
        font-size: 3rem;
        opacity: 0.8;
    }
    .summary-card .card-title-text {
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .summary-card .card-value {
        font-size: 2.25rem;
        font-weight: bold;
    }
    .bg-card-total { background-color: #17A2B8; }
    .bg-card-aktif { background-color: #28A745; }
    .bg-card-nonaktif { background-color: #FFC107; color: #212529 !important; }
    .bg-card-meninggal { background-color: #343A40; }
    .bg-card-dusun { background-color: #6F42C1; }

    .quick-link-card {
        transition: transform 0.2s ease-in-out;
    }
    .quick-link-card:hover {
        transform: scale(1.03);
    }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info shadow-sm" role="alert">
                <h4 class="alert-heading">Selamat Datang, {{ $user->name }}!</h4>
                <p>Anda telah login ke Sistem Informasi Geografis Pemetaan Penerima BPJS.</p>
                <hr>
                <p class="mb-0">Gunakan menu navigasi di samping atau tombol di bawah untuk mengakses fitur-fitur aplikasi.</p>
            </div>
        </div>
    </div>

<div class="card mt-4">
    <div class="card-header">
        <h4>Layanan BPJS Kesehatan</h4>
    </div>
    <div class="card-body">
        <p>Untuk pengecekan status kepesertaan JKN, hubungi Layanan Resmi BPJS Kesehatan (PANDAWA) melalui WhatsApp.</p>
        <a href="https://wa.me/628118165165" target="_blank" class="btn btn-info">
            <i class="fab fa-whatsapp"></i> Hubungi PANDAWA (08118165165)
        </a>
    </div>
</div>

    <div class="row mt-2 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Pencarian Cepat Penerima BPJS</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard') }}">
                        <div class="input-group">
                            <select name="search" id="searchDashboardSelect2" class="form-select form-select-lg">
                                <option value="">Ketik atau pilih Nama/NIK...</option>
                                @foreach($searchOptionsList as $option)
                                    <option value="{{ $option->nik }} - {{ $option->nama }}"
                                            {{ ($search ?? '') == ($option->nik . ' - ' . $option->nama) ? 'selected' : '' }}>
                                        {{ $option->nik }} - {{ $option->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary btn-lg" type="submit">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            @if(!empty($search))
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Reset
                            </a>
                            @endif
                        </div>
                    </form>

                    @if(isset($search) && $search && $penerimasFound->isNotEmpty())
                        <hr>
                        <h6 class="mt-3">Hasil Pencarian untuk "{{ $search }}":</h6>
                        <ul class="list-group list-group-flush">
                            @foreach($penerimasFound as $penerima)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $penerima->nama }}</strong> (NIK: {{ $penerima->nik }})<br>
                                        <small>Dusun: {{ $penerima->dusun }}, Status: {{ $penerima->status }}</small>
                                    </div>
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('penerima.show', $penerima->id) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail (Admin)">
                                            <i class="fas fa-user-shield"></i> Detail
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="showPenerimaDetailModal({{ $penerima->id }})" title="Lihat Detail">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @elseif(isset($search) && $search)
                        <hr>
                        <div class="alert alert-warning mt-3" role="alert">
                            Tidak ada penerima yang ditemukan dengan kata kunci "{{ $search }}".
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card summary-card bg-card-total shadow">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-title-text">Total Penerima</div>
                        <div class="card-value">{{ $totalPenerima }}</div>
                    </div>
                    <i class="fas fa-users card-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card summary-card bg-card-aktif shadow">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-title-text">BPJS Aktif</div>
                        <div class="card-value">{{ $totalAktif }}</div>
                    </div>
                    <i class="fas fa-user-check card-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card summary-card bg-card-nonaktif shadow">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-title-text">BPJS Nonaktif</div>
                        <div class="card-value">{{ $totalNonaktif }}</div>
                    </div>
                    <i class="fas fa-user-clock card-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card summary-card bg-card-meninggal shadow">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-title-text">Non JKN</div>
                        <div class="card-value">{{ $totalMeninggal }}</div>
                    </div>
                    <i class="fas fa-user-slash card-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <h5 class="mb-3">Navigasi Cepat:</h5>
        </div>
    <div class="row justify-content-center">
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-info quick-link-card shadow">
                <div class="card-body text-center">
                    <i class="fas fa-map-marked-alt fa-3x mb-2"></i>
                    <h5 class="card-title">Pemetaan Penerima</h5>
                    <p class="card-text small">Lihat persebaran semua penerima BPJS pada peta.</p>
                    <a href="{{ route('pemetaan.index') }}" class="btn btn-light stretched-link">Buka Peta <i class="fas fa-arrow-circle-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
             <div class="card text-white bg-success quick-link-card shadow">
                <div class="card-body text-center">
                    <i class="fas fa-route fa-3x mb-2"></i>
                    <h5 class="card-title">Pencarian Rute</h5>
                    <p class="card-text small">Hitung rute tercepat ke lokasi penerima BPJS.</p>
                    <a href="{{ route('rute.index') }}" class="btn btn-light stretched-link">Cari Rute <i class="fas fa-arrow-circle-right ms-1"></i></a>
                </div>
            </div>
        </div>
                @auth
            @if(Auth::user()->isAdmin())
            <div class="col-md-4 mb-3">
                 <div class="card text-white bg-primary quick-link-card shadow">
                    <div class="card-body text-center">
                        <i class="fas fa-users-cog fa-3x mb-2"></i>
                        <h5 class="card-title">Manajemen Data</h5>
                        <p class="card-text small">Kelola data penerima BPJS (Tambah, Edit, Hapus).</p>
                        <a href="{{ route('penerima.index') }}" class="btn btn-light stretched-link">Kelola Data <i class="fas fa-arrow-circle-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            @endif
        @endauth
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#searchDashboardSelect2').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ketik atau pilih Nama/NIK...',
        allowClear: true
    });

});
</script>
@endpush