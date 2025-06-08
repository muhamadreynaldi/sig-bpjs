<div class="row mb-3">
    <label for="nik" class="col-md-2 col-form-label">NIK <span class="text-danger">*</span></label>
    <div class="col-md-6">
        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik', $penerima->nik) }}" required maxlength="16">
        @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label for="nama" class="col-md-2 col-form-label">Nama Lengkap <span class="text-danger">*</span></label>
    <div class="col-md-6">
        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $penerima->nama) }}" required>
        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label for="alamat" class="col-md-2 col-form-label">Alamat</label>
    <div class="col-md-6">
        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3">{{ old('alamat', $penerima->alamat) }}</textarea>
        @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label for="dusun" class="col-md-2 col-form-label">Dusun <span class="text-danger">*</span></label>
    <div class="col-md-6">
        <input type="text" class="form-control @error('dusun') is-invalid @enderror" id="dusun" name="dusun" value="{{ old('dusun', $penerima->dusun) }}" required>
        @error('dusun') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label for="status" class="col-md-2 col-form-label">Status BPJS <span class="text-danger">*</span></label>
    <div class="col-md-6">
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            <option value="">-- Pilih Status --</option>
            @foreach($statusOptions as $statusValue)
                <option value="{{ $statusValue }}" {{ old('status', $penerima->status) == $statusValue ? 'selected' : '' }}>
                    {{ $statusValue }}
                </option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
