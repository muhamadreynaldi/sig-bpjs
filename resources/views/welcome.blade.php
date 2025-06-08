@extends('layouts.master')

@section('title', 'Selamat Datang - SIG BPJS')

@section('content')
<div class="row mt-4">
    <div class="col-md-12">
        <h1>Selamat Datang di Aplikasi SIG Pemetaan Penerima BPJS</h1>
        <p>Ini adalah halaman awal. Silakan navigasi melalui menu di samping.</p>
        <p>Proyek ini dibuat menggunakan Laravel {{ app()->version() }}.</p>
    </div>
</div>
@endsection

@push('styles')
@endpush

@push('scripts')
@endpush