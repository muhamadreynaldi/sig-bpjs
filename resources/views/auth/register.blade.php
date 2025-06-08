@extends('layouts.master')

@section('title', 'Registrasi Akun - SIG BPJS')

@push('styles')
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
    body {
        background-color: #f8f9fa;
    }
    #wrapper {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        width: 100%;
    }
    #page-content-wrapper {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .register-container {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .register-card {
        width: 100%;
        max-width: 450px;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
    }
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