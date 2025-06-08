<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-f">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIG Pemetaan BPJS')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @stack('styles')
</head>
<body>
    <div class="d-flex" id="wrapper">
        @include('partials.sidebar')
        <div id="page-content-wrapper">
            @include('partials.navbar')
            <div class="container-fluid px-4">
                @yield('content')
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="modal fade" id="penerimaDetailModal" tabindex="-1" aria-labelledby="penerimaDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="penerimaDetailModalLabel">Detail Penerima</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7">
                        <table class="table table-sm table-borderless">
                            <tbody>
                                <tr><th style="width:30%;">Nama</th><td id="detailNama"></td></tr>
                                <tr><th>NIK</th><td id="detailNik"></td></tr>
                                <tr><th>Alamat</th><td id="detailAlamat"></td></tr>
                                <tr><th>Dusun</th><td id="detailDusun"></td></tr>
                                <tr><th>Status BPJS</th><td id="detailStatus"></td></tr>
                                <tr><th>Latitude</th><td id="detailLat"></td></tr>
                                <tr><th>Longitude</th><td id="detailLng"></td></tr>
                                <tr><th>Diperbarui</th><td id="detailUpdatedAt"></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-5">
                        <h6>Peta Lokasi:</h6>
                        <div id="mapDetailModal" style="height: 250px; width: 100%; border-radius: 0.25rem; border: 1px solid #ced4da;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
    
    <script src="{{ asset('js/script.js') }}"></script>
    @stack('scripts')
</body>
</html>