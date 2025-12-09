@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Data Kasbon (Admin)</h4>
                
                <form action="{{ route('kasbon.update', $kasbon->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label>Nama Peminjam</label>
                        <input type="text" class="form-control" value="{{ $kasbon->user->name }}" disabled>
                    </div>

                    <div class="form-group">
                        <label>Total Uang</label>
                        <input type="number" name="amount" class="form-control" value="{{ $kasbon->amount }}" required>
                    </div>

                    <div class="form-group">
                        <label>Keterangan 1</label>
                        <input type="text" name="description_1" class="form-control" value="{{ $kasbon->description_1 }}" required>
                    </div>

                    <div class="form-group">
                        <label>Keterangan 2</label>
                        <input type="text" name="description_2" class="form-control" value="{{ $kasbon->description_2 }}">
                    </div>
                    
                    <div class="form-group">
                        <label>Ganti Foto 1 (Opsional)</label>
                        <input type="file" name="photo_1" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Ganti Foto 2 (Opsional)</label>
                        <input type="file" name="photo_2" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('kasbon.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection