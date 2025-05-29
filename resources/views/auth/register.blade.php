@extends('layouts.master') {{-- Atau layout khusus auth jika ada --}}

@section('title', 'Registrasi Akun - SIG BPJS')

@push('styles')
<style>
    body { background-color: #f8f9fa; }
    .register-container { min-height: 80vh; display: flex; align-items: center; justify-content: center; }
    .register-card { width: 100%; max-width: 450px; padding: 25px; border-radius: 8px;}
</style>
@endpush

@section('content')
<div class="container register-container">
    <div class="card shadow-sm register-card">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Buat Akun Baru</h3>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">
                        Registrasi
                    </button>
                </div>

                <div class="text-center mt-3">
                    Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script untuk menyembunyikan navbar & sidebar di halaman registrasi, mirip halaman login --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar-wrapper');
        const navbar = document.querySelector('#page-content-wrapper nav.navbar');
        const pageContentWrapper = document.getElementById('page-content-wrapper');
        const sidebarToggleButton = document.getElementById('sidebarToggle');

        if (sidebar) sidebar.style.display = 'none';
        if (navbar) navbar.style.display = 'none';
        if (sidebarToggleButton) sidebarToggleButton.style.display = 'none';

        if (pageContentWrapper) {
            pageContentWrapper.style.marginLeft = '0';
            pageContentWrapper.style.paddingLeft = '0';
        }
        const wrapper = document.getElementById('wrapper');
        if(wrapper) wrapper.classList.remove('toggled');
    });
</script>
@endpush