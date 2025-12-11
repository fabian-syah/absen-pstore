@extends('layout.master')

@section('title', 'Tambah Riwayat')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

{{-- Custom Style Select2 --}}
<style>
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #e9ecef !important; color: #333 !important;
    }
</style>

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
                </div>

                <form action="{{ route('employment-history.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $targetUser->id }}">

                    <div class="form-group mb-3">
                        <label>Jenis Kejadian <span class="text-danger">*</span></label>
                        <select name="type" id="typeSelect" class="form-select select2-single" required onchange="handleTypeChange()">
                            <option value="" disabled selected>-- Pilih Jenis --</option>
                            <option value="join">Awal Masuk</option>
                            <option value="transfer_branch">Pindah Cabang (Divisi Dihilangkan)</option>
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
                        <div class="form-group mb-3 d-none" id="auditBranchContainer">
                            <label>Wilayah Audit Baru (Multi Select)</label>
                            <select name="audit_branch_ids[]" class="form-select select2-multi" multiple="multiple" style="width:100%">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else 
                        {{-- User Biasa Single Branch --}}
                        <div class="form-group mb-3 d-none" id="singleBranchContainer">
                            <label>Cabang Tujuan</label>
                            <select name="branch_id" class="form-select select2-single">
                                <option value="">-- Pilih Cabang --</option>
                                {{-- Menampilkan SEMUA cabang agar Leader/Admin bisa memindahkan user kemana saja --}}
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-group mb-3" id="divisionContainer">
                        <label>Divisi / Jabatan</label>
                        <select name="division_id" id="divisionSelect" class="form-select select2-single">
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
        $('.select2-multi').select2({ theme: "bootstrap-5", width: '100%', closeOnSelect: false, placeholder: "Pilih Cabang..." });
        handleTypeChange();
    });

    function handleTypeChange() {
        const type = $('#typeSelect').val();
        const auditContainer = $('#auditBranchContainer');
        const singleContainer = $('#singleBranchContainer');
        const divContainer = $('#divisionContainer');
        const isAudit = "{{ $targetUser->role == 'audit' }}"; 

        if(auditContainer.length) auditContainer.addClass('d-none');
        if(singleContainer.length) singleContainer.addClass('d-none');
        divContainer.removeClass('d-none'); // Default muncul

        if (type === 'transfer_branch') {
            if (isAudit) {
                if(auditContainer.length) auditContainer.removeClass('d-none');
            } else {
                if(singleContainer.length) singleContainer.removeClass('d-none');
            }
            divContainer.addClass('d-none'); // Hilangkan divisi
            $('#divisionSelect').val(null).trigger('change');

        } else if (type === 'join' || type === 'rejoin') {
            if (isAudit) {
                if(auditContainer.length) auditContainer.removeClass('d-none');
            } else {
                if(singleContainer.length) singleContainer.removeClass('d-none');
            }
            divContainer.removeClass('d-none');

        } else if (type === 'transfer_division') {
            divContainer.removeClass('d-none');
        } else if (type === 'resign') {
            divContainer.addClass('d-none');
        }
    }
</script>
@endpush