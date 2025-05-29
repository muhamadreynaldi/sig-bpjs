@extends('layouts.master')

@section('title', 'Login - SIG BPJS')

@push('styles')
<style>
    body {
        background-color: #f8f9fa; /* Optional: a light background for login page */
    }
    .login-container {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card {
        width: 100%;
        max-width: 400px;
        padding: 25px;
        border-radius: 8px;
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
            </form>
            {{-- Jika Anda ingin menambahkan link "Forgot Password?" atau "Register" nanti --}}
            {{-- <div class="text-center mt-3">
                <a href="#">Lupa Password?</a> | <a href="#">Buat Akun Baru</a>
            </div> --}}
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