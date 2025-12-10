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

                        {{-- INPUT GAJI: HANYA MUNCUL JIKA USER LOGIN ADALAH ADMIN ATAU ADMIN GAJI --}}
                        @if(in_array(auth()->user()->role, ['admin', 'admin_gaji']))
                        <div class="form-group mb-3">
                            <label class="fw-bold text-primary">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                {{-- GANTI TIPE KE TEXT DAN TAMBAH EVENT LISTENER --}}
                                <input type="text" class="form-control" id="gaji" name="gaji" placeholder="Contoh: 3.000.000" value="{{ old('gaji') }}">
                            </div>
                            <small class="text-muted">Masukkan angka, otomatis terformat.</small>
                        </div>
                        @endif

                        <div class="form-group mb-3">
                            <label>awal masuk pstore ( opsional )</label>
                            <input type="date" class="form-control" name="hire_date" value="{{ old('hire_date') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: PENEMPATAN & KONTAK --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Penempatan & Kontak</h4>

                        {{-- SINGLE BRANCH --}}
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

                        {{-- MULTI BRANCH --}}
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

                        <hr>

                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" placeholder="contoh@email.com" value="{{ old('email') }}">
                        </div>
                        <div class="form-group mb-3">
                            <label>WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp" placeholder="08xxx" value="{{ old('whatsapp') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW BARU: SETTING JAM KERJA PERSONAL --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Atur Jam Kerja Personal</h4>
                        <p class="card-description text-muted">
                            Atur jam masuk dan jam pulang spesifik untuk user ini. <br>
                            <strong>Biarkan kosong jika jam kerja Fleksibel/Bebas.</strong>
                        </p>
                        
                        {{-- SATU KARTU UNTUK KEDUANYA --}}
                        <div class="card border" style="border-color: #009688;">
                            <div class="card-header text-white" style="background-color: #009688;">
                                <h6 class="mb-0"><i class="mdi mdi-clock-outline me-2"></i>Pengaturan Jam Kerja</h6>
                            </div>
                            <div class="card-body py-4">
                                <div class="row">
                                    {{-- INPUT KIRI: JAM MASUK --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Jam Masuk (Check In)</label>
                                        <input type="time" class="form-control" name="check_in_start" value="{{ old('check_in_start') }}">
                                        <small class="text-muted">Waktu mulai absen masuk</small>
                                    </div>

                                    {{-- INPUT KANAN: JAM PULANG --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Jam Pulang (Check Out)</label>
                                        <input type="time" class="form-control" name="check_out_start" value="{{ old('check_out_start') }}">
                                        <small class="text-muted">Waktu mulai absen pulang</small>
                                    </div>
                                </div>

                                {{-- TOMBOL HAPUS JAM (RESET) --}}
                                <div class="mt-2">
                                    <a href="javascript:void(0)" onclick="clearWorkHours()" class="text-danger small" style="text-decoration: none;">
                                        <i class="mdi mdi-close-circle me-1"></i>Hapus / Reset ke Fleksibel
                                    </a>
                                </div>
                            </div>
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

            // FUNGSI BARU: RESET JAM KERJA
            window.clearWorkHours = function() {
                $('input[name="check_in_start"]').val('');
                $('input[name="check_out_start"]').val('');
            }

            window.toggleInputs = function() {
                const role = $('#role').val();
                if (role === 'admin') { $('#single-branch-group').addClass('d-none'); } else { $('#single-branch-group').removeClass('d-none'); }
                if (role === 'audit') { $('#multi-branch-group').removeClass('d-none'); } else { $('#multi-branch-group').addClass('d-none'); }
            };
            toggleInputs();

            // --- FUNGSI FORMAT RUPIAH ---
            var gaji = document.getElementById('gaji');
            if(gaji){
                gaji.addEventListener('keyup', function(e){
                    gaji.value = formatRupiah(this.value, '');
                });
            }

            function formatRupiah(angka, prefix){
                var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split   		= number_string.split(','),
                sisa     		= split[0].length % 3,
                rupiah     		= split[0].substr(0, sisa),
                ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
    
                if(ribuan){
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
    
                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return prefix == undefined ? rupiah : (rupiah ? rupiah : '');
            }
        });
    </script>
@endpush