@extends('layout.master')

@section('title', 'Edit User')
@section('heading', 'Edit User: ' . $user->name)

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        .select2-container ul, .select2-container li, .select2-selection__rendered, span.select2-selection__choice { list-style: none !important; padding-left: 0 !important; margin-left: 0 !important; }
        .select2-container--bootstrap-5 .select2-selection--multiple { background-color: #fff !important; border: 1px solid #ced4da !important; padding: 4px !important; display: flex !important; flex-wrap: wrap !important; align-items: center !important; }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice { background-color: #e9ecef !important; border-radius: 20px !important; padding: 2px 10px !important; margin: 2px 4px !important; font-size: 0.85rem !important; color: #333 !important; }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove { border: none !important; background: transparent !important; margin-right: 5px !important; color: #999 !important; }
        .form-label { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .card-header-custom { background-color: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 15px 20px; }

        /* SECURITY TOGGLE STYLES */
        .security-access-card { border: 1px solid #e0e0e0; border-radius: 12px; transition: all 0.3s ease; position: relative; overflow: hidden; background: #fff; margin-bottom: 15px;}
        .security-access-card.unlocked { border-left: 5px solid #10b981; background: linear-gradient(to right, #f0fdf4, #fff); }
        .security-access-card.locked { border-left: 5px solid #dc3545; background: linear-gradient(to right, #fef2f2, #fff); }
        .security-icon-wrapper { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; transition: all 0.3s ease; }
        .unlocked .security-icon-wrapper { background-color: #d1fae5; color: #10b981; }
        .locked .security-icon-wrapper { background-color: #fee2e2; color: #dc3545; }

         /* AI TOGGLE STYLES */
        .ai-access-card { border: 1px solid #e0e0e0; border-radius: 12px; transition: all 0.3s ease; position: relative; overflow: hidden; background: #fff; }
        .ai-access-card.on { border-left: 5px solid #3b82f6; background: linear-gradient(to right, #eff6ff, #fff); }
        .ai-access-card.off { border-left: 5px solid #f59e0b; background: linear-gradient(to right, #fffbeb, #fff); }
        .ai-icon-wrapper { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; transition: all 0.3s ease; }
        .on .ai-icon-wrapper { background-color: #dbeafe; color: #3b82f6; }
        .off .ai-icon-wrapper { background-color: #fef3c7; color: #f59e0b; }

        .form-switch .form-check-input { width: 3.5em; height: 1.75em; cursor: pointer; }
        .security-status-text { font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
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
            $isLeader     = $currentUser->role == 'leader';
            
            // Logic: Hanya Super Admin / Admin Gaji yang boleh mengubah struktur cabang User
            $canEditBranch = $isSuperAdmin || $isAdminGaji || $isAudit || $isLeader;
            
            // Variable untuk men-disable input Multi Branch jika yang login bukan Admin/Admin Gaji
            // Ini akan membuat input jadi abu-abu (disabled) untuk Audit/Leader
            $globalDisabled = ($isSuperAdmin || $isAdminGaji) ? '' : 'disabled';
        @endphp

        <div class="row">
            {{-- KOLOM KIRI --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card shadow-sm border-0">
                    <div class="card-header-custom"><h5 class="mb-0 text-primary"><i class="mdi mdi-account-edit me-2"></i>Edit Data Akun</h5></div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="form-group mb-3"><label class="form-label">Nama Lengkap (Sesuai KTP)</label><input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required></div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group mb-3"><label class="form-label">Tanggal Lahir</label><input type="date" class="form-control" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}"></div></div>
                                <div class="col-md-6"><div class="form-group mb-3"><label class="form-label">Tanggal Masuk</label><input type="date" class="form-control" name="hire_date" value="{{ old('hire_date', $user->hire_date ? $user->hire_date->format('Y-m-d') : '') }}"></div></div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="mb-2">
                            <div class="form-group mb-3"><label class="form-label">ID Login <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text bg-light"><i class="mdi mdi-account-key"></i></span><input type="text" class="form-control" name="login_id" value="{{ old('login_id', $user->login_id) }}" required></div></div>
                            <div class="form-group mb-3"><label class="form-label">Role Pengguna</label><select class="form-select" id="role" name="role" onchange="toggleInputs()" required {{ $globalDisabled }}>@foreach ($allowedRoles as $roleOption)<option value="{{ $roleOption }}" {{ old('role', $user->role) == $roleOption ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $roleOption)) }}</option>@endforeach</select>@if ($globalDisabled == 'disabled')<input type="hidden" name="role" value="{{ $user->role }}">@endif</div>
                            
                            {{-- KONTROL AKSES ABSENSI --}}
                            <div class="mt-4">
                                <label class="form-label mb-2">Kontrol Akses Absensi</label>
                                
                                {{-- 1. SECURITY SCAN ONLY --}}
                                <div class="security-access-card p-3 d-flex align-items-center justify-content-between {{ old('only_security_scan', $user->only_security_scan) ? 'locked' : 'unlocked' }}" id="securityCard">
                                    <div class="d-flex align-items-center">
                                        <div class="security-icon-wrapper me-3"><i class="mdi {{ old('only_security_scan', $user->only_security_scan) ? 'mdi-lock-alert' : 'mdi-cellphone-check' }}" id="securityIcon"></i></div>
                                        <div>
                                            <div class="security-status-text {{ old('only_security_scan', $user->only_security_scan) ? 'text-danger' : 'text-success' }}" id="securityStatusTitle">{{ old('only_security_scan', $user->only_security_scan) ? 'DIBATASI: HANYA SCAN SECURITY' : 'ABSENSI MANDIRI AKTIF' }}</div>
                                            <small class="text-muted lh-1 d-block mt-1" id="securityStatusDesc">{{ old('only_security_scan', $user->only_security_scan) ? 'User DILARANG absen mandiri/selfie. Wajib Scan QR ke Security.' : 'User bisa melakukan absen mandiri via HP (Selfie/Lokasi).' }}</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="only_security_scan" name="only_security_scan" value="1" {{ old('only_security_scan', $user->only_security_scan) ? 'checked' : '' }} onchange="toggleSecurityCard(this)">
                                    </div>
                                </div>

                                {{-- 2. AI FACE DETECTION --}}
                                <div class="ai-access-card p-3 d-flex align-items-center justify-content-between {{ old('use_face_recognition', $user->use_face_recognition) ? 'on' : 'off' }}" id="aiCard">
                                    <div class="d-flex align-items-center">
                                        <div class="ai-icon-wrapper me-3"><i class="mdi {{ old('use_face_recognition', $user->use_face_recognition) ? 'mdi-face-recognition' : 'mdi-camera-off' }}" id="aiIcon"></i></div>
                                        <div>
                                            <div class="security-status-text {{ old('use_face_recognition', $user->use_face_recognition) ? 'text-primary' : 'text-warning' }}" id="aiStatusTitle">{{ old('use_face_recognition', $user->use_face_recognition) ? 'AI FACE DETECT ON' : 'AI FACE DETECT OFF' }}</div>
                                            <small class="text-muted lh-1 d-block mt-1" id="aiStatusDesc">{{ old('use_face_recognition', $user->use_face_recognition) ? 'Wajib scan wajah saat absen mandiri.' : 'Mode Manual: Kamera aktif tanpa deteksi wajah (untuk Cadar/Masker).' }}</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        {{-- Hidden Input --}}
                                        <input type="hidden" name="use_face_recognition" value="0">
                                        <input class="form-check-input" type="checkbox" id="use_face_recognition" name="use_face_recognition" value="1" {{ old('use_face_recognition', $user->use_face_recognition) ? 'checked' : '' }} onchange="toggleAICard(this)">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h6 class="text-muted text-uppercase small fw-bold mb-2">Ubah Password (Opsional)</h6>
                                <div class="row">
                                    <div class="col-md-6"><div class="form-group mb-3"><input type="password" class="form-control" name="password" placeholder="Password Baru"></div></div>
                                    <div class="col-md-6"><div class="form-group mb-3"><input type="password" class="form-control" name="password_confirmation" placeholder="Konfirmasi"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card shadow-sm border-0">
                    <div class="card-header-custom"><h5 class="mb-0 text-success"><i class="mdi mdi-map-marker-radius me-2"></i>Penempatan & Kontak</h5></div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Lokasi Kerja</h6>
                            <div class="form-group mb-3" id="single-branch-group"><label class="form-label">Cabang Utama</label><select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang" {{ $canEditBranch ? '' : 'disabled' }}><option></option>@foreach ($branches as $branch)<option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>@endforeach</select>@if (!$canEditBranch && $user->branch_id)<input type="hidden" name="branch_id" value="{{ $user->branch_id }}">@endif</div>
                            
                            {{-- MULTI BRANCH SELECT - INI YANG DIMINTA DISABLED --}}
                            <div class="form-group mb-3 d-none" id="multi-branch-group">
                                <label class="form-label text-primary">Akses Wilayah Audit/Leader (Multi)</label>
                                {{-- Jika $globalDisabled aktif ('disabled'), maka user (Audit/Leader) tidak bisa klik ini. --}}
                                <select class="form-select select2-multi" name="multi_branches[]" multiple="multiple" style="width: 100%" {{ $globalDisabled }}>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ in_array($branch->id, old('multi_branches', $user->branches->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @if($globalDisabled == 'disabled')
                                    <small class="text-danger mt-1 d-block"><i class="mdi mdi-lock"></i> Akses wilayah terkunci. Hanya Admin yang bisa mengubah.</small>
                                @endif
                            </div>

                            <div class="form-group mb-3" id="multi-division-group"><label class="form-label">Divisi (Multi Select)</label><select class="form-select select2-multi" name="multi_divisions[]" multiple="multiple" style="width: 100%">@foreach ($divisions as $division)<option value="{{ $division->id }}" {{ in_array($division->id, old('multi_divisions', $user->divisions->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $division->name }}</option>@endforeach</select><div class="mt-2 small"><a href="javascript:void(0)" onclick="selectAll('#multi-division-group .select2-multi')">Pilih Semua</a> | <a href="javascript:void(0)" onclick="clearAll('#multi-division-group .select2-multi')" class="text-danger">Hapus</a></div></div>
                        </div>
                        <hr class="my-4">
                        <div class="mb-2">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Kontak</h6>
                            <div class="form-group mb-3"><label class="form-label">Email</label><div class="input-group"><span class="input-group-text bg-light">@</span><input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}"></div></div>
                            <div class="form-group mb-3"><label class="form-label">WhatsApp</label><div class="input-group"><span class="input-group-text bg-light"><i class="mdi mdi-whatsapp"></i></span><input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 border-top border-3" style="border-top-color: #009688 !important;">
                    <div class="card-header bg-white py-3"><h5 class="mb-0 text-teal"><i class="mdi mdi-clock-outline me-2" style="color: #009688;"></i>Atur Jam Kerja Personal</h5></div>
                    <div class="card-body">
                        <p class="text-muted small mb-4"><i class="mdi mdi-information-outline me-1"></i>Update jam di bawah ini untuk mengubah jadwal spesifik user ini. Biarkan kosong jika fleksibel.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Jam Masuk (Check In)</label><input type="time" class="form-control form-control-lg" name="check_in_start" value="{{ old('check_in_start', $user->check_in_start ? date('H:i', strtotime($user->check_in_start)) : '') }}"><small class="text-muted">Batas awal mulai absen masuk</small></div>
                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Jam Pulang (Check Out)</label><input type="time" class="form-control form-control-lg" name="check_out_start" value="{{ old('check_out_start', $user->check_out_start ? date('H:i', strtotime($user->check_out_start)) : '') }}"><small class="text-muted">Batas awal mulai absen pulang</small></div>
                        </div>
                        <div class="mt-2 text-end"><button type="button" onclick="clearWorkHours()" class="btn btn-outline-danger btn-sm"><i class="mdi mdi-eraser me-1"></i> Reset Jam ke Kosong</button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-5">
            <div class="col-12 text-center"><a href="{{ route('users.index') }}" class="btn btn-light btn-lg me-3 px-4">Batal</a><button type="submit" class="btn btn-primary btn-lg px-5 shadow"><i class="mdi mdi-content-save-edit me-2"></i>Update Data User</button></div>
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
            window.clearWorkHours = function() { $('input[name="check_in_start"]').val(''); $('input[name="check_out_start"]').val(''); }
            
            // LOGIC UTAMA: Role Audit ATAU Leader menampilkan Multi Branch
            window.toggleInputs = function() {
                const role = $('#role').val();
                if (role === 'admin' || role === 'admin_gaji') { $('#single-branch-group').addClass('d-none'); } else { $('#single-branch-group').removeClass('d-none'); }
                
                // DISINI UPDATE-NYA:
                if (role === 'audit' || role === 'leader') { 
                    $('#multi-branch-group').removeClass('d-none'); 
                } else { 
                    $('#multi-branch-group').addClass('d-none'); 
                }
            };
            toggleInputs();

            // JS SECURITY CARD
            window.toggleSecurityCard = function(checkbox) {
                const card = document.getElementById('securityCard');
                const icon = document.getElementById('securityIcon');
                const title = document.getElementById('securityStatusTitle');
                const desc = document.getElementById('securityStatusDesc');
                if (checkbox.checked) {
                    card.classList.remove('unlocked'); card.classList.add('locked');
                    icon.className = 'mdi mdi-lock-alert';
                    title.className = 'security-status-text text-danger'; title.textContent = 'DIBATASI: HANYA SCAN SECURITY';
                    desc.textContent = 'User DILARANG absen mandiri/selfie. Wajib Scan QR ke Security.';
                } else {
                    card.classList.remove('locked'); card.classList.add('unlocked');
                    icon.className = 'mdi mdi-cellphone-check';
                    title.className = 'security-status-text text-success'; title.textContent = 'ABSENSI MANDIRI AKTIF';
                    desc.textContent = 'User bisa melakukan absen mandiri via HP (Selfie/Lokasi).';
                }
            };

            // JS AI CARD
            window.toggleAICard = function(checkbox) {
                const card = document.getElementById('aiCard');
                const icon = document.getElementById('aiIcon');
                const title = document.getElementById('aiStatusTitle');
                const desc = document.getElementById('aiStatusDesc');
                if (checkbox.checked) {
                    card.classList.remove('off'); card.classList.add('on');
                    icon.className = 'mdi mdi-face-recognition';
                    title.className = 'security-status-text text-primary'; title.textContent = 'AI FACE DETECT ON';
                    desc.textContent = 'Wajib scan wajah saat absen mandiri.';
                } else {
                    card.classList.remove('on'); card.classList.add('off');
                    icon.className = 'mdi mdi-camera-off';
                    title.className = 'security-status-text text-warning'; title.textContent = 'AI FACE DETECT OFF';
                    desc.textContent = 'Mode Manual: Kamera aktif tanpa deteksi wajah (untuk Cadar/Masker).';
                }
            };

            // Init state if element exists (for edit page)
            const aiCheckbox = document.getElementById('use_face_recognition');
            if(aiCheckbox) toggleAICard(aiCheckbox);
        });
    </script>
@endpush