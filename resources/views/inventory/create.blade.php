@extends('layout.master')

@section('title', 'Tambah Inventaris')

@section('content')
{{-- Load CSS Select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Tambah Data Inventaris</h4>
                <p class="card-description">
                    @if(in_array(auth()->user()->role, ['admin', 'audit']))
                        Masukkan data aset untuk karyawan.
                    @else
                        Masukkan data aset yang Anda terima/gunakan.
                    @endif
                </p>

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

                    {{-- LOGIKA PENANGGUNG JAWAB --}}
                    <div class="form-group">
                        <label>Penanggung Jawab (User) <span class="text-danger">*</span></label>
                        
                        @if(in_array(auth()->user()->role, ['admin', 'audit']))
                            {{-- ADMIN & AUDIT: BISA PILIH USER (Sesuai Logic Controller) --}}
                            <select name="user_id" class="form-control select2-single" required>
                                <option value="">-- Cari Nama Karyawan --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} - {{ $user->branch->name ?? 'Non-Cabang' }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->role == 'audit')
                                <small class="text-muted">*Menampilkan karyawan di cabang yang Anda pegang.</small>
                            @endif
                        @else
                            {{-- USER BIASA / LEADER / SECURITY: OTOMATIS DIRI SENDIRI --}}
                            <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }} - {{ auth()->user()->branch->name ?? 'Non-Cabang' }}" readonly>
                            
                            {{-- Kirim ID via Hidden Input (Meski Controller akan memaksa pakai Auth::id() demi keamanan) --}}
                            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                            <small class="text-success"><i class="mdi mdi-check-circle"></i> Barang ini akan tercatat sebagai milik Anda.</small>
                        @endif
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
                            <label>Nomor Seri (IMEI, Serial Number, Plat Nomor)</label>
                            <input type="text" name="serial_number" class="form-control" placeholder="Kosongkan jika tidak ada" value="{{ old('serial_number') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Diterima <span class="text-danger">*</span></label>
                            <input type="date" name="received_date" class="form-control" required value="{{ old('received_date', date('Y-m-d')) }}">
                        </div>
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

@push('scripts')
{{-- Load JS Select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Aktifkan Select2 hanya jika elemennya ada (untuk Admin/Audit)
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