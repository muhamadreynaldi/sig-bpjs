<nav class="navbar navbar-expand-lg border-bottom fixed-top">
    <div class="container-fluid">
        <button class="btn btn-primary" id="sidebarToggle">☰ Menu</button>
        <a class="navbar-brand ms-3" href="{{ Auth::check() ? route('dashboard') : url('/') }}">Aplikasi Pemetaan BPJS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                @guest
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                            @else
                            @endif
                            {{ Auth::user()->name }}
                            <span class="badge bg-secondary ms-2 rounded-pill" style="font-size: 0.7em;">{{ ucfirst(Auth::user()->role) }}</span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-cog fa-fw me-2"></i>Profil Saya
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt fa-fw me-2"></i>Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>