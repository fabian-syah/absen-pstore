@extends('layout.master')

@section('title', 'Tambah Riwayat')

@section('content')
{{-- Load CSS Select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<div class="row justify-content-center">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Formulir Riwayat Baru</h4>
                    <a href="{{ route('employment-history.index', ['user_id' => $targetUser->id]) }}" class="btn btn-light btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <div class="alert alert-info">
                    <i class="mdi mdi-information-outline me-1"></i>
                    Menambahkan catatan untuk: <strong>{{ $targetUser->name }}</strong>. 
                    <br>
                    <small>Data ini hanya akan masuk ke <strong>Timeline</strong> dan tidak mengubah data akun/login user secara otomatis.</small>
                </div>

                <form action="{{ route('employment-history.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $targetUser->id }}">

                    <div class="form-group mb-3">
                        <label>Jenis Kejadian <span class="text-danger">*</span></label>
                        <select name="type" id="typeSelect" class="form-select select2-single" required onchange="handleTypeChange()">
                            <option value="" disabled selected>-- Pilih Jenis --</option>
                            <option value="join">Awal Masuk</option>
                            <option value="transfer_branch">Pindah Cabang</option>
                            <option value="transfer_division">Pindah Divisi</option>
                            <option value="resign">Resign / Keluar</option>
                            <option value="rejoin">Masuk Kembali</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Tanggal Efektif <span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>

                    {{-- Form Dinamis Berdasarkan Role Target User --}}
                    @if($targetUser->role == 'audit')
                        {{-- Audit Multi Branch --}}
                        <div id="auditBranchContainer" class="d-none bg-light p-3 rounded mb-3 border">
                            <label class="fw-bold mb-2">Wilayah Audit Baru (Snapshot)</label>
                            <select name="audit_branch_ids[]" class="form-select select2-multi" multiple="multiple" style="width:100%">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else 
                        {{-- User Biasa Single Branch --}}
                        <div class="form-group mb-3 d-none" id="singleBranchContainer">
                            <label>Cabang Tujuan (Tercatat di History)</label>
                            <select name="branch_id" class="form-select select2-single">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-group mb-3" id="divisionContainer">
                        <label>Divisi / Jabatan (Tercatat di History)</label>
                        <select name="division_id" class="form-select select2-single">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Keterangan Tambahan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Contoh: SK Nomor 123..."></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label>Lampiran Dokumen</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Simpan Catatan</button>
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
        $('.select2-single').select2({ theme: "bootstrap-5", width: '100%' });
        $('.select2-multi').select2({ theme: "bootstrap-5", width: '100%', closeOnSelect: false });
        handleTypeChange();
    });

    function handleTypeChange() {
        const type = $('#typeSelect').val();
        const auditContainer = $('#auditBranchContainer');
        const singleContainer = $('#singleBranchContainer');
        const divContainer = $('#divisionContainer');
        const isAudit = "{{ $targetUser->role == 'audit' }}"; 

        // Reset semua ke hidden
        if(auditContainer.length) auditContainer.addClass('d-none');
        if(singleContainer.length) singleContainer.addClass('d-none');
        divContainer.removeClass('d-none');

        // Logic
        if (type === 'transfer_branch' || type === 'join' || type === 'rejoin') {
            if (isAudit) {
                if(auditContainer.length) auditContainer.removeClass('d-none');
            } else {
                if(singleContainer.length) singleContainer.removeClass('d-none');
            }
        } else if (type === 'resign') {
            divContainer.addClass('d-none');
        }
    }
</script>
@endpush