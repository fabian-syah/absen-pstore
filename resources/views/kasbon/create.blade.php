@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Form Pengajuan Kasbon</h4>
                
                <form action="{{ route('kasbon.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <label>Nama Peminjam</label>
                        <select name="user_id" class="form-control select2" required>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $u->id == auth()->id() ? 'selected' : '' }}>
                                    {{ $u->name }} - {{ $u->branch->name ?? 'No Branch' }}
                                </option>
                            @endforeach
                        </select>
                        @if(auth()->user()->role == 'user_biasa' || auth()->user()->role == 'security')
                            <small class="text-muted">Anda hanya bisa mengajukan untuk diri sendiri.</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Total Uang (Nominal)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="amount" class="form-control" placeholder="0" min="1000" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Keterangan 1 (Utama)</label>
                                <input type="text" name="description_1" class="form-control" placeholder="Contoh: Keperluan Berobat" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Keterangan 2 (Detail/Tambahan)</label>
                                <input type="text" name="description_2" class="form-control" placeholder="Rincian tambahan...">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foto Dokumen 1 (Wajib)</label>
                                <input type="file" name="photo_1" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foto Dokumen 2 (Opsional)</label>
                                <input type="file" name="photo_2" class="form-control">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Ajukan Kasbon</button>
                    <a href="{{ route('kasbon.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection