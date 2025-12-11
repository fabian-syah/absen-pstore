@extends('layout.master')

@section('title', 'Riwayat Karir & Mutasi')

@push('plugin-styles')
    {{-- Pastikan Select2 CSS sudah di-load di layout master atau di sini --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="row">
    
    {{-- ==================================================== --}}
    {{-- 1. BAGIAN FILTER USER (HANYA ADMIN & AUDIT)          --}}
    {{-- ==================================================== --}}
    @if(in_array(auth()->user()->role, ['admin', 'audit']))
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Kelola Riwayat Pegawai</h4>
                
                <form action="{{ route('employment-history.index') }}" method="GET" class="d-flex align-items-center w-50 justify-content-end">
                    <label class="mr-3 mb-0 text-muted">Pilih Pegawai:</label>
                    <select name="user_id" class="form-control w-75 select2-single" onchange="this.form.submit()">
                        <option value="{{ auth()->user()->id }}">-- Saya Sendiri --</option>
                        @foreach($selectableUsers as $u)
                            <option value="{{ $u->id }}" {{ isset($targetUser) && $targetUser->id == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ ucfirst($u->role) }}) - {{ $u->branch->name ?? 'Pusat' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ==================================================== --}}
    {{-- 2. FORM INPUT (CREATE)                               --}}
    {{-- ==================================================== --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Tambah Riwayat</h4>
                    @if($targetUser->role == 'audit')
                        <span class="badge badge-warning text-white">Target: AUDIT (Multi-Branch)</span>
                    @else
                        <span class="badge badge-info text-white">Target: {{ ucfirst($targetUser->role) }}</span>
                    @endif
                </div>
                <p class="card-description">Menambahkan data untuk: <strong>{{ $targetUser->name }}</strong></p>

                {{-- Tampilkan Alert Error/Success --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('employment-history.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Hidden Input ID User yang sedang diedit --}}
                    <input type="hidden" name="user_id" value="{{ $targetUser->id }}">

                    {{-- TYPE SELECTION --}}
                    <div class="form-group">
                        <label>Jenis Mutasi / Kejadian <span class="text-danger">*</span></label>
                        <select name="type" id="typeSelect" class="form-control" required onchange="handleTypeChange()">
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <option value="join">Awal Masuk Pstore</option>
                            <option value="transfer_branch">Pindah Cabang</option>
                            <option value="transfer_division">Pindah Divisi / Jabatan</option>
                            <option value="resign">Resign / Dirumahkan</option>
                            <option value="rejoin">Masuk Pstore Lagi</option>
                        </select>
                    </div>

                    {{-- DATE SELECTION --}}
                    <div class="form-group">
                        <label>Tanggal Efektif <span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>

                    {{-- LOGIKA TAMPILAN CABANG --}}
                    
                    {{-- A. JIKA TARGET ADALAH AUDIT (MULTI SELECT) --}}
                    @if($targetUser->role == 'audit')
                        <div class="form-group" id="auditBranchContainer" style="display:none;">
                            <label>Wilayah Audit Baru (Multi Select) <span class="text-danger">*</span></label>
                            <select name="audit_branch_ids[]" class="form-control js-example-basic-multiple" multiple="multiple" style="width:100%">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" 
                                        {{ in_array($branch->id, $targetUser->branches->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Cabang yang tidak dipilih akan dihapus dari akses audit user ini.</small>
                        </div>
                    @else 
                    {{-- B. JIKA TARGET USER BIASA (SINGLE SELECT) --}}
                        <div class="form-group" id="singleBranchContainer" style="display:none;">
                            <label>Cabang Tujuan <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branchInput" class="form-control">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Cabang Saat Ini: <strong>{{ $targetUser->branch->name ?? 'Belum Ada' }}</strong> (Akan tersimpan sebagai history cabang lama).
                            </small>
                        </div>
                    @endif

                    {{-- DIVISION SELECTION --}}
                    <div class="form-group" id="divisionContainer">
                        <label>Divisi / Jabatan Tujuan</label>
                        <select name="division_id" class="form-control">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="form-group">
                        <label>Keterangan / Alasan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Contoh: Promosi, Rotasi Rutin, dll"></textarea>
                    </div>

                    {{-- ATTACHMENT --}}
                    <div class="form-group">
                        <label>Lampiran (SK / Surat) - Opsional</label>
                        <input type="file" name="attachment" class="form-control-file">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block me-2">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ==================================================== --}}
    {{-- 3. TIMELINE LIST (READ/SHOW)                         --}}
    {{-- ==================================================== --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Timeline Karir: {{ $targetUser->name }}</h4>
                <p class="card-description">Urutan kejadian dari terbaru ke terlama.</p>

                @if($histories->isEmpty())
                    <div class="text-center py-5">
                        <i class="mdi mdi-history text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Belum ada riwayat karir tercatat.</p>
                    </div>
                @else
                    <ul class="bullet-line-list">
                        @foreach($histories as $history)
                            <li>
                                {{-- HEADER: TIPE & TANGGAL --}}
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="text-{{ $history->type_color }} font-weight-bold mb-1">
                                            {{ $history->type_label }}
                                        </h6>
                                        <p class="text-muted small mb-0">
                                            <i class="mdi mdi-calendar-clock"></i> 
                                            {{ \Carbon\Carbon::parse($history->event_date)->translatedFormat('d F Y') }}
                                        </p>
                                    </div>
                                    
                                    {{-- TOMBOL HAPUS (ADMIN/AUDIT ONLY) --}}
                                    @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                        <form action="{{ route('employment-history.destroy', $history->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus history ini? Data user saat ini TIDAK akan kembali otomatis ke status lama.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-inverse-danger btn-sm p-2" data-toggle="tooltip" title="Hapus Log">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                
                                {{-- BODY: DETAIL PERUBAHAN --}}
                                <div class="p-3 bg-light rounded mt-3 border-left-{{ $history->type_color }}">
                                    
                                    {{-- KONDISI 1: AUDIT (MULTI BRANCH) --}}
                                    @if($targetUser->role == 'audit' && $history->type == 'transfer_branch')
                                        <div class="mb-2">
                                            <strong class="d-block text-dark small mb-1">Perubahan Wilayah Audit:</strong>
                                            <div class="d-flex flex-column pl-2 border-left">
                                                <span class="text-muted small">
                                                    {!! $history->audit_change_text !!} 
                                                    {{-- output dari Accessor di Model --}}
                                                </span>
                                            </div>
                                        </div>

                                    {{-- KONDISI 2: USER BIASA (PINDAH CABANG) --}}
                                    @elseif($history->type == 'transfer_branch')
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-md-5">
                                                <small class="text-muted d-block">Dari Cabang:</small>
                                                <span class="text-danger font-weight-bold" style="text-decoration: line-through;">
                                                    {{ $history->previousBranch->name ?? 'Tidak Diketahui' }}
                                                </span>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <i class="mdi mdi-arrow-right-bold text-muted"></i>
                                            </div>
                                            <div class="col-md-5">
                                                <small class="text-muted d-block">Ke Cabang:</small>
                                                <span class="text-success font-weight-bold">
                                                    {{ $history->branch->name ?? '-' }}
                                                </span>
                                            </div>
                                        </div>

                                    {{-- KONDISI 3: PINDAH DIVISI / LAINNYA --}}
                                    @elseif($history->type != 'resign')
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Cabang:</small><br>
                                                <strong class="text-dark">{{ $history->branch->name ?? '-' }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Divisi/Jabatan:</small><br>
                                                <strong class="text-dark">{{ $history->division->name ?? '-' }}</strong>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- KETERANGAN & LAMPIRAN --}}
                                    @if($history->description)
                                        <hr class="my-2">
                                        <p class="mb-0 small text-muted">"{{ $history->description }}"</p>
                                    @endif

                                    @if($history->attachment)
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/'.$history->attachment) }}" target="_blank" class="badge badge-outline-primary">
                                                <i class="mdi mdi-paperclip"></i> Lihat Lampiran
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('plugin-scripts')
    {{-- Load JS Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@push('custom-scripts')
<script>
    // Inisialisasi Select2
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2({
            placeholder: "Pilih Cabang (Bisa Lebih dari 1)",
            allowClear: true
        });
        
        $('.select2-single').select2();
        
        // Jalankan fungsi saat halaman load (untuk handling old input jika validation fail)
        handleTypeChange();
    });

    function handleTypeChange() {
        const type = document.getElementById('typeSelect').value;
        
        // Element Containers
        const auditContainer = document.getElementById('auditBranchContainer');
        const singleContainer = document.getElementById('singleBranchContainer');
        const divContainer = document.getElementById('divisionContainer');
        
        // Cek Role Target User (Dari Blade ke JS String)
        const isAudit = "{{ $targetUser->role == 'audit' }}"; 

        // 1. Reset: Sembunyikan Input Cabang & Tampilkan Divisi (Default)
        if(auditContainer) auditContainer.style.display = 'none';
        if(singleContainer) singleContainer.style.display = 'none';
        divContainer.style.display = 'block';

        // 2. Logika Cabang (Join / Pindah Cabang / Masuk Lagi)
        if (type === 'transfer_branch' || type === 'join' || type === 'rejoin') {
            if (isAudit) {
                // Jika Audit: Tampilkan Multi Select
                if(auditContainer) auditContainer.style.display = 'block';
            } else {
                // Jika User Biasa: Tampilkan Single Select
                if(singleContainer) singleContainer.style.display = 'block';
            }
        } 
        
        // 3. Logika Resign (Sembunyikan Semua)
        else if (type === 'resign') {
            divContainer.style.display = 'none';
        }
        
        // 4. Logika Pindah Divisi
        else if (type === 'transfer_division') {
            // Cabang hidden (karena tidak pindah cabang)
            // Divisi visible (default)
        }
    }
</script>
@endpush