@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        
        {{-- Header Card --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Setting Master Gaji</h3>
                <p class="text-muted mb-0">Atur komponen gaji dan kategori karyawan.</p>
            </div>
            <a href="{{ route('employee-salaries.index') }}" class="btn btn-light shadow-sm fw-bold">
                <i class="mdi mdi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">

                {{-- User Info Header --}}
                <div class="d-flex align-items-center mb-5 pb-4 border-bottom">
                    <div class="avatar-lg bg-primary rounded-circle text-white d-flex align-items-center justify-content-center fw-bold me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">{{ $user->name }}</h4>
                        <span class="badge bg-light text-dark border me-2"><i class="mdi mdi-domain me-1"></i> {{ $user->branch->name ?? 'No Branch' }}</span>
                        <span class="badge bg-light text-dark border"><i class="mdi mdi-card-account-details-outline me-1"></i> ID: {{ $user->login_id ?? '-' }}</span>
                    </div>
                </div>
                
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('employee-salaries.update', $user->id) }}" method="POST" id="salaryForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-5">
                        
                        {{-- KOLOM KIRI: PENGATURAN DASAR --}}
                        <div class="col-md-5 border-end">
                            <h6 class="fw-bold text-secondary text-uppercase mb-3 small ls-1">KATEGORI & PEMBAYARAN</h6>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Kategori Karyawan</label>
                                <select name="category" id="category" class="form-select form-select-lg shadow-none border-primary">
                                    <option value="employee" {{ ($user->employeeSalary->category ?? '') == 'employee' ? 'selected' : '' }}>Karyawan Tetap</option>
                                    <option value="promotor" {{ ($user->employeeSalary->category ?? '') == 'promotor' ? 'selected' : '' }}>Promotor / JCS</option>
                                    <option value="freelance" {{ ($user->employeeSalary->category ?? '') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                                </select>
                                <div class="form-text mt-2" id="cat_desc">Pilih jenis kontrak kerja karyawan ini.</div>
                            </div>

                            <div class="card bg-light border-0 rounded-3 mt-4">
                                <div class="card-body">
                                    <h6 class="fw-bold text-dark mb-3"><i class="mdi mdi-bank me-1"></i> Rekening Bank</h6>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted">Nama Bank</label>
                                        <input type="text" name="bank_name" class="form-control" 
                                               value="{{ $user->employeeSalary->bank_name ?? '' }}" placeholder="Contoh: BCA">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small text-muted">Nomor Rekening</label>
                                        <input type="number" name="bank_account_number" class="form-control" 
                                               value="{{ $user->employeeSalary->bank_account_number ?? '' }}" placeholder="Contoh: 1234567890">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KOLOM KANAN: KOMPONEN GAJI (DINAMIS) --}}
                        <div class="col-md-7">
                            
                            {{-- 1. KARYAWAN TETAP --}}
                            <div id="form_employee" class="salary-section">
                                <h6 class="fw-bold text-success text-uppercase mb-3 small ls-1"><i class="mdi mdi-briefcase-check me-1"></i> KOMPONEN KARYAWAN TETAP</h6>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Gaji Pokok</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="text" name="basic_salary" class="form-control rupiahe fw-bold form-control-lg" 
                                               value="{{ number_format($user->employeeSalary->basic_salary ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                    <div class="form-text text-danger">* Maksimal Rp 6.000.000</div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Tunjangan Jabatan</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">Rp</span>
                                            <input type="text" name="position_allowance" class="form-control rupiahe" 
                                                   value="{{ number_format($user->employeeSalary->position_allowance ?? 0, 0, ',', '.') }}" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Privilege (Nominal)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">Rp</span>
                                            <input type="text" name="owner_privilege" class="form-control rupiahe" 
                                                   value="{{ number_format($user->employeeSalary->owner_privilege ?? 0, 0, ',', '.') }}" placeholder="0">
                                        </div>
                                    </div>
                                </div>

                                {{-- SWITCH PRIVILEGE --}}
                                <div class="p-3 rounded-3 border" style="background-color: #f0fdf4; border-color: #bbf7d0 !important;">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="use_privilege_mode" value="1" id="privilegeSwitch"
                                               {{ ($user->employeeSalary->use_privilege_mode ?? 0) ? 'checked' : '' }} style="cursor: pointer;">
                                        <label class="form-check-label fw-bold text-success" for="privilegeSwitch" style="cursor: pointer;">
                                            Aktifkan Mode Privilege (Bebas Potongan)
                                        </label>
                                    </div>
                                    <p class="mb-0 mt-2 small text-muted lh-sm ms-4">
                                        Jika aktif, karyawan ini <strong>tidak akan terkena potongan absen</strong> (Alpha/Telat) secara otomatis saat proses Payroll.
                                    </p>
                                </div>
                            </div>

                            {{-- 2. PROMOTOR --}}
                            <div id="form_promotor" class="salary-section" style="display: none;">
                                <h6 class="fw-bold text-info text-uppercase mb-3 small ls-1"><i class="mdi mdi-bullhorn me-1"></i> KOMPONEN PROMOTOR / JCS</h6>
                                <div class="alert alert-info d-flex align-items-center py-2 px-3 mb-4">
                                    <i class="mdi mdi-information-outline fs-5 me-2"></i>
                                    <span class="small">Kategori ini hanya menerima <strong>Bonus / Insentif</strong>.</span>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Insentif / Bonus (Estimasi)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="text" name="promotor_bonus" class="form-control rupiahe fw-bold form-control-lg" 
                                               value="{{ number_format($user->employeeSalary->promotor_bonus ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                    <div class="form-text">Nominal ini akan muncul otomatis saat Payroll (dapat diedit).</div>
                                </div>
                            </div>

                            {{-- 3. FREELANCE --}}
                            <div id="form_freelance" class="salary-section" style="display: none;">
                                <h6 class="fw-bold text-warning text-uppercase mb-3 small ls-1"><i class="mdi mdi-account-clock me-1"></i> KOMPONEN FREELANCE</h6>
                                <div class="alert alert-warning d-flex align-items-center py-2 px-3 mb-4">
                                    <i class="mdi mdi-clock-outline fs-5 me-2"></i>
                                    <span class="small">Pembayaran dihitung <strong>Harian</strong> (No Work No Pay).</span>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Rate Gaji Per Hari</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="text" name="daily_salary" class="form-control rupiahe fw-bold form-control-lg" 
                                               value="{{ number_format($user->employeeSalary->daily_salary ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <hr class="my-5">

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('employee-salaries.index') }}" class="btn btn-light btn-lg px-4 fw-bold">Batal</a>
                        <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm rounded-pill">
                            <i class="mdi mdi-content-save-check me-2"></i> Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const sections = document.querySelectorAll('.salary-section');
        const catDesc = document.getElementById('cat_desc');
        
        const descriptions = {
            'employee': 'Mendapatkan Gaji Pokok + Tunjangan Bulanan.',
            'promotor': 'Hanya mendapatkan Insentif/Bonus Bulanan.',
            'freelance': 'Dibayar harian berdasarkan kehadiran.'
        };

        function toggleForm() {
            const val = categorySelect.value;
            sections.forEach(el => el.style.display = 'none');

            if (val === 'employee') document.getElementById('form_employee').style.display = 'block';
            if (val === 'promotor') document.getElementById('form_promotor').style.display = 'block';
            if (val === 'freelance') document.getElementById('form_freelance').style.display = 'block';

            if(catDesc) catDesc.innerText = descriptions[val];
        }

        categorySelect.addEventListener('change', toggleForm);
        toggleForm(); 

        // FORMAT RUPIAH
        const rupiahInputs = document.querySelectorAll('.rupiahe');
        rupiahInputs.forEach(input => {
            input.value = formatRupiah(input.value);
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        });

        function formatRupiah(angka) {
            if(!angka) return '';
            var number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
                split   = number_string.split(','),
                sisa    = split[0].length % 3,
                rupiah  = split[0].substr(0, sisa),
                ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

            if(ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        }
    });
</script>
@endsection