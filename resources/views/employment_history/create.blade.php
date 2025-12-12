@extends('layout.master')

@section('title', 'Tambah Riwayat')

@section('content')
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
                </div>

                <form action="{{ route('employment-history.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $targetUser->id }}">

                    <div class="form-group mb-3">
                        <label>Jenis Kejadian <span class="text-danger">*</span></label>
                        <select name="type" id="typeSelect" class="form-select select2-single" required onchange="handleTypeChange()">
                            <option value="" disabled selected>-- Pilih Jenis --</option>
                            <option value="join">Awal Masuk Pstore</option>
                            <option value="transfer_branch">Pindah Cabang</option>
                            <option value="transfer_division">Pindah Divisi</option>
                            <option value="resign">Resign / Keluar</option>
                            <option value="rejoin">Masuk Kembali</option>
                            <option value="external" class="fw-bold">-- Pengalaman Di Luar Pstore --</option>
                        </select>
                    </div>

                    {{-- Input Judul Khusus External --}}
                    <div class="form-group mb-3 d-none" id="titleContainer">
                        <label>Judul / Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Staff IT di PT. Maju Mundur">
                    </div>

                    {{-- Container Tanggal (Akan disembunyikan jika External) --}}
                    <div class="form-group mb-3" id="dateContainer">
                        <label>Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group mb-3 d-none" id="singleBranchContainer">
                        <label>Cabang Tujuan</label>
                        <select name="branch_id" class="form-select select2-single">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

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
                        <label>Keterangan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi kejadian..."></textarea>
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
        handleTypeChange();
    });

    function handleTypeChange() {
        const type = $('#typeSelect').val();
        const singleContainer = $('#singleBranchContainer');
        const divContainer = $('#divisionContainer');
        const titleContainer = $('#titleContainer');
        const dateContainer = $('#dateContainer'); // ID container tanggal

        // Reset display default
        if(singleContainer.length) singleContainer.addClass('d-none');
        divContainer.removeClass('d-none');
        titleContainer.addClass('d-none');
        dateContainer.removeClass('d-none'); // Default tanggal muncul

        if (type === 'transfer_branch') {
            if(singleContainer.length) singleContainer.removeClass('d-none');
            divContainer.addClass('d-none'); 
            $('#divisionSelect').val(null).trigger('change');

        } else if (type === 'join' || type === 'rejoin') {
            if(singleContainer.length) singleContainer.removeClass('d-none');
            divContainer.removeClass('d-none');

        } else if (type === 'transfer_division') {
            divContainer.removeClass('d-none');

        } else if (type === 'resign') {
            divContainer.addClass('d-none');
        
        } else if (type === 'external') {
            // EXTERNAL: Tampilkan Judul, Sembunyikan Branch, Divisi, DAN Tanggal
            titleContainer.removeClass('d-none');
            divContainer.addClass('d-none');
            dateContainer.addClass('d-none'); // Sembunyikan Tanggal
            if(singleContainer.length) singleContainer.addClass('d-none');
        }
    }
</script>
@endpush