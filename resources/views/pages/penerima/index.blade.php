@extends('layouts.master')

@section('title', 'Data Penerima BPJS - SIG BPJS')

@section('content')


<div class="card">
    <div class="card-header">
        <h4>Kelola Data Penerima</h4>
    </div>
    <div class="card-body">
        <p>Gunakan tombol di bawah ini untuk mengunduh data dalam format Excel atau mengunggah data dari file Excel.</p>
        
        <a href="{{ route('penerimas.export') }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export ke Excel
        </a>

        <hr>

        <form action="{{ route('penerimas.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="file">Import File Excel</label>
                <input type="file" name="file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-file-upload"></i> Import Data
            </button>
        </form>
@if(session('import_success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('import_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    </div>
</div>

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
    @if(session('crud_success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('crud_success') }}
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
                    <thead>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>NIK</th>
        <th>Alamat</th>
        <th>RT/RW</th>
        <th>Dusun</th>
        <th>Jenis Kepesertaan</th>
        <th>Status</th>
        <th width="280px">Action</th>
    </tr>
</thead>

<tbody>
    @foreach ($penerimas as $index => $penerima)
        <tr>
            <td>{{ $penerimas->firstItem() + $index }}</td>
            <td>{{ $penerima->nama }}</td>
            <td>{{ $penerima->nik }}</td>
            <td>{{ $penerima->alamat }}</td>
            <td>{{ $penerima->rt }}/{{ $penerima->rw }}</td>
            <td>{{ $penerima->dusun }}</td>
            <td>{{ $penerima->jenis_kepesertaan }}</td>
            <td>
    @php
        $statusClass = 'bg-secondary'; // Warna abu-abu sebagai default
        if ($penerima->status == 'Terdaftar') {
            $statusClass = 'bg-success';
        } elseif ($penerima->status == 'Nonaktif') {
            $statusClass = 'bg-warning text-dark';
        } elseif ($penerima->status == 'Meninggal') {
            $statusClass = 'bg-dark';
        }
    @endphp
    <span class="badge {{ $statusClass }}">{{ $penerima->status ?? 'Tidak Diketahui' }}</span>
</td>
            <td>
                <form action="{{ route('penerima.destroy', $penerima->id) }}" method="POST">
                    <a class="btn btn-info" href="{{ route('penerima.show', $penerima->id) }}">Show</a>
                    <a class="btn btn-primary" href="{{ route('penerima.edit', $penerima->id) }}">Edit</a>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
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