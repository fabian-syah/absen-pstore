@extends('layout.master')

@section('content')
{{-- Custom Style --}}
<style>
    .form-label { font-weight: 600; color: #495057; font-size: 0.9rem; margin-bottom: 0.5rem; }
    .input-group-text { background-color: #f8f9fa; border: 1px solid #ced4da; border-right: none; color: #6c757d; font-weight: 500; }
    .form-control, .form-select { border: 1px solid #ced4da; padding: 0.6rem 1rem; font-size: 0.95rem; border-radius: 8px; transition: all 0.2s; }
    .form-control:focus, .form-select:focus { border-color: #4B49AC; box-shadow: 0 0 0 3px rgba(75, 73, 172, 0.1); }
    
    /* Input Rupiah Style */
    .input-group .form-control { border-left: none; }
    .input-group:focus-within .input-group-text { border-color: #4B49AC; }
    
    /* Card Header Clean */
    .card-header-clean { background: transparent; padding: 25px 30px 10px; border: none; }
    .card-body-clean { padding: 10px 30px 30px; }
    
    /* Custom Switch Modern (iOS Style) */
    .switch { position: relative; display: inline-block; width: 50px; height: 26px; vertical-align: middle; margin-right: 10px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e9ecef; -webkit-transition: .4s; transition: .4s; border-radius: 34px; border: 1px solid #ced4da; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; -webkit-transition: .4s; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    input:checked + .slider { background-color: #4B49AC; border-color: #4B49AC; }
    input:checked + .slider:before { -webkit-transform: translateX(24px); -ms-transform: translateX(24px); transform: translateX(24px); }
    
    /* Privilege Box */
    .privilege-box { background: #f8f9fa; border: 1px dashed #ced4da; border-radius: 12px; padding: 15px; transition: all 0.3s; }
    .privilege-box.active { background: #eef2ff; border-color: #4B49AC; }
    
    /* Error Style */
    .is-invalid-custom { border-color: #dc3545 !important; }
    .text-danger-custom { color: #dc3545; font-size: 0.75rem; font-weight: bold; margin-top: 5px; display: none; }
</style>

<div class="row justify-content-center">
    <div class="col-lg-9 col-md-11">
        
        {{-- Tombol Kembali --}}
        <div class="mb-4">
            <a href="{{ route('employee-salaries.index') }}" class="text-decoration-none text-muted fw-bold small">
                <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Daftar Gaji
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            
            {{-- Header --}}
            <div class="card-header-clean d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Setting Master Gaji</h3>
                    <p class="text-muted mb-0">Atur komponen gaji untuk karyawan ini.</p>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold text-primary mb-0">{{ $user->name }}</h5>
                    <span class="badge bg-light text-dark border mt-1">ID: {{ $user->login_id ?? '-' }}</span>
                </div>
            </div>

            <div class="card-body-clean">
                <hr class="my-4 text-muted opacity-25">

                {{-- Alert Error --}}
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4 border-0 shadow-sm">
                        <ul class="mb-0 small ps-3">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger rounded-3 mb-4 border-0 shadow-sm">
                        <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('employee-salaries.update', $user->id) }}" method="POST" id="salaryForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-5">
                        
                        {{-- KIRI: KATEGORI --}}
                        <div class="col-md-5 border-end">
                            <div class="mb-4">
                                <h6 class="fw-bold text-uppercase text-secondary small ls-1 mb-3">Kategori & Pembayaran</h6>
                                
                                <div class="mb-4">
                                    <label class="form-label">Jenis Karyawan</label>
                                    <select name="category" id="category" class="form-select form-select-lg shadow-none">
                                        <option value="employee" {{ ($user->employeeSalary->category ?? '') == 'employee' ? 'selected' : '' }}>Karyawan Tetap</option>
                                        <option value="promotor" {{ ($user->employeeSalary->category ?? '') == 'promotor' ? 'selected' : '' }}>Promotor / JCS</option>
                                        <option value="freelance" {{ ($user->employeeSalary->category ?? '') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                                    </select>
                                    <div class="form-text text-muted mt-2 small lh-sm" id="cat_desc">Pilih jenis kontrak kerja untuk menentukan komponen gaji.</div>
                                </div>

                                <div class="bg-light p-4 rounded-3 border">
                                    <h6 class="fw-bold text-dark mb-3 small"><i class="mdi mdi-bank me-1"></i> Data Rekening</h6>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Nama Bank</label>
                                        <input type="text" name="bank_name" class="form-control form-control-sm" 
                                               value="{{ $user->employeeSalary->bank_name ?? '' }}" placeholder="Contoh: BCA">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small text-muted mb-1">Nomor Rekening</label>
                                        <input type="number" name="bank_account_number" class="form-control form-control-sm" 
                                               value="{{ $user->employeeSalary->bank_account_number ?? '' }}" placeholder="Contoh: 1234567890">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KANAN: FORM NOMINAL --}}
                        <div class="col-md-7">
                            
                            {{-- 1. KARYAWAN TETAP --}}
                            <div id="form_employee" class="salary-section">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box bg-success-light text-success rounded-circle me-2 p-1"><i class="mdi mdi-briefcase-check"></i></div>
                                    <h6 class="fw-bold text-dark mb-0">Komponen Gaji Tetap</h6>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Gaji Pokok</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="basic_salary" id="basic_salary" class="form-control rupiahe fw-bold form-control-lg" 
                                               value="{{ number_format($user->employeeSalary->basic_salary ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                    <div id="salary_warning" class="text-danger-custom">⚠️ Gaji pokok tidak boleh melebihi Rp 6.000.000</div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Tunjangan Jabatan</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="position_allowance" class="form-control rupiahe" 
                                                   value="{{ number_format($user->employeeSalary->position_allowance ?? 0, 0, ',', '.') }}" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Privilege Owner</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="owner_privilege" class="form-control rupiahe" 
                                                   value="{{ number_format($user->employeeSalary->owner_privilege ?? 0, 0, ',', '.') }}" placeholder="0">
                                        </div>
                                    </div>
                                </div>

                                {{-- SWITCH PRIVILEGE MODERN --}}
                                <div class="privilege-box d-flex align-items-center {{ ($user->employeeSalary->use_privilege_mode ?? 0) ? 'active' : '' }}" id="privilegeBox">
                                    <label class="switch">
                                        <input type="checkbox" name="use_privilege_mode" value="1" id="privilegeSwitch"
                                               {{ ($user->employeeSalary->use_privilege_mode ?? 0) ? 'checked' : '' }}
                                               onchange="togglePrivilegeStyle(this)">
                                        <span class="slider"></span>
                                    </label>
                                    <div class="ms-2">
                                        <label class="fw-bold text-dark mb-0 d-block cursor-pointer" for="privilegeSwitch">Aktifkan Mode Privilege</label>
                                        <small class="text-muted lh-1 d-block mt-1" style="font-size: 0.75rem;">Bebas potongan absensi (Alpha/Telat) otomatis.</small>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. PROMOTOR --}}
                            <div id="form_promotor" class="salary-section" style="display: none;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box bg-info-light text-info rounded-circle me-2 p-1"><i class="mdi mdi-bullhorn"></i></div>
                                    <h6 class="fw-bold text-dark mb-0">Komponen Promotor</h6>
                                </div>
                                <div class="alert alert-info border-0 bg-soft-info py-2 px-3 mb-4 rounded-3 d-flex align-items-center">
                                    <i class="mdi mdi-information-outline fs-5 me-2"></i>
                                    <span class="small text-dark">Hanya menerima <strong>Bonus / Insentif</strong>.</span>
                                </div>
                                
                                <input type="hidden" name="promotor_monthly_salary" value="0">
                                <div class="mb-3">
                                    <label class="form-label">Insentif / Bonus (Estimasi)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="promotor_bonus" class="form-control rupiahe fw-bold form-control-lg" 
                                               value="{{ number_format($user->employeeSalary->promotor_bonus ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                    <div class="form-text mt-1">Nominal ini akan muncul otomatis saat Payroll.</div>
                                </div>
                            </div>

                            {{-- 3. FREELANCE --}}
                            <div id="form_freelance" class="salary-section" style="display: none;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box bg-warning-light text-warning rounded-circle me-2 p-1"><i class="mdi mdi-account-clock"></i></div>
                                    <h6 class="fw-bold text-dark mb-0">Komponen Freelance</h6>
                                </div>
                                <div class="alert alert-warning border-0 bg-soft-warning py-2 px-3 mb-4 rounded-3 d-flex align-items-center">
                                    <i class="mdi mdi-clock-outline fs-5 me-2"></i>
                                    <span class="small text-dark">Dibayar <strong>Harian</strong> (No Work No Pay).</span>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Rate Gaji Per Hari</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="daily_salary" class="form-control rupiahe fw-bold form-control-lg" 
                                               value="{{ number_format($user->employeeSalary->daily_salary ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                </div>
                            </div>

                            {{-- FIELD CATATAN (BARU) --}}
                            <div class="mt-4 pt-3 border-top">
                                <label class="form-label small text-secondary mb-2">Catatan Tambahan (Master)</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Kenaikan gaji berkala, Note khusus, dll">{{ $user->employeeSalary->notes ?? '' }}</textarea>
                            </div>

                        </div>
                    </div>

                    <hr class="my-5 opacity-25">

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('employee-salaries.index') }}" class="btn btn-light btn-lg px-4 fw-bold border" style="border-radius: 10px;">Batal</a>
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm text-white" style="border-radius: 10px;">
                            <i class="mdi mdi-content-save-check me-2"></i> Simpan Data
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT --}}
<script>
    function togglePrivilegeStyle(checkbox) {
        const box = document.getElementById('privilegeBox');
        if (checkbox.checked) {
            box.classList.add('active');
        } else {
            box.classList.remove('active');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const sections = document.querySelectorAll('.salary-section');
        const catDesc = document.getElementById('cat_desc');
        const basicSalaryInput = document.getElementById('basic_salary');
        const warningText = document.getElementById('salary_warning');
        const submitBtn = document.getElementById('submitBtn');
        
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
            validateMaxSalary();
        }

        function validateMaxSalary() {
            if(categorySelect.value === 'employee') {
                const rawValue = basicSalaryInput.value.replace(/\./g, '');
                const nominal = parseInt(rawValue) || 0;

                if(nominal > 6000000) {
                    basicSalaryInput.classList.add('is-invalid-custom');
                    warningText.style.display = 'block';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                } else {
                    basicSalaryInput.classList.remove('is-invalid-custom');
                    warningText.style.display = 'none';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                }
            } else {
                basicSalaryInput.classList.remove('is-invalid-custom');
                warningText.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            }
        }

        categorySelect.addEventListener('change', toggleForm);
        basicSalaryInput.addEventListener('keyup', validateMaxSalary);
        toggleForm(); 

        // FORMAT RUPIAH
        const rupiahInputs = document.querySelectorAll('.rupiahe');
        rupiahInputs.forEach(input => {
            input.value = formatRupiah(input.value);
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        });

        function formatRupiah(angka, prefix) {
            if(!angka) return '';
            var number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
                split   = number_string.split(','),
                sisa    = split[0].length % 3,
                rupiah  = split[0].substr(0, sisa),
                ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

            if(ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        }

        // Clean Rupiah on Submit
        document.getElementById('salaryForm').addEventListener('submit', function(e) {
            rupiahInputs.forEach(input => {
                input.value = input.value.replace(/\./g, '');
            });
        });
    });
</script>
@endsection