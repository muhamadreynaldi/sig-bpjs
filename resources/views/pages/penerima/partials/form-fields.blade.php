{{-- NIK --}}
<div class="row mb-3">
    <label for="nik" class="col-md-3 col-form-label">NIK <span class="text-danger">*</span></label>
    <div class="col-md-9">
        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik', $penerima->nik ?? '') }}" required maxlength="16" placeholder="Masukkan 16 digit NIK">
        @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Nama Lengkap --}}
<div class="row mb-3">
    <label for="nama" class="col-md-3 col-form-label">Nama Lengkap <span class="text-danger">*</span></label>
    <div class="col-md-9">
        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $penerima->nama ?? '') }}" required placeholder="Masukkan nama sesuai KTP">
        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Alamat --}}
<div class="row mb-3">
    <label for="alamat" class="col-md-3 col-form-label">Alamat</label>
    <div class="col-md-9">
        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat', $penerima->alamat ?? '') }}</textarea>
        @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- RT/RW --}}
<div class="row mb-3">
    <label for="rt" class="col-md-3 col-form-label">RT / RW</label>
    <div class="col-md-3">
        <input type="text" class="form-control @error('rt') is-invalid @enderror" id="rt" name="rt" value="{{ old('rt', $penerima->rt ?? '') }}" placeholder="RT (contoh: 001)">
        @error('rt') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <input type="text" class="form-control @error('rw') is-invalid @enderror" id="rw" name="rw" value="{{ old('rw', $penerima->rw ?? '') }}" placeholder="RW (contoh: 001)">
        @error('rw') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Dusun --}}
<div class="row mb-3">
    <label for="dusun" class="col-md-3 col-form-label">Dusun <span class="text-danger">*</span></label>
    <div class="col-md-9">
        <select name="dusun" id="dusun" class="form-select @error('dusun') is-invalid @enderror" required>
            <option value="" disabled selected>-- Pilih Dusun --</option>
            @foreach ($dusunList as $dusun)
                <option value="{{ $dusun }}" {{ old('dusun', $penerima->dusun ?? '') == $dusun ? 'selected' : '' }}>
                    {{ $dusun }}
                </option>
            @endforeach
        </select>
         @error('dusun')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Jenis Kepesertaan --}}
<div class="row mb-3">
    <label for="jenis_kepesertaan" class="col-md-3 col-form-label">Jenis Kepesertaan <span class="text-danger">*</span></label>
    <div class="col-md-9">
        <select name="jenis_kepesertaan" id="jenis_kepesertaan" class="form-select @error('jenis_kepesertaan') is-invalid @enderror" required>
            <option value="" disabled selected>-- Pilih Jenis Kepesertaan --</option>
             @foreach($jenisKepesertaanList as $jenis)
                <option value="{{ $jenis }}" {{ old('jenis_kepesertaan', $penerima->jenis_kepesertaan ?? '') == $jenis ? 'selected' : '' }}>
                    {{ $jenis }}
                </option>
            @endforeach
        </select>
        @error('jenis_kepesertaan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

        <div class="form-group">
            <label>Status Kepesertaan</label>
             <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                <option value="">-- Pilih Status --</option>
                @foreach ($statusList as $status)
                    <option value="{{ $status }}" {{ old('status', $penerima->status ?? '') == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
             @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        {{-- SAMPAI SINI --}}

{{-- Informasi Bantuan Lainnya --}}
<div class="row mb-3">
    <label class="col-md-3 col-form-label">Bantuan Lainnya</label>
    <div class="col-md-9 pt-2">
         @foreach($bantuanLainnyaList as $bantuan)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="bantuan_lainnya[]" value="{{ $bantuan }}" id="{{ str_replace(' ', '', $bantuan) }}"
                @if(is_array(old('bantuan_lainnya', $penerima->bantuan_lainnya ?? [])) && in_array($bantuan, old('bantuan_lainnya', $penerima->bantuan_lainnya ?? []))) checked @endif >
                <label class="form-check-label" for="{{ str_replace(' ', '', $bantuan) }}">
                    {{ $bantuan }}
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="row mb-3">
    <label for="lat" class="col-md-2 col-form-label">Latitude <span class="text-danger">*</span></label>
    <div class="col-md-6">
        <input type="number" step="any" class="form-control @error('lat') is-invalid @enderror" id="lat" name="lat" value="{{ old('lat', $penerima->lat) }}" placeholder="Contoh: -0.02355" required readonly>
        @error('lat') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label for="lng" class="col-md-2 col-form-label">Longitude <span class="text-danger">*</span></label>
    <div class="col-md-6">
        <input type="number" step="any" class="form-control @error('lng') is-invalid @enderror" id="lng" name="lng" value="{{ old('lng', $penerima->lng) }}" placeholder="Contoh: 109.33215" required readonly>
        @error('lng') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-md-2 col-form-label">Pilih di Peta</label>
    <div class="col-md-6">
        <div id="mapInputCoordinate" style="height: 300px; width:100%; border-radius: 0.25rem; border: 1px solid #ced4da;"></div>
        <small class="form-text text-muted">Klik pada peta untuk mengisi Latitude dan Longitude di atas secara otomatis.</small>
    </div>
</div>
