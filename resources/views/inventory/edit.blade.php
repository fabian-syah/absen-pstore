@extends('layout.master')

@section('title', 'Edit Inventaris')

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Inventaris: {{ $inventory->item_name }}</h4>
                
                <form class="forms-sample" action="{{ route('inventory.update', $inventory->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label>Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required value="{{ old('item_name', $inventory->item_name) }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-control" required>
                                @foreach(['Elektronik', 'Perkantoran', 'Kendaraan', 'Lainnya'] as $cat)
                                    <option value="{{ $cat }}" {{ strtolower($inventory->category) == strtolower($cat) ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Kondisi <span class="text-danger">*</span></label>
                            <select name="condition" class="form-control" required>
                                @foreach(['Baru', 'Baik', 'Rusak Ringan', 'Rusak Berat', 'Perbaikan'] as $cond)
                                    {{-- Mapping Value agar sesuai --}}
                                    @php 
                                        $dbVal = strtolower(str_replace(' ', '_', $cond)); // misal: rusak_ringan
                                        $currentVal = strtolower(str_replace(' ', '_', $inventory->condition));
                                        if($cond == 'Dalam Perbaikan') $dbVal = 'perbaikan';
                                    @endphp
                                    <option value="{{ $dbVal }}" {{ $currentVal == $dbVal ? 'selected' : '' }}>
                                        {{ $cond }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $inventory->serial_number) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Diterima</label>
                            <input type="date" name="received_date" class="form-control" required value="{{ old('received_date', $inventory->received_date) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Penanggung Jawab <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $inventory->user_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->branch->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Update Foto Barang</label>
                        <input type="file" name="item_photo" class="form-control" accept="image/*">
                        @if($inventory->item_photo_path)
                            <div class="mt-2">
                                <small class="text-muted">Foto Saat Ini:</small><br>
                                <img src="{{ asset('storage/'.$inventory->item_photo_path) }}" width="150" class="rounded mt-1 border">
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Update Dokumen</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx">
                         @if($inventory->document_path)
                            <div class="mt-2 text-success">
                                <i class="mdi mdi-check-circle"></i> Dokumen sudah ada. Upload baru untuk mengganti.
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $inventory->description) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Update Data</button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection