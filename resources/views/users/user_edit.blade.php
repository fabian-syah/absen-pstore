@extends('layout.master')

@section('title', 'Edit User')
@section('heading', 'Edit User: ' . $user->name)

@section('content')
    {{-- CSS SELECT2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* CSS KHUSUS SELECT2 */
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

    <form class="forms-sample" action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @php
            $currentUser = Auth::user();
            $isSuperAdmin = $currentUser->role == 'admin' && $currentUser->branch_id == null;
            $disabledAttr = $isSuperAdmin ? '' : 'disabled';
        @endphp

        <div class="row">
            {{-- KOLOM KIRI --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Data Login & Role</h4>

                        <div class="form-group mb-3">
                            <label>Nama Lengkap ( Sesuai KTP )</label>
                            <input type="text" class="form-control" name="name"
                                value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Tanggal Lahir (Opsional)</label>
                            <input type="date" class="form-control" name="birth_date"
                                value="{{ old('birth_date', $user->birth_date) }}">
                        </div>

                        <div class="form-group mb-3">
                            <label>ID Login *</label>
                            <input type="text" class="form-control" name="login_id"
                                value="{{ old('login_id', $user->login_id) }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Role</label>
                            <select class="form-select" id="role" name="role" onchange="toggleInputs()" required {{ $disabledAttr }}>
                                @foreach ($allowedRoles as $roleOption)
                                    <option value="{{ $roleOption }}" {{ old('role', $user->role) == $roleOption ? 'selected' : '' }}>
                                        {{ strtoupper(str_replace('_', ' ', $roleOption)) }}
                                    </option>
                                @endforeach
                            </select>
                            @if (!$isSuperAdmin)
                                <input type="hidden" name="role" value="{{ $user->role }}">
                            @endif
                        </div>

                        {{-- [PERBAIKAN] INPUT HIRE DATE DENGAN FORMAT YG BENAR --}}
                        <div class="form-group mb-3">
                            <label>awal masuk pstore ( opsional )</label>
                            <input type="date" class="form-control" name="hire_date"
                                value="{{ old('hire_date', $user->hire_date ? $user->hire_date->format('Y-m-d') : '') }}">
                        </div>

                        <div class="form-group mb-3">
                            <label>Password Baru</label>
                            <input type="password" class="form-control" name="password" placeholder="********">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label>Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation" placeholder="********">
                        </div>

                        {{-- JAM KERJA --}}
                        <div class="form-group mb-3">
                            <label>Jam Kerja Khusus (Opsional)</label>
                            <select class="form-select" name="work_schedule_id">
                                <option value="">-- Ikuti Default Sistem / Cabang --</option>
                                @foreach($workSchedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ old('work_schedule_id', $user->work_schedule_id) == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->schedule_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Penempatan & Kontak</h4>

                        {{-- SINGLE BRANCH --}}
                        <div class="form-group mb-3" id="single-branch-group">
                            <label>Cabang Utama (Lokasi Kerja)</label>
                            <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang" {{ $disabledAttr }}>
                                <option></option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if (!$isSuperAdmin && $user->branch_id)
                                <input type="hidden" name="branch_id" value="{{ $user->branch_id }}">
                            @endif
                        </div>

                        {{-- MULTI BRANCH --}}
                        <div class="form-group mb-3 d-none" id="multi-branch-group">
                            <label class="text-primary fw-bold">Akses Wilayah Audit (Multi)</label>
                            <select class="form-select select2-multi" name="multi_branches[]" multiple="multiple" style="width: 100%" {{ $disabledAttr }}>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ in_array($branch->id, old('multi_branches', $user->branches->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DIVISI --}}
                        <div class="form-group mb-3" id="multi-division-group">
                            <label class="text-success fw-bold">Divisi (Multi Select)</label>
                            <select class="form-select select2-multi" name="multi_divisions[]" multiple="multiple" style="width: 100%">
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" {{ in_array($division->id, old('multi_divisions', $user->divisions->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2">
                                <a href="javascript:void(0)" onclick="selectAll('#multi-division-group .select2-multi')">Pilih Semua</a> | 
                                <a href="javascript:void(0)" onclick="clearAll('#multi-division-group .select2-multi')" class="text-danger">Hapus</a>
                            </div>
                        </div>

                        <hr>

                        {{-- <div class="form-group mb-3">
                            <label>Foto Profil</label>
                            <input type="file" class="form-control" name="profile_photo_path">
                            @if ($user->profile_photo_path)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Profile" class="img-thumbnail" style="max-height: 100px">
                                </div>
                            @endif
                        </div> --}}
                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}">
                        </div>
                        <div class="form-group mb-3">
                            <label>WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary btn-lg me-3">Update User</button>
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
            $('.select2-multi').select2({ theme: "bootstrap-5", width: '100%', placeholder: "Pilih data...", closeOnSelect: false, allowClear: true });

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