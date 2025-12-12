@extends('layout.master')

@section('title', 'Edit Riwayat')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

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
                    <h4 class="card-title mb-0">Edit Catatan Riwayat</h4>
                    <a href="{{ route('employment-history.index', ['user_id' => $history->user_id]) }}" class="btn btn-light btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Batal
                    </a>
                </div>

                <form action="{{ route('employment-history.update', $history->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="form-group mb-3">
                        <label>Jenis Kejadian</label>
                        <select name="type" id="typeSelect" class="form-select select2-single" required onchange="handleTypeChange()">
                            <option value="join" {{ $history->type == 'join' ? 'selected' : '' }}>Awal Masuk</option>
                            <option value="transfer_branch" {{ $history->type == 'transfer_branch' ? 'selected' : '' }}>Pindah Cabang (Divisi Dihilangkan)</option>
                            <option value="transfer_division" {{ $history->type == 'transfer_division' ? 'selected' : '' }}>Pindah Divisi</option>
                            <option value="resign" {{ $history->type == 'resign' ? 'selected' : '' }}>Resign</option>
                            <option value="rejoin" {{ $history->type == 'rejoin' ? 'selected' : '' }}>Masuk Kembali</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Tanggal Efektif</label>
                        <input type="date" name="event_date" class="form-control" required value="{{ $history->event_date->format('Y-m-d') }}">
                    </div>

                    {{-- Form Cabang (Unified) --}}
                    <div class="form-group mb-3 d-none" id="singleBranchContainer">
                        <label>Cabang</label>
                        <select name="branch_id" class="form-select select2-single">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $history->branch_id == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3" id="divisionContainer">
                        <label>Divisi</label>
                        <select name="division_id" id="divisionSelect" class="form-select select2-single">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ $history->division_id == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Keterangan</label>
                        <textarea name="description" class="form-control" rows="3">{{ $history->description }}</textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label>Update Lampiran</label>
                        <input type="file" name="attachment" class="form-control">
                        @if($history->attachment)
                            <small class="text-muted d-block mt-1">File saat ini: <a href="{{ asset('storage/'.$history->attachment) }}" target="_blank">Lihat</a></small>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-warning text-white w-100">Update Data</button>
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

        // Reset display
        if(singleContainer.length) singleContainer.addClass('d-none');
        divContainer.removeClass('d-none');

        if (type === 'transfer_branch') {
            // Pindah Cabang
            if(singleContainer.length) singleContainer.removeClass('d-none');
            divContainer.addClass('d-none'); 
            $('#divisionSelect').val(null).trigger('change');

        } else if (type === 'join' || type === 'rejoin') {
            // Masuk
            if(singleContainer.length) singleContainer.removeClass('d-none');
            divContainer.removeClass('d-none');

        } else if (type === 'transfer_division') {
            divContainer.removeClass('d-none');
        } else if (type === 'resign') {
            divContainer.addClass('d-none');
        }
    }
</script>
@endpush