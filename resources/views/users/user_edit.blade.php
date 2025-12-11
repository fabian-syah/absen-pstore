@extends('layout.master')

@section('title', 'Edit User')
@section('heading', 'Edit User: ' . $user->name)

@section('content')
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
        .form-label { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .card-header-custom { background-color: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 15px 20px; }
    </style>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0">
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
            $isAdminGaji  = $currentUser->role == 'admin_gaji';
            $isAudit      = $currentUser->role == 'audit';
            $canEditBranch = $isSuperAdmin || $isAdminGaji || $isAudit;
            $globalDisabled = $isSuperAdmin || $isAdminGaji ? '' : 'disabled';
        @endphp

        <div class="row">
            {{-- KOLOM KIRI --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card shadow-sm border-0">
                    <div class="card-header-custom">
                        <h5 class="mb-0 text-primary"><i class="mdi mdi-account-edit me-2"></i>Edit Data Akun</h5>
                    </div>
                    <div class="card-body">
                        
                        {{-- Data Pribadi --}}
                        <div class="mb-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Nama Lengkap (Sesuai KTP)</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <input type="date" class="form-control" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tanggal Masuk</label>
                                        <input type="date" class="form-control" name="hire_date" value="{{ old('hire_date', $user->hire_date ? $user->hire_date->format('Y-m-d') : '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Kredensial --}}
                        <div class="mb-2">
                            <div class="form-group mb-3">
                                <label class="form-label">ID Login <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="mdi mdi-account-key"></i></span>
                                    <input type="text" class="form-control" name="login_id" value="{{ old('login_id', $user->login_id) }}" required>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Role Pengguna</label>
                                <select class="form-select" id="role" name="role" onchange="toggleInputs()" required {{ $globalDisabled }}>
                                    @foreach ($allowedRoles as $roleOption)
                                        <option value="{{ $roleOption }}" {{ old('role', $user->role) == $roleOption ? 'selected' : '' }}>
                                            {{ strtoupper(str_replace('_', ' ', $roleOption)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($globalDisabled == 'disabled')
                                    <input type="hidden" name="role" value="{{ $user->role }}">
                                @endif
                            </div>

                            {{-- [TOGGLE SCAN ONLY] --}}
                            <div class="p-3 bg-light rounded border mt-3">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="only_security_scan" name="only_security_scan" value="1" 
                                        {{ old('only_security_scan', $user->only_security_scan) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-danger d-flex align-items-center" for="only_security_scan">
                                        <i class="mdi mdi-lock-alert me-2 fs-5"></i> Wajib Absen via Security (Scan Only)
                                    </label>
                                </div>
                                <small class="text-muted ms-4 d-block mt-1">Jika aktif, user ini <b>TIDAK BISA</b> absen mandiri/selfie.</small>
                            </div>

                            <div class="mt-4">
                                <h6 class="text-muted text-uppercase small fw-bold mb-2">Ubah Password (Opsional)</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <input type="password" class="form-control" name="password" placeholder="Password Baru">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <input type="password" class="form-control" name="password_confirmation" placeholder="Konfirmasi">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card shadow-sm border-0">
                    <div class="card-header-custom">
                        <h5 class="mb-0 text-success"><i class="mdi mdi-map-marker-radius me-2"></i>Penempatan & Kontak</h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Lokasi Kerja</h6>

                            <div class="form-group mb-3" id="single-branch-group">
                                <label class="form-label">Cabang Utama</label>
                                <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang" {{ $canEditBranch ? '' : 'disabled' }}>
                                    <option></option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (!$canEditBranch && $user->branch_id)
                                    <input type="hidden" name="branch_id" value="{{ $user->branch_id }}">
                                @endif
                            </div>

                            <div class="form-group mb-3 d-none" id="multi-branch-group">
                                <label class="form-label text-primary">Akses Wilayah Audit (Multi)</label>
                                <select class="form-select select2-multi" name="multi_branches[]" multiple="multiple" style="width: 100%" {{ $globalDisabled }}>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ in_array($branch->id, old('multi_branches', $user->branches->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3" id="multi-division-group">
                                <label class="form-label">Divisi (Multi Select)</label>
                                <select class="form-select select2-multi" name="multi_divisions[]" multiple="multiple" style="width: 100%">
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}" {{ in_array($division->id, old('multi_divisions', $user->divisions->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $division->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="mt-2 small">
                                    <a href="javascript:void(0)" onclick="selectAll('#multi-division-group .select2-multi')">Pilih Semua</a> | 
                                    <a href="javascript:void(0)" onclick="clearAll('#multi-division-group .select2-multi')" class="text-danger">Hapus</a>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-2">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Kontak</h6>
                            <div class="form-group mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">@</span>
                                    <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="mdi mdi-whatsapp"></i></span>
                                    <input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW BARU: SETTING JAM KERJA PERSONAL --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 border-top border-3" style="border-top-color: #009688 !important;">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-teal"><i class="mdi mdi-clock-outline me-2" style="color: #009688;"></i>Atur Jam Kerja Personal</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-4">
                            <i class="mdi mdi-information-outline me-1"></i>
                            Update jam di bawah ini untuk mengubah jadwal spesifik user ini. Biarkan kosong jika fleksibel.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Masuk (Check In)</label>
                                <input type="time" class="form-control form-control-lg" name="check_in_start" 
                                    value="{{ old('check_in_start', $user->check_in_start ? date('H:i', strtotime($user->check_in_start)) : '') }}">
                                <small class="text-muted">Batas awal mulai absen masuk</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Pulang (Check Out)</label>
                                <input type="time" class="form-control form-control-lg" name="check_out_start" 
                                    value="{{ old('check_out_start', $user->check_out_start ? date('H:i', strtotime($user->check_out_start)) : '') }}">
                                <small class="text-muted">Batas awal mulai absen pulang</small>
                            </div>
                        </div>
                        <div class="mt-2 text-end">
                            <button type="button" onclick="clearWorkHours()" class="btn btn-outline-danger btn-sm">
                                <i class="mdi mdi-eraser me-1"></i> Reset Jam ke Kosong
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-5">
            <div class="col-12 text-center">
                <a href="{{ route('users.index') }}" class="btn btn-light btn-lg me-3 px-4">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                    <i class="mdi mdi-content-save-edit me-2"></i>Update Data User
                </button>
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

            window.clearWorkHours = function() {
                $('input[name="check_in_start"]').val('');
                $('input[name="check_out_start"]').val('');
            }

            window.toggleInputs = function() {
                const role = $('#role').val();
                
                // Logic tampilan Single Branch
                if (role === 'admin' || role === 'admin_gaji') { 
                    $('#single-branch-group').addClass('d-none'); 
                } else { 
                    $('#single-branch-group').removeClass('d-none'); 
                }

                // Logic tampilan Multi Branch (Audit)
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