@extends('layout.master')

@section('title', 'Tambah Inventaris')

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Tambah Data Inventaris (Admin)</h4>
                <p class="card-description">Masukkan data aset baru untuk karyawan.</p>

                {{-- Tampilkan Error Validasi --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="forms-sample" action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <label>Nama Barang / Aset ( Merk / series / tipe / RAM / GB )<span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required placeholder="Contoh: Laptop Asus ROG / iPhone 13" value="{{ old('item_name') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Handphone" {{ old('category') == 'Handphone' ? 'selected' : '' }}>Handphone</option>
                                <option value="iPad / Tab" {{ old('category') == 'iPad / Tab' ? 'selected' : '' }}>iPad / Tab</option>
                                <option value="Laptop" {{ old('category') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                                <option value="Motor / Sepeda" {{ old('category') == 'Motor / Sepeda' ? 'selected' : '' }}>Motor / Sepeda</option>
                                <option value="Mobil" {{ old('category') == 'Mobil' ? 'selected' : '' }}>Mobil</option>
                                <option value="Kamera / Lensa" {{ old('category') == 'Kamera / Lensa' ? 'selected' : '' }}>Kamera / Lensa</option>
                                <option value="Accesories" {{ old('category') == 'Accesories' ? 'selected' : '' }}>Accesories</option>
                                <option value="Lainnya" {{ old('category') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Kondisi Saat Ini <span class="text-danger">*</span></label>
                            <select name="condition" class="form-control" required>
                                <option value="Baru" {{ old('condition') == 'Baru' ? 'selected' : '' }}>Baru</option>
                                <option value="Baik" {{ old('condition') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Rusak Ringan" {{ old('condition') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="Rusak Berat" {{ old('condition') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                                <option value="Perbaikan" {{ old('condition') == 'Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            {{-- Label diupdate sesuai request --}}
                            <label>Nomor Seri (IMEI, Serial Number, Plat Nomor)</label>
                            <input type="text" name="serial_number" class="form-control" placeholder="Kosongkan jika tidak ada" value="{{ old('serial_number') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Diterima <span class="text-danger">*</span></label>
                            <input type="date" name="received_date" class="form-control" required value="{{ old('received_date', date('Y-m-d')) }}">
                        </div>
                    </div>

                    {{-- DROPDOWN PILIH USER --}}
                    <div class="form-group">
                        <label>Penanggung Jawab (User) <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} - {{ $user->branch->name ?? 'Non-Cabang' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Foto Barang</label>
                        <input type="file" name="item_photo" class="form-control" accept="image/*">
                        <small class="text-muted">Format: JPG/PNG, Maksimal 5MB</small>
                    </div>

                    <div class="form-group">
                        <label>Dokumen (Faktur/Garansi)</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx">
                        <small class="text-muted">Format: PDF/DOC/DOCX, Maksimal 10MB</small>
                    </div>

                    <div class="form-group">
                        <label>Keterangan Tambahan</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Simpan Data</button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection