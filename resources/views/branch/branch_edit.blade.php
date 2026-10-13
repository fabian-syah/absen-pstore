@extends('layout.master')

@section('title')
    Edit Cabang
@endsection

@section('heading')
    Edit Cabang: {{ $branch->name }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Form Edit Cabang</h4>
                        <span class="badge {{ $branch->is_active ? 'bg-success' : 'bg-danger' }}">
                            Current Status: {{ $branch->is_active ? 'BUKA' : 'TUTUP' }}
                        </span>
                    </div>

                    {{-- Error Validation --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('branches.update', $branch->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nama Cabang --}}
                        <div class="form-group mb-3">
                            <label for="name" class="fw-bold mb-1">Nama Cabang</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $branch->name) }}" placeholder="Contoh: Pstore Jakarta Selatan" required>
                        </div>

                        {{-- Zona Waktu (BARU) --}}
                        <div class="form-group mb-3">
                            <label for="timezone" class="fw-bold mb-1">Zona Waktu</label>
                            <select class="form-control form-select" id="timezone" name="timezone" required>
                                <option value="Asia/Jakarta" {{ $branch->timezone == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Jakarta)</option>
                                <option value="Asia/Makassar" {{ $branch->timezone == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Makassar/Bali)</option>
                                <option value="Asia/Jayapura" {{ $branch->timezone == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Jayapura)</option>
                            </select>
                        </div>

                        {{-- Kemenag City ID --}}
                        <div class="form-group mb-3">
                            <label for="kemenag_city_id" class="fw-bold mb-1">ID Kota Kemenag (Untuk Jadwal Shalat)</label>
                            <input type="text" class="form-control" id="kemenag_city_id" name="kemenag_city_id"
                                value="{{ old('kemenag_city_id', $branch->kemenag_city_id) }}" placeholder="Contoh: 1301 (Jakarta)">
                            <small class="form-text text-muted">ID Kota dapat dicari di API MyQuran. Kosongkan jika tidak perlu jadwal shalat spesifik cabang.</small>
                        </div>

                        {{-- Alamat Cabang --}}
                        <div class="form-group mb-3">
                            <label for="address" class="fw-bold mb-1">Alamat Cabang</label>
                            <textarea class="form-control" id="address" name="address" rows="3" 
                                placeholder="Masukkan alamat lengkap cabang">{{ old('address', $branch->address) }}</textarea>
                        </div>

                        {{-- GEOFENCING / LOKASI ABSENSI MANDIRI --}}
                        <div class="card bg-light border p-3 mb-4 rounded">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="mdi mdi-map-marker-radius me-1"></i> Pengaturan Geofencing & Radius Absensi
                            </h6>
                            <p class="text-muted small mb-3">
                                Tentukan titik koordinat GPS cabang dan batas radius toleransi absensi mandiri karyawan.
                            </p>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="latitude" class="form-label fw-bold small">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude"
                                        value="{{ old('latitude', $branch->latitude) }}" placeholder="Contoh: -6.1952300">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="longitude" class="form-label fw-bold small">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude"
                                        value="{{ old('longitude', $branch->longitude) }}" placeholder="Contoh: 106.7865200">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="radius" class="form-label fw-bold small">Radius Absensi (Meter)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="radius" name="radius" min="10" max="5000"
                                            value="{{ old('radius', $branch->radius ?? 100) }}" placeholder="100">
                                        <span class="input-group-text">Meter</span>
                                    </div>
                                    <small class="text-muted">Standar: 100 meter</small>
                                </div>
                            </div>
                            @if($branch->latitude && $branch->longitude)
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <a href="https://www.google.com/maps?q={{ $branch->latitude }},{{ $branch->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="mdi mdi-google-maps me-1"></i> Buka Titik di Google Maps
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- STATUS OPERASIONAL --}}
                        <div class="form-group mb-4">
                            <label for="is_active" class="fw-bold mb-1">Status Operasional</label>
                            <select class="form-control form-select" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $branch->is_active) == 1 ? 'selected' : '' }}>
                                    🟢 Buka (Aktif)
                                </option>
                                <option value="0" {{ old('is_active', $branch->is_active) == 0 ? 'selected' : '' }}>
                                    🔴 Tutup (Non-Aktif)
                                </option>
                            </select>
                            <small class="text-muted">
                                *Jika status diubah menjadi <strong>Tutup</strong>, cabang akan ditandai merah dan dicoret pada daftar utama.
                            </small>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="mdi mdi-content-save me-1"></i> Update Data
                            </button>
                            <a href="{{ route('branches.index') }}" class="btn btn-light px-4">Batal</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection