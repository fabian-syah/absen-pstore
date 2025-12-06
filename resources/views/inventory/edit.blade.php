@extends('layout.master')

@section('title', 'Edit Inventaris')

@section('content')
{{-- Load CSS Select2 (Opsional, jika ingin dropdown user bisa dicari) --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Inventaris: {{ $inventory->item_name }}</h4>
                <p class="card-description">
                    Perbarui data aset atau ganti foto bukti.
                </p>

                {{-- Menampilkan Error Validasi --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form class="forms-sample" action="{{ route('inventory.update', $inventory->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label>Nama Barang / Aset <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required value="{{ old('item_name', $inventory->item_name) }}">
                    </div>

                    <div class="form-group">
                        <label>Penanggung Jawab (User) <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control select2-single" required>
                            <option value="">-- Pilih User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $inventory->user_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} - {{ $user->branch->name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach(['Handphone', 'iPad / Tab', 'Laptop', 'Motor / Sepeda', 'Mobil', 'Kamera / Lensa', 'Accesories', 'Lainnya'] as $cat)
                                    <option value="{{ $cat }}" {{ $inventory->category == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Kondisi <span class="text-danger">*</span></label>
                            <select name="condition" class="form-control" required>
                                <option value="Baru" {{ $inventory->condition == 'Baru' ? 'selected' : '' }}>Baru</option>
                                <option value="second" {{ $inventory->condition == 'second' ? 'selected' : '' }}>Second</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nomor Seri (SN/IMEI)</label>
                            <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $inventory->serial_number) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Diterima</label>
                            <input type="date" name="received_date" class="form-control" required value="{{ old('received_date', $inventory->received_date->format('Y-m-d')) }}">
                        </div>
                    </div>

                    {{-- UPDATE: AREA FOTO (2 KOLOM) --}}
                    <div class="row">
                        {{-- KOLOM 1: FOTO BARANG --}}
                        <div class="col-md-6 form-group">
                            <label>Foto 1: Fisik Barang</label>
                            <input type="file" name="item_photo" class="form-control" accept="image/*">
                            <small class="text-muted">Upload baru jika ingin mengganti.</small>
                            
                            @if($inventory->item_photo_path)
                                <div class="mt-2 p-2 border rounded bg-light">
                                    <small class="d-block text-muted mb-1">Foto Saat Ini:</small>
                                    <img src="{{ asset('storage/'.$inventory->item_photo_path) }}" 
                                         style="width: 100%; max-height: 200px; object-fit: contain; border-radius: 4px;">
                                </div>
                            @endif
                        </div>

                        {{-- KOLOM 2: FOTO DIRI + BARANG --}}
                        <div class="col-md-6 form-group">
                            <label>Foto 2: Foto Diri + Barang</label>
                            <input type="file" name="user_item_photo" class="form-control" accept="image/*">
                            <small class="text-muted">Upload baru jika ingin mengganti.</small>

                            @if($inventory->user_item_photo_path)
                                <div class="mt-2 p-2 border rounded bg-light">
                                    <small class="d-block text-muted mb-1">Foto Saat Ini:</small>
                                    <img src="{{ asset('storage/'.$inventory->user_item_photo_path) }}" 
                                         style="width: 100%; max-height: 200px; object-fit: contain; border-radius: 4px;">
                                </div>
                            @else
                                <div class="mt-2 p-2 border rounded bg-light text-center text-muted">
                                    <small>Belum ada foto diri.</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Update Dokumen</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx">
                        @if($inventory->document_path)
                            <div class="mt-2 text-success">
                                <i class="mdi mdi-check-circle"></i> Dokumen tersimpan. <a href="{{ asset('storage/'.$inventory->document_path) }}" target="_blank">Lihat</a>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if ($('.select2-single').length > 0) {
            $('.select2-single').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Cari Nama Karyawan...",
                allowClear: true
            });
        }
    });
</script>
@endpush