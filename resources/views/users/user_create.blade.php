@extends('layout.master')

@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

@section('content')
    {{-- CSS SELECT2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* CSS KHUSUS SELECT2 (SAMA SEPERTI SEBELUMNYA) */
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

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="forms-sample" action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- KOLOM KIRI: DATA LOGIN & ROLE --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Data Login & Role</h4>

                        <div class="form-group mb-3">
                            <label>Nama Lengkap ( Sesuai KTP )</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Tanggal Lahir (Opsional)</label>
                            <input type="date" class="form-control" name="birth_date" value="{{ old('birth_date') }}">
                        </div>

                        <div class="form-group mb-3">
                            <label>ID Login *</label>
                            <input type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>

                        {{-- ROLE SELECTION --}}
                        <div class="form-group mb-3">
                            <label>Role</label>
                            <select class="form-select" id="role" name="role" onchange="toggleInputs()" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach ($allowedRoles as $role)
                                    <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                        {{ strtoupper(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- [PERBAIKAN] INPUT HIRE DATE DISINI (LABEL BARU) --}}
                        <div class="form-group mb-3">
                            <label>awal masuk pstore ( opsional )</label>
                            <input type="date" class="form-control" name="hire_date" value="{{ old('hire_date') }}">
                        </div>

                        {{-- WORK SCHEDULE (OPSIONAL) --}}
                        <div class="form-group mb-3">
                            <label>Jam Kerja Khusus (Opsional)</label>
                            <select class="form-select" name="work_schedule_id">
                                <option value="">-- Ikuti Default Sistem / Cabang --</option>
                                @foreach($workSchedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ old('work_schedule_id') == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->schedule_name }} 
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Biarkan kosong jika ikut default.</small>
                        </div>

                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: PENEMPATAN & KONTAK --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Penempatan & Kontak</h4>

                        {{-- SINGLE BRANCH (HOMEBASE) --}}
                        <div class="form-group mb-3" id="single-branch-group">
                            <label>Cabang Utama (Lokasi Kerja)</label>
                            <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang">
                                <option></option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- MULTI BRANCH (AUDIT) --}}
                        <div class="form-group mb-3 d-none" id="multi-branch-group">
                            <label class="text-primary fw-bold">Akses Wilayah Audit (Multi)</label>
                            <select class="form-select select2-multi" name="multi_branches[]" multiple="multiple" style="width: 100%">
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ in_array($branch->id, old('multi_branches', [])) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2">
                                <a href="javascript:void(0)" onclick="selectAll('#multi-branch-group .select2-multi')">Pilih Semua</a> | 
                                <a href="javascript:void(0)" onclick="clearAll('#multi-branch-group .select2-multi')" class="text-danger">Hapus</a>
                            </div>
                        </div>

                        {{-- DIVISI --}}
                        <div class="form-group mb-3" id="multi-division-group">
                            <label class="text-success fw-bold">Divisi (Multi Select)</label>
                            <select class="form-select select2-multi" name="multi_divisions[]" multiple="multiple" style="width: 100%">
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" {{ in_array($division->id, old('multi_divisions', [])) ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2">
                                <a href="javascript:void(0)" onclick="selectAll('#multi-division-group .select2-multi')">Pilih Semua</a> | 
                                <a href="javascript:void(0)" onclick="clearAll('#multi-division-group .select2-multi')" class="text-danger">Hapus</a>
                            </div>
                        </div>

                        {{-- *PERHATIAN: INPUT HIRE DATE DUPLIKAT DI SINI SUDAH DIHAPUS* --}}

                        <hr>

                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" placeholder="contoh@email.com" value="{{ old('email') }}">
                        </div>
                        <div class="form-group mb-3">
                            <label>WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp" placeholder="08xxx" value="{{ old('whatsapp') }}">
                        </div>
                         <div class="form-group mb-3">
                            <label>Foto Profil</label>
                            <input type="file" class="form-control" name="profile_photo_path">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary btn-lg me-3">Simpan User</button>
                <a href="{{ route('users.index') }}" class="btn btn-light btn-lg">Batal</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-single').select2({ theme: "bootstrap-5", width: '100%', placeholder: "Silahkan pilih...", allowClear: true });
            $('.select2-multi').select2({ theme: "bootstrap-5", width: '100%', placeholder: "Pilih satu atau lebih...", closeOnSelect: false, allowClear: true });

            @if (auth()->user()->role == 'admin' && auth()->user()->branch_id)
                $('.select2-single').val('{{ auth()->user()->branch_id }}').trigger('change');
            @endif

            window.selectAll = function(selector) { $(selector).find('option').prop('selected', true); $(selector).trigger('change'); }
            window.clearAll = function(selector) { $(selector).val(null).trigger('change'); }

            window.toggleInputs = function() {
                const role = $('#role').val();
                if (role === 'admin') { $('#single-branch-group').addClass('d-none'); } else { $('#single-branch-group').removeClass('d-none'); }
                if (role === 'audit') { $('#multi-branch-group').removeClass('d-none'); } else { $('#multi-branch-group').addClass('d-none'); }
            };
            toggleInputs();
        });
    </script>
@endpush