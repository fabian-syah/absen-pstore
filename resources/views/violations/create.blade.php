@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Catat Pelanggaran Baru</h4>
                <p class="card-description">Form input pelanggaran karyawan</p>

                <form action="{{ route('violations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <label for="user_id">Nama Karyawan (Pelanggar)</label>
                        <select class="form-control select2" name="user_id" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->division->name ?? '-' }} ({{ $user->branch->name ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category">Kategori Pelanggaran</label>
                        <select class="form-control" name="category" required>
                            <option value="berat">BERAT (High Priority)</option>
                            <option value="sedang">SEDANG</option>
                            <option value="ringan">RINGAN</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Judul Pelanggaran</label>
                        <input type="text" class="form-control" name="title" placeholder="Contoh: Datang Terlambat > 1 Jam" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi Lengkap</label>
                        <textarea class="form-control" name="description" rows="4" placeholder="Jelaskan kronologi pelanggaran..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes">Keterangan Tambahan / Sanksi</label>
                        <input type="text" class="form-control" name="notes" placeholder="Contoh: Diberikan SP1">
                    </div>

                    <div class="form-group">
                        <label>Bukti Foto</label>
                        <input type="file" name="photo" class="form-control file-upload-info" required>
                        <small class="text-muted">Format: jpg, png, jpeg. Max 2MB.</small>
                    </div>

                    <button type="submit" class="btn btn-primary mr-2">Simpan</button>
                    <a href="{{ route('violations.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection