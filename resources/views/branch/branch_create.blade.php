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
                            <label for="address">Alamat Cabang</label>
                            <textarea class="form-control" id="address" name="address"
                                rows="3">{{ old('address') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Simpan</button>
                        <a href="{{ route('branches.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection