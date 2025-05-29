@extends('layouts.master')

@section('title', 'Data Penerima BPJS - SIG BPJS')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>Data Penerima BPJS
            </h5>
            <a href="{{ route('penerima.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus-circle me-1"></i> Tambah Penerima
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="GET" action="{{ route('penerima.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan Nama, NIK, atau Dusun..." value="{{ $search ?? '' }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    @if($search)
                    <a href="{{ route('penerima.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Dusun</th>
                            <th>Status BPJS</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penerimas as $index => $penerima)
                        <tr>
                            <td>{{ $penerimas->firstItem() + $index }}</td>
                            <td>{{ $penerima->nik }}</td>
                            <td>{{ $penerima->nama }}</td>
                            <td>{{ Str::limit($penerima->alamat, 30) }}</td>
                            <td>{{ $penerima->dusun }}</td>
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
                            <td>{{ $penerima->lat }}</td>
                            <td>{{ $penerima->lng }}</td>
                            <td>
                                    <a href="{{ route('penerima.show', $penerima->id) }}" class="btn btn-info btn-sm me-1" title="Detail">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>
                                    <a href="{{ route('penerima.edit', $penerima->id) }}" class="btn btn-primary btn-sm me-1" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('penerima.destroy', $penerima->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data penerima.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $penerimas->appends(['search' => $search])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (confirm('Apakah Anda yakin ingin menghapus data penerima ini? Tindakan ini tidak dapat dibatalkan.')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endpush