<div class="bg-dark border-end" id="sidebar-wrapper">
    <div class="sidebar-heading border-bottom bg-dark">Menu Navigasi</div>
    <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action list-group-item-light p-3 {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           href="{{ route('dashboard') }}">
           <i class="fas fa-search fa-fw me-2"></i>Dashboard
        </a>
        <a class="list-group-item list-group-item-action list-group-item-light p-3 {{ request()->routeIs('pemetaan.index') ? 'active' : '' }}"
           href="{{ route('pemetaan.index') }}">
           <i class="fas fa-map-marked-alt fa-fw me-2"></i>Pemetaan Penerima
        </a>
        <a class="list-group-item list-group-item-action list-group-item-light p-3 {{ request()->routeIs('rute.index') ? 'active' : '' }}"
           href="{{ route('rute.index') }}">
           <i class="fas fa-route fa-fw me-2"></i>Pencarian Rute
        </a>
        @auth {{-- Pastikan user sudah login --}}
            @if(Auth::user()->isAdmin()) {{-- Gunakan helper method jika sudah dibuat di model User --}}
            {{-- Atau: @if(Auth::user()->role == 'admin') --}}
            <a class="list-group-item list-group-item-action list-group-item-light p-3 {{ request()->routeIs('penerima.*') ? 'active' : '' }}"
               href="{{ route('penerima.index') }}">
               <i class="fas fa-users-cog fa-fw me-2"></i>Manajemen Penerima
            </a>
            @endif
        @endauth
        {{-- Tambahkan menu lain jika ada --}}
    </div>
    <div class="sidebar-footer mt-auto">
        <small>Designed by Muhamad Reynaldi</small>
    </div>
</div>