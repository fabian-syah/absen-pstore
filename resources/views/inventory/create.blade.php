@extends('layout.master')

@section('title', 'Tambah Inventaris')

@section('content')
    {{-- Load CSS Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Tambah Data Inventaris</h4>
                    <p class="card-description">
                        @if ($fixedUser)
                            Masukkan data aset yang Anda terima/gunakan.
                        @else
                            Masukkan data aset untuk karyawan.
                        @endif
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('inventory.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        {{-- Hidden Input jika akses dari Menu Cabang --}}
                        @if (isset($targetBranchId))
                            <input type="hidden" name="target_branch_id" value="{{ $targetBranchId }}">
                        @endif

                        <div class="form-group">
                            <label>Nama barang ( Merk / series / tipe / RAM / GB )<span class="text-danger">*</span></label>
                            <input type="text" name="item_name" class="form-control" required
                                placeholder="Contoh: Laptop Asus ROG" value="{{ old('item_name') }}">
                        </div>

                        {{-- LOGIKA PENANGGUNG JAWAB --}}
                        <div class="form-group">
                            <label>Penanggung Jawab (User) <span class="text-danger">*</span></label>

                            @if ($fixedUser)
                                {{-- KASUS 1: Menu Utama (Role Non-Admin) -> Readonly Diri Sendiri --}}
                                <input type="text" class="form-control bg-light"
                                    value="{{ $fixedUser->name }} - {{ $fixedUser->branch->name ?? 'Non-Cabang' }}"
                                    readonly>
                                <input type="hidden" name="user_id" value="{{ $fixedUser->id }}">
                                <small class="text-success"><i class="mdi mdi-check-circle"></i> Barang ini akan tercatat
                                    sebagai milik Anda.</small>
                            @else
                                {{-- KASUS 2: Admin ATAU Audit/Leader dari Menu Cabang -> Dropdown Pilih User --}}
                                <select name="user_id" class="form-control select2-single" required>
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} - {{ $u->branch->name ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (isset($targetBranchId))
                                    <small class="text-info">*Menampilkan karyawan di cabang ini saja.</small>
                                @endif
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Kategori <span class="text-danger">*</span></label>
                                <select name="category" class="form-control" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Handphone">Handphone</option>
                                    <option value="iPad / Tab">iPad / Tab</option>
                                    <option value="Laptop">Laptop</option>
                                    <option value="Motor / Sepeda">Motor / Sepeda</option>
                                    <option value="Mobil">Mobil</option>
                                    <option value="Kamera / Lensa">Kamera / Lensa</option>
                                    <option value="Accesories">Accesories</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Kondisi Saat Ini <span class="text-danger">*</span></label>
                                <select name="condition" class="form-control" required>
                                    <option value="Baru">Baru</option>
                                    <option value="second">Second</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nomor seri ( IMEI, serial number, plat nomer )</label>
                                <input type="text" name="serial_number" class="form-control" placeholder="Opsional"
                                    value="{{ old('serial_number') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Tanggal Diterima <span class="text-danger">*</span></label>
                                <input type="date" name="received_date" class="form-control" required
                                    value="{{ old('received_date', date('Y-m-d')) }}">
                            </div>
                        </div>

                        {{-- UPDATE: DUA KOLOM UPLOAD FOTO --}}
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Foto 1: Fisik Barang <span class="text-danger">*</span></label>
                                <input type="file" name="item_photo" class="form-control" accept="image/*" required>
                                <small class="text-muted">Foto detail barangnya saja.</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Foto 2: Foto Diri + Barang <span class="text-danger">*</span></label>
                                <input type="file" name="user_item_photo" class="form-control" accept="image/*" required>
                                <small class="text-muted">Foto selfie/dipegang user sebagai bukti serah terima.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Dokumen (Faktur/Garansi)</label>
                            <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx">
                        </div>

                        <div class="form-group">
                            <label>Keterangan Tambahan</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Simpan Data</button>
                        <a href="{{ url()->previous() }}" class="btn btn-light">Batal</a>
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
