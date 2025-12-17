@extends('layout.master')

@section('content')
{{-- Custom Style --}}
<style>
    .form-label { font-weight: 600; color: #343a40; font-size: 0.9rem; }
    .input-group-text { background-color: #f8f9fa; border-color: #dee2e6; color: #6c757d; }
    .form-control:focus, .form-select:focus { box-shadow: none; border-color: #4B49AC; }
    .card-header-clean { background: transparent; border-bottom: 1px solid #f0f0f0; padding: 20px 25px; }
    .section-title { font-size: 1rem; font-weight: 700; color: #4B49AC; margin-bottom: 15px; display: flex; align-items: center; }
    .section-title i { margin-right: 8px; font-size: 1.2rem; }
    .bg-privilege { background-color: #f0fdf4; border: 1px solid #bbf7d0; }
    .form-switch .form-check-input { width: 3em; height: 1.5em; cursor: pointer; }
</style>

<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card shadow-sm border-0 rounded-4">
            
            <div class="card-header-clean d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-dark"><i class="mdi mdi-account-cash me-2"></i>Setting Master Gaji</h4>
                    <p class="text-muted mb-0 small">Atur komponen gaji untuk: <strong>{{ $user->name }}</strong></p>
                </div>
                <span class="badge bg-primary rounded-pill px-3 py-2">ID: {{ $user->login_id ?? '-' }}</span>
            </div>

            <div class="card-body p-4">
                
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('employee-salaries.update', $user->id) }}" method="POST" id="salaryForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-5">
                        
                        {{-- KIRI: KATEGORI & BANK --}}
                        <div class="col-md-5 border-end">
                            <div class="mb-4">
                                <h6 class="section-title"><i class="mdi mdi-shape"></i> Kategori & Pembayaran</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Kategori Karyawan</label>
                                    <select name="category" id="category" class="form-select form-select-lg shadow-sm border-primary">
                                        <option value="employee" {{ ($user->employeeSalary->category ?? '') == 'employee' ? 'selected' : '' }}>Karyawan Tetap</option>
                                        <option value="promotor" {{ ($user->employeeSalary->category ?? '') == 'promotor' ? 'selected' : '' }}>Promotor / JCS</option>
                                        <option value="freelance" {{ ($user->employeeSalary->category ?? '') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                                    </select>
                                    <div class="form-text text-small" id="cat_desc">Pilih jenis kontrak kerja.</div>
                                </div>

                                <div class="p-3 bg-light rounded-3 border">
                                    <label class="form-label text-muted mb-2"><i class="mdi mdi-bank me-1"></i> Informasi Rekening</label>
                                    <div class="mb-2">
                                        <input type="text" name="bank_name" class="form-control form-control-sm" 
                                               value="{{ $user->employeeSalary->bank_name ?? '' }}" placeholder="Nama Bank (BCA/BRI)">
                                    </div>
                                    <div>
                                        <input type="number" name="bank_account_number" class="form-control form-control-sm" 
                                               value="{{ $user->employeeSalary->bank_account_number ?? '' }}" placeholder="No. Rekening">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KANAN: FORM NOMINAL --}}
                        <div class="col-md-7">
                            
                            {{-- 1. KARYAWAN TETAP --}}
                            <div id="form_employee" class="salary-section">
                                <h6 class="section-title text-success"><i class="mdi mdi-briefcase-check"></i> Komponen Karyawan Tetap</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Gaji Pokok</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="basic_salary" class="form-control rupiahe fw-bold" 
                                               value="{{ number_format($user->employeeSalary->basic_salary ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                </div>

                                <div class="row g-2 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small">Tunjangan Jabatan</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="position_allowance" class="form-control rupiahe" 
                                                   value="{{ number_format($user->employeeSalary->position_allowance ?? 0, 0, ',', '.') }}" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Privilege (Nominal)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="owner_privilege" class="form-control rupiahe" 
                                                   value="{{ number_format($user->employeeSalary->owner_privilege ?? 0, 0, ',', '.') }}" placeholder="0">
                                        </div>
                                    </div>
                                </div>

                                {{-- SWITCH PRIVILEGE (FIXED) --}}
                                <div class="p-3 bg-privilege rounded-3 d-flex align-items-start">
                                    <div class="form-check form-switch pt-1">
                                        {{-- Input Checkbox harus punya value="1" agar dikirim sebagai 1 saat dicentang --}}
                                        <input class="form-check-input" type="checkbox" name="use_privilege_mode" value="1" id="privilegeSwitch"
                                               {{ ($user->employeeSalary->use_privilege_mode ?? 0) == 1 ? 'checked' : '' }}>
                                    </div>
                                    <div class="ms-3">
                                        <label class="form-check-label fw-bold text-success mb-1" for="privilegeSwitch" style="cursor: pointer;">
                                            Aktifkan Mode Privilege
                                        </label>
                                        <p class="mb-0 small text-muted lh-sm">
                                            Jika aktif, karyawan ini <strong>bebas potongan absensi</strong> (Alpha/Telat) saat proses Payroll.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. PROMOTOR --}}
                            <div id="form_promotor" class="salary-section" style="display: none;">
                                <h6 class="section-title text-info"><i class="mdi mdi-bullhorn"></i> Komponen Promotor / JCS</h6>
                                <div class="alert alert-info py-2 small mb-3">
                                    <i class="mdi mdi-information-outline"></i> Hanya menerima <strong>Bonus / Insentif</strong>.
                                </div>
                                <input type="hidden" name="promotor_monthly_salary" value="0">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Insentif / Bonus (Estimasi)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="text" name="promotor_bonus" class="form-control rupiahe fw-bold" 
                                               value="{{ number_format($user->employeeSalary->promotor_bonus ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                </div>
                            </div>

                            {{-- 3. FREELANCE --}}
                            <div id="form_freelance" class="salary-section" style="display: none;">
                                <h6 class="section-title text-warning"><i class="mdi mdi-account-clock"></i> Komponen Freelance</h6>
                                <div class="alert alert-warning py-2 small mb-3">
                                    <i class="mdi mdi-clock-outline"></i> Hitungan <strong>Harian</strong> (No Work No Pay).
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Rate Gaji Per Hari</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="text" name="daily_salary" class="form-control rupiahe fw-bold" 
                                               value="{{ number_format($user->employeeSalary->daily_salary ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('employee-salaries.index') }}" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                            <i class="mdi mdi-content-save-check me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const sections = document.querySelectorAll('.salary-section');
        const catDesc = document.getElementById('cat_desc');
        
        const descriptions = {
            'employee': 'Gaji Pokok + Tunjangan Bulanan.',
            'promotor': 'Hanya Insentif/Bonus Bulanan.',
            'freelance': 'Dibayar harian berdasarkan kehadiran.'
        };

        function toggleForm() {
            const val = categorySelect.value;
            sections.forEach(el => el.style.display = 'none');

            if (val === 'employee') document.getElementById('form_employee').style.display = 'block';
            if (val === 'promotor') document.getElementById('form_promotor').style.display = 'block';
            if (val === 'freelance') document.getElementById('form_freelance').style.display = 'block';

            if(catDesc) catDesc.innerText = descriptions[val] || '';
        }

        categorySelect.addEventListener('change', toggleForm);
        toggleForm(); 

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