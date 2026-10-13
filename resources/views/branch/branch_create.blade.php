@extends('layout.master')

@section('title')
    Tambah Cabang
@endsection

@section('heading')
    Tambah Cabang Baru
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form Cabang Baru</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('branches.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nama Cabang</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Contoh: PStore Condet" value="{{ old('name') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="timezone">Zona Waktu</label>
                            <select class="form-control form-select" id="timezone" name="timezone" required>
                                <option value="Asia/Jakarta">WIB (Jakarta/Jawa/Sumatera/Kalbar)</option>
                                <option value="Asia/Makassar">WITA (Bali/Sulawesi/NTB/NTT/Kaltim/Kalsel)</option>
                                <option value="Asia/Jayapura">WIT (Papua/Maluku)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="kemenag_city_id">ID Kota Kemenag (Untuk Jadwal Shalat)</label>
                            <input type="text" class="form-control" id="kemenag_city_id" name="kemenag_city_id"
                                placeholder="Contoh: 1301 (Jakarta)" value="{{ old('kemenag_city_id') }}">
                            <small class="form-text text-muted">ID Kota dapat dicari di API MyQuran. Kosongkan jika tidak perlu jadwal shalat spesifik cabang.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="address">Alamat Cabang</label>
                            <textarea class="form-control" id="address" name="address"
                                rows="3">{{ old('address') }}</textarea>
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
                                        value="{{ old('latitude') }}" placeholder="Contoh: -6.1952300">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="longitude" class="form-label fw-bold small">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude"
                                        value="{{ old('longitude') }}" placeholder="Contoh: 106.7865200">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="radius" class="form-label fw-bold small">Radius Absensi (Meter)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="radius" name="radius" min="10" max="5000"
                                            value="{{ old('radius', 100) }}" placeholder="100">
                                        <span class="input-group-text">Meter</span>
                                    </div>
                                    <small class="text-muted">Standar: 100 meter</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Simpan</button>
                        <a href="{{ route('branches.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection