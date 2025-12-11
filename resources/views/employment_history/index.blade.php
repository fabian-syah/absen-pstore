@extends('layout.master')

@section('title', 'Riwayat Karir & Mutasi')

@section('content')
{{-- CSS SELECT2 CUSTOM (Sesuai User Create) --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
    .select2-container ul, .select2-container li, .select2-selection__rendered, span.select2-selection__choice {
        list-style: none !important; padding-left: 0 !important; margin-left: 0 !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple {
        background-color: #fff !important; border: 1px solid #ced4da !important;
        padding: 4px !important; display: flex !important; flex-wrap: wrap !important; align-items: center !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #e9ecef !important; border-radius: 20px !important; padding: 2px 10px !important;
        margin: 2px 4px !important; font-size: 0.85rem !important; color: #333 !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove {
        border: none !important; background: transparent !important; margin-right: 5px !important; color: #999 !important;
    }
</style>

<div class="row">
    
    {{-- ==================================================== --}}
    {{-- 1. BAGIAN FILTER USER (ADMIN & AUDIT)                --}}
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
                                {{ $u->name }} ({{ ucfirst($u->role) }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ==================================================== --}}
    {{-- 2. FORM INPUT                                        --}}
    {{-- ==================================================== --}}
    <div class="col-md-5 grid-margin stretch-card">
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

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('employment-history.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $targetUser->id }}">

                    {{-- TYPE SELECTION --}}
                    <div class="form-group mb-3">
                        <label>Jenis Mutasi / Kejadian <span class="text-danger">*</span></label>
                        <select name="type" id="typeSelect" class="form-select select2-single" required onchange="handleTypeChange()">
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <option value="join">Awal Masuk Pstore</option>
                            <option value="transfer_branch">Pindah Cabang</option>
                            <option value="transfer_division">Pindah Divisi / Jabatan</option>
                            <option value="resign">Resign / Dirumahkan</option>
                            <option value="rejoin">Masuk Pstore Lagi</option>
                        </select>
                    </div>

                    {{-- DATE SELECTION --}}
                    <div class="form-group mb-3">
                        <label>Tanggal Efektif <span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>

                    {{-- =============================================== --}}
                    {{-- A. LOGIKA FORM KHUSUS AUDIT (MULTI BRANCH)      --}}
                    {{-- =============================================== --}}
                    @if($targetUser->role == 'audit')
                        <div id="auditBranchContainer" class="d-none bg-light p-3 rounded mb-3 border">
                            <h6 class="text-primary mb-3">Mutasi Wilayah Audit</h6>
                            
                            {{-- 1. CABANG SEBELUMNYA (READONLY) --}}
                            <div class="form-group mb-3">
                                <label class="text-muted small">Cabang Sebelumnya (Saat Ini)</label>
                                <select class="form-select select2-multi" multiple="multiple" disabled>
                                    @foreach($targetUser->branches as $currentBranch)
                                        <option selected>{{ $currentBranch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 2. CABANG BARU (INPUT) --}}
                            <div class="form-group mb-3">
                                <label class="fw-bold">Cabang Baru (Tujuan) <span class="text-danger">*</span></label>
                                <select name="audit_branch_ids[]" class="form-select select2-multi" multiple="multiple" style="width:100%">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-2 text-right">
                                    <small class="text-muted"><a href="javascript:void(0)" onclick="$('.select2-multi').val(null).trigger('change')" class="text-danger">Reset Pilihan</a></small>
                                </div>
                            </div>
                        </div>

                    {{-- =============================================== --}}
                    {{-- B. LOGIKA FORM USER BIASA (SINGLE BRANCH)       --}}
                    {{-- =============================================== --}}
                    @else 
                        <div class="form-group mb-3 d-none" id="singleBranchContainer">
                            <label>Cabang Tujuan <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branchInput" class="form-select select2-single">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted mt-2">
                                Cabang Saat Ini: <strong>{{ $targetUser->branch->name ?? 'Belum Ada' }}</strong> (Akan tersimpan sebagai history).
                            </small>
                        </div>
                    @endif

                    {{-- DIVISION SELECTION --}}
                    <div class="form-group mb-3" id="divisionContainer">
                        <label>Divisi / Jabatan Tujuan</label>
                        <select name="division_id" class="form-select select2-single">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="form-group mb-3">
                        <label>Keterangan / Alasan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Contoh: Promosi, Rotasi Rutin, dll"></textarea>
                    </div>

                    {{-- ATTACHMENT --}}
                    <div class="form-group mb-3">
                        <label>Lampiran (Opsional)</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ==================================================== --}}
    {{-- 3. TIMELINE LIST                                     --}}
    {{-- ==================================================== --}}
    <div class="col-md-7 grid-margin stretch-card">
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
                            <li class="mb-4">
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
                                    
                                    @if(in_array(auth()->user()->role, ['admin', 'audit']))
                                        <form action="{{ route('employment-history.destroy', $history->id) }}" method="POST" onsubmit="return confirm('Hapus history ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-inverse-danger btn-sm p-1">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                
                                <div class="p-3 bg-light rounded mt-2 border-left-{{ $history->type_color }}" style="border-left: 4px solid;">
                                    
                                    {{-- DISPLAY AUDIT CHANGE --}}
                                    @if($targetUser->role == 'audit' && $history->type == 'transfer_branch')
                                        <div class="mb-2">
                                            <strong class="d-block text-dark small mb-1">Perubahan Wilayah Audit:</strong>
                                            <div class="d-flex flex-column pl-2 border-left">
                                                <span class="text-muted small">
                                                    {!! $history->audit_change_text !!} 
                                                </span>
                                            </div>
                                        </div>

                                    {{-- DISPLAY USER BIASA CHANGE --}}
                                    @elseif($history->type == 'transfer_branch')
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-md-5">
                                                <small class="text-muted d-block">Dari:</small>
                                                <span class="text-danger text-decoration-line-through">
                                                    {{ $history->previousBranch->name ?? '?' }}
                                                </span>
                                            </div>
                                            <div class="col-md-2 text-center"><i class="mdi mdi-arrow-right"></i></div>
                                            <div class="col-md-5">
                                                <small class="text-muted d-block">Ke:</small>
                                                <span class="text-success font-weight-bold">
                                                    {{ $history->branch->name ?? '-' }}
                                                </span>
                                            </div>
                                        </div>

                                    @elseif($history->type != 'resign')
                                        <small class="d-block">Cabang: <strong>{{ $history->branch->name ?? '-' }}</strong></small>
                                        <small class="d-block">Divisi: <strong>{{ $history->division->name ?? '-' }}</strong></small>
                                    @endif

                                    @if($history->description)
                                        <p class="mt-2 mb-0 small text-muted font-italic">"{{ $history->description }}"</p>
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

@push('scripts')
{{-- JS SELECT2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Init Single Select
        $('.select2-single').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "Silahkan pilih...",
            allowClear: true
        });

        // Init Multi Select
        $('.select2-multi').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "Pilih Cabang (Bisa lebih dari 1)...",
            closeOnSelect: false,
            allowClear: true
        });

        // Trigger logic tampilan saat load
        handleTypeChange();
    });

    function handleTypeChange() {
        const type = $('#typeSelect').val();
        
        // Containers
        const auditContainer = $('#auditBranchContainer');
        const singleContainer = $('#singleBranchContainer');
        const divContainer = $('#divisionContainer');
        
        // Cek Role Target
        const isAudit = "{{ $targetUser->role == 'audit' }}"; 

        // 1. Reset (Hide All Branch Inputs)
        auditContainer.addClass('d-none');
        singleContainer.addClass('d-none');
        divContainer.removeClass('d-none'); // Default Divisi muncul

        // 2. Logic Pindah Cabang / Join
        if (type === 'transfer_branch' || type === 'join' || type === 'rejoin') {
            if (isAudit) {
                // Tampilkan Form Khusus Audit (Previous & New Branch Multi)
                auditContainer.removeClass('d-none');
            } else {
                // Tampilkan Form Biasa (Single Branch)
                singleContainer.removeClass('d-none');
            }
        } 
        
        // 3. Logic Resign (Sembunyikan Divisi juga)
        else if (type === 'resign') {
            divContainer.addClass('d-none');
        }
    }
</script>
@endpush