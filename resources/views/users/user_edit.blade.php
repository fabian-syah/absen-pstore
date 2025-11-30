@extends('layout.master')

@section('title', 'Edit User')
@section('heading', 'Edit User: ' . $user->name)

@section('content')
    {{-- CSS SELECT2 (SAMA DENGAN CREATE) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* CSS ANTI-CONFLICT */
        .select2-container ul,
        .select2-container li,
        .select2-selection__rendered,
        span.select2-selection__choice {
            list-style: none !important;
            list-style-type: none !important;
            padding-left: 0 !important;
            margin-left: 0 !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple {
            background-color: #fff !important;
            border: 1px solid #ced4da !important;
            min-height: 38px !important;
            padding: 4px !important;
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #e9ecef !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 20px !important;
            padding: 2px 10px !important;
            margin: 2px 4px !important;
            font-size: 0.85rem !important;
            color: #333 !important;
            display: inline-flex !important;
            align-items: center !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove {
            border: none !important;
            background: transparent !important;
            margin-right: 5px !important;
            color: #999 !important;
            font-weight: bold !important;
            padding: 0 !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove:hover {
            color: #dc3545 !important;
        }

        .select2-results__option {
            padding: 6px 12px !important;
            font-size: 14px !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--selected {
            background-color: #e9ecef !important;
            color: #333 !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #0d6efd !important;
            color: #fff !important;
        }

        /* Badge akses */
        .access-badge {
            font-size: 0.75rem;
            padding: 3px 8px;
            margin-left: 5px;
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
            // Cek hak akses user yang sedang login (bukan user yang sedang diedit)
            $currentUser = Auth::user();
            $isSuperAdmin = $currentUser->role == 'admin' && $currentUser->branch_id == null;

            // Jika bukan Super Admin, input Role & Branch dimatikan (disabled)
            $disabledAttr = $isSuperAdmin ? '' : 'disabled';
        @endphp

        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Data Login & Role</h4>

                        <div class="form-group mb-3">
                            <label>Nama Lengkap ( Sesuai KTP )</label>
                            <input type="text" class="form-control" name="name"
                                value="{{ old('name', $user->name) }}" required>
                        </div>

                        {{-- [BARU] TANGGAL LAHIR --}}
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
                            {{-- Select Role --}}
                            <select class="form-select" id="role" name="role" onchange="toggleInputs()" required
                                {{ $disabledAttr }}>
                                @foreach ($allowedRoles as $roleOption)
                                    @if ($roleOption == 'admin')
                                        <option value="admin"
                                            {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Super Admin
                                        </option>
                                    @elseif($roleOption == 'audit')
                                        <option value="audit"
                                            {{ old('role', $user->role) == 'audit' ? 'selected' : '' }}>Audit (Multi
                                            Cabang)</option>
                                    @elseif($roleOption == 'leader')
                                        <option value="leader"
                                            {{ old('role', $user->role) == 'leader' ? 'selected' : '' }}>Leader</option>
                                    @elseif($roleOption == 'security')
                                        <option value="security"
                                            {{ old('role', $user->role) == 'security' ? 'selected' : '' }}>Security
                                        </option>
                                    @elseif($roleOption == 'user_biasa')
                                        <option value="user_biasa"
                                            {{ old('role', $user->role) == 'user_biasa' ? 'selected' : '' }}>Karyawan
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                            {{-- Input Hidden untuk Role jika disabled --}}
                            @if (!$isSuperAdmin)
                                <input type="hidden" name="role" value="{{ $user->role }}">
                                <small class="text-danger mt-1 d-block">Hanya Super Admin yang bisa mengubah Role.</small>
                            @endif
                        </div>

                        <hr class="my-4">
                        <p class="card-description text-muted">Kosongkan password jika tidak ingin mengubahnya.</p>

                        <div class="form-group mb-3">
                            <label>Password Baru</label>
                            <input type="password" class="form-control" name="password" placeholder="********">
                        </div>
                        <div class="form-group mb-3">
                            <label>Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation"
                                placeholder="********">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Penempatan & Kontak</h4>

                        {{-- SINGLE BRANCH (HOMEBASE) --}}
                        <div class="form-group mb-3" id="single-branch-group">
                            <label>Cabang Utama (Lokasi Kerja)</label>
                            <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang"
                                {{ $disabledAttr }}>
                                <option></option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>

                            @if (!$isSuperAdmin && $user->branch_id)
                                <input type="hidden" name="branch_id" value="{{ $user->branch_id }}">
                            @endif
                            <small class="text-muted">Lokasi utama user bekerja.</small>
                        </div>

                        {{-- MULTI BRANCH (WILAYAH AUDIT) --}}
                        <div class="form-group mb-3 d-none" id="multi-branch-group">
                            <label class="text-primary fw-bold">Akses Wilayah Audit (Multi)</label>
                            <br>
                            <select class="form-select select2-multi" name="multi_branches[]" multiple="multiple"
                                style="width: 100%" {{ $disabledAttr }}>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ in_array($branch->id, old('multi_branches', $user->branches->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($isSuperAdmin)
                                <div class="mt-2">
                                    <a href="javascript:void(0)" onclick="selectAll('#multi-branch-group .select2-multi')"
                                        class="me-2">Pilih Semua</a>
                                    <a href="javascript:void(0)" onclick="clearAll('#multi-branch-group .select2-multi')"
                                        class="text-danger">Hapus Semua</a>
                                </div>
                            @endif
                            <small class="text-muted">Pilih wilayah audit user ini.</small>
                        </div>

                        {{-- DIVISI (GLOBAL - MULTI SELECT) --}}
                        <div class="form-group mb-3" id="multi-division-group">
                            <label class="text-success fw-bold">Divisi (Multi Select)</label>
                            <br>
                            <select class="form-select select2-multi" name="multi_divisions[]" multiple="multiple"
                                style="width: 100%">
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ in_array($division->id, old('multi_divisions', $user->divisions->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{-- PERBAIKAN: HANYA TAMPILKAN NAMA DIVISI (GLOBAL) --}}
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2">
                                <a href="javascript:void(0)" onclick="selectAll('#multi-division-group .select2-multi')"
                                    class="me-2">Pilih Semua</a>
                                <a href="javascript:void(0)"
                                    onclick="clearAll('#multi-division-group .select2-multi')"
                                    class="text-danger">Hapus Semua</a>
                            </div>
                            <small class="text-muted">Pilih satu atau lebih divisi (Jabatan).</small>
                        </div>

                        {{-- [BARU] TANGGAL MASUK (HIRE DATE) --}}
                        <div class="form-group mb-3">
                            <label>Tanggal Masuk (Hire Date - Opsional)</label>
                            <input type="date" class="form-control" name="hire_date"
                                value="{{ old('hire_date', $user->hire_date) }}">
                        </div>

                        <hr>

                        <div class="form-group mb-3">
                            <label>Foto Profil</label>
                            <input type="file" class="form-control" name="profile_photo_path">
                            @if ($user->profile_photo_path)
                                <div class="mt-2">
                                    <small class="text-muted">Foto saat ini:</small><br>
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Profile"
                                        class="img-thumbnail" style="max-height: 100px">
                                </div>
                            @endif
                        </div>
                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email"
                                value="{{ old('email', $user->email) }}">
                        </div>
                        <div class="form-group mb-3">
                            <label>WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp"
                                value="{{ old('whatsapp', $user->whatsapp) }}">
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
            $('.select2-single').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Silahkan pilih...",
                allowClear: true
            });
            $('.select2-multi').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Pilih data...",
                closeOnSelect: false,
                allowClear: true
            });

            window.selectAll = function(selector) {
                $(selector).find('option').prop('selected', true);
                $(selector).trigger('change');
            }
            window.clearAll = function(selector) {
                $(selector).val(null).trigger('change');
            }

            window.toggleInputs = function() {
                const role = $('#role').val();
                if (role === 'admin') {
                    $('#single-branch-group').addClass('d-none');
                } else {
                    $('#single-branch-group').removeClass('d-none');
                }
                if (role === 'audit') {
                    $('#multi-branch-group').removeClass('d-none');
                } else {
                    $('#multi-branch-group').addClass('d-none');
                }
            };
            toggleInputs();
        });
    </script>
@endpush