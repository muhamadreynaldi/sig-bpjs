@extends('layouts.master')

@section('title', 'Login - SIG BPJS')

@push('styles')
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
    body {
        background-color: #f8f9fa;
        /* display: flex; /* Dihapus karena #wrapper akan jadi flex utama */
        /* flex-direction: column; */
    }
    /* Pastikan #wrapper mengambil tinggi penuh dan bisa memusatkan #page-content-wrapper */
    #wrapper {
        display: flex;
        flex-direction: column; /* Konten akan mengisi ruang secara vertikal */
        min-height: 100vh;
        width: 100%;
    }
    /* Jadikan #page-content-wrapper sebagai flex container untuk memusatkan isinya */
    #page-content-wrapper {
        flex-grow: 1; /* Memastikan ia mengisi sisa ruang di #wrapper */
        display: flex;
        align-items: center; /* Pemusatan vertikal */
        justify-content: center; /* Pemusatan horizontal */
        width: 100% !important; /* Override style lain jika ada */
        padding: 0 !important; /* Hapus padding bawaan jika mengganggu pemusatan */
        margin: 0 !important; /* Hapus margin bawaan jika mengganggu pemusatan */
    }
    /* .login-container sekarang akan dipusatkan oleh #page-content-wrapper (melalui div.container-fluid) */
    /* Anda bisa biarkan style .login-container yang lama jika hanya untuk membungkus .login-card */
    .login-container {
        /* min-height: 80vh; Dihapus atau disesuaikan, karena pemusatan utama oleh parent */
        /* display: flex; align-items: center; justify-content: center; Dihapus atau disesuaikan */
        width: 100%; /* Agar .login-card bisa diatur max-width-nya */
        display: flex; /* Tetap gunakan flex untuk memusatkan .login-card di dalamnya */
        align-items: center;
        justify-content: center;
    }
    .login-card {
        width: 100%;
        max-width: 400px;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); /* Efek shadow standar */
    }
</style>
@endpush

@section('content')
<div class="container login-container">
    <div class="card shadow-sm login-card">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Login Aplikasi</h3>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Ingat Saya
                    </label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        Login
                    </button>
                </div>

                <div class="text-center mt-3">
                    Belum punya akun? <a href="{{ route('register') }}">Register di sini</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Sembunyikan navbar dan sidebar di halaman login
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar-wrapper');
        const navbar = document.querySelector('#page-content-wrapper nav.navbar'); // Lebih spesifik selector navbar
        const pageContentWrapper = document.getElementById('page-content-wrapper');
        const sidebarToggleButton = document.getElementById('sidebarToggle');

        if (sidebar) sidebar.style.display = 'none';
        if (navbar) navbar.style.display = 'none';
        if (sidebarToggleButton) sidebarToggleButton.style.display = 'none';

        // Sesuaikan page-content-wrapper agar full width tanpa sidebar
        if (pageContentWrapper) {
            pageContentWrapper.style.marginLeft = '0';
            pageContentWrapper.style.paddingLeft = '0'; // Hapus padding jika ada dari master
        }
        // Hapus toggle class dari wrapper jika ada
        const wrapper = document.getElementById('wrapper');
        if(wrapper) {
            wrapper.classList.remove('toggled');
        }
    });
</script>
@endpush