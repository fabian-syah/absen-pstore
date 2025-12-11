@extends('layout.master')

@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

@section('content')
    {{-- CSS SELECT2 --}}
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

    <form class="forms-sample" action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- KOLOM KIRI: DATA PRIBADI & LOGIN --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card shadow-sm border-0">
                    <div class="card-header-custom">
                        <h5 class="mb-0 text-primary"><i class="mdi mdi-account-key me-2"></i>Data Akun & Pribadi</h5>
                    </div>
                    <div class="card-body">
                        
                        {{-- Data Pribadi --}}
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Informasi Dasar</h6>
                            <div class="form-group mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span> (Sesuai KTP)</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <input type="date" class="form-control" name="birth_date" value="{{ old('birth_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tanggal Masuk (Join Date)</label>
                                        <input type="date" class="form-control" name="hire_date" value="{{ old('hire_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Data Login --}}
                        <div class="mb-2">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Kredensial Login</h6>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">ID Login <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="mdi mdi-account"></i></span>
                                    <input type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" placeholder="Username unik" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password" placeholder="********" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password_confirmation" placeholder="********" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Role Pengguna <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" onchange="toggleInputs()" required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach ($allowedRoles as $role)
                                        <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                            {{ strtoupper(str_replace('_', ' ', $role)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- [FITUR BARU] TOGGLE SCAN ONLY --}}
                            <div class="p-3 bg-light rounded border mt-3">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="only_security_scan" name="only_security_scan" value="1" {{ old('only_security_scan') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-danger d-flex align-items-center" for="only_security_scan">
                                        <i class="mdi mdi-lock-alert me-2 fs-5"></i> Wajib Absen via Security (Scan Only)
                                    </label>
                                </div>
                                <small class="text-muted ms-4 d-block mt-1">Jika aktif, user ini <b>TIDAK BISA</b> absen mandiri/selfie. Wajib di-scan oleh Security.</small>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: PENEMPATAN & KONTAK --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card shadow-sm border-0">
                    <div class="card-header-custom">
                        <h5 class="mb-0 text-success"><i class="mdi mdi-map-marker-radius me-2"></i>Penempatan & Kontak</h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Lokasi Kerja</h6>

                            {{-- SINGLE BRANCH --}}
                            <div class="form-group mb-3" id="single-branch-group">
                                <label class="form-label">Cabang Utama</label>
                                <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang">
                                    <option></option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- MULTI BRANCH --}}
                            <div class="form-group mb-3 d-none" id="multi-branch-group">
                                <label class="form-label text-primary">Akses Wilayah Audit (Multi)</label>
                                <select class="form-select select2-multi" name="multi_branches[]" multiple="multiple" style="width: 100%">
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ in_array($branch->id, old('multi_branches', [])) ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="mt-2 small">
                                    <a href="javascript:void(0)" onclick="selectAll('#multi-branch-group .select2-multi')">Pilih Semua</a> | 
                                    <a href="javascript:void(0)" onclick="clearAll('#multi-branch-group .select2-multi')" class="text-danger">Hapus</a>
                                </div>
                            </div>

                            {{-- DIVISI --}}
                            <div class="form-group mb-3" id="multi-division-group">
                                <label class="form-label">Divisi (Bisa Lebih dari Satu)</label>
                                <select class="form-select select2-multi" name="multi_divisions[]" multiple="multiple" style="width: 100%">
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}" {{ in_array($division->id, old('multi_divisions', [])) ? 'selected' : '' }}>
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
                                    <input type="email" class="form-control" name="email" placeholder="contoh@email.com" value="{{ old('email') }}">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="mdi mdi-whatsapp"></i></span>
                                    <input type="text" class="form-control" name="whatsapp" placeholder="08xxx" value="{{ old('whatsapp') }}">
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
                            Atur jam masuk dan jam pulang spesifik untuk user ini. Biarkan kosong jika jadwal mengikuti shift umum atau fleksibel.
                        </p>
                        
                        <div class="row">
                            {{-- INPUT KIRI: JAM MASUK --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Masuk (Check In)</label>
                                <input type="time" class="form-control form-control-lg" name="check_in_start" value="{{ old('check_in_start') }}">
                                <small class="text-muted">Batas awal mulai absen masuk</small>
                            </div>

                            {{-- INPUT KANAN: JAM PULANG --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Pulang (Check Out)</label>
                                <input type="time" class="form-control form-control-lg" name="check_out_start" value="{{ old('check_out_start') }}">
                                <small class="text-muted">Batas awal mulai absen pulang</small>
                            </div>
                        </div>

                        {{-- TOMBOL HAPUS JAM (RESET) --}}
                        <div class="mt-2 text-end">
                            <button type="button" onclick="clearWorkHours()" class="btn btn-outline-danger btn-sm">
                                <i class="mdi mdi-eraser me-1"></i> Reset Jam ke Kosong (Fleksibel)
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
                    <i class="mdi mdi-content-save me-2"></i>Simpan User Baru
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
            $('.select2-multi').select2({ theme: "bootstrap-5", width: '100%', placeholder: "Pilih satu atau lebih...", closeOnSelect: false, allowClear: true });

            @if (auth()->user()->role == 'admin' && auth()->user()->branch_id)
                $('.select2-single').val('{{ auth()->user()->branch_id }}').trigger('change');
            @endif

            window.selectAll = function(selector) { $(selector).find('option').prop('selected', true); $(selector).trigger('change'); }
            window.clearAll = function(selector) { $(selector).val(null).trigger('change'); }

            // FUNGSI BARU: RESET JAM KERJA
            window.clearWorkHours = function() {
                $('input[name="check_in_start"]').val('');
                $('input[name="check_out_start"]').val('');
            }

            window.toggleInputs = function() {
                const role = $('#role').val();
                // UPDATE: Jika role admin ATAU admin_gaji, hide single branch group
                if (role === 'admin' || role === 'admin_gaji') { 
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