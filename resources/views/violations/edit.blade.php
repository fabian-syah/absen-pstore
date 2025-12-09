@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Data Pelanggaran</h4>

                <form action="{{ route('violations.update', $violation->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label>Nama Karyawan</label>
                        <input type="text" class="form-control" value="{{ $violation->user->name }}" disabled>
                        <small class="text-muted">User tidak dapat diubah saat edit.</small>
                    </div>

                    <div class="form-group">
                        <label for="category">Kategori Pelanggaran</label>
                        <select class="form-control" name="category" required>
                            <option value="berat" {{ $violation->category == 'berat' ? 'selected' : '' }}>BERAT</option>
                            <option value="sedang" {{ $violation->category == 'sedang' ? 'selected' : '' }}>SEDANG</option>
                            <option value="ringan" {{ $violation->category == 'ringan' ? 'selected' : '' }}>RINGAN</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Judul Pelanggaran</label>
                        <input type="text" class="form-control" name="title" value="{{ $violation->title }}" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi Lengkap</label>
                        <textarea class="form-control" name="description" rows="4" required>{{ $violation->description }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes">Keterangan Tambahan</label>
                        <input type="text" class="form-control" name="notes" value="{{ $violation->notes }}">
                    </div>

                    <div class="form-group">
                        <label>Ganti Bukti Foto (Opsional)</label>
                        <input type="file" name="photo" class="form-control file-upload-info">
                        @if($violation->photo_path)
                            <div class="mt-2">
                                <small>Foto saat ini:</small><br>
                                <img src="{{ asset('storage/' . $violation->photo_path) }}" width="100" class="rounded">
                            </div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary mr-2">Update</button>
                    <a href="{{ route('violations.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection