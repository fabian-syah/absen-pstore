@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="mdi mdi-settings"></i> Setting Master Gaji</h4>
                <small>{{ $user->name }} - {{ $user->branch->name ?? 'No Branch' }}</small>
            </div>
            <div class="card-body">
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('employee-salaries.update', $user->id) }}" method="POST" id="salaryForm">
                    @csrf
                    @method('PUT')

                    {{-- 1. PILIH KATEGORI --}}
                    <div class="form-group mb-4">
                        <label class="fw-bold">Kategori Karyawan</label>
                        <select name="category" id="category" class="form-control form-select-lg">
                            <option value="employee" {{ ($user->employeeSalary->category ?? '') == 'employee' ? 'selected' : '' }}>Karyawan Tetap</option>
                            <option value="promotor" {{ ($user->employeeSalary->category ?? '') == 'promotor' ? 'selected' : '' }}>Promotor / JCS Security</option>
                            <option value="freelance" {{ ($user->employeeSalary->category ?? '') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                        </select>
                        <small class="text-muted fst-italic" id="cat_desc">Form akan menyesuaikan kategori yang dipilih.</small>
                    </div>

                    <hr>

                    {{-- 2. FORM KARYAWAN TETAP --}}
                    <div id="form_employee" class="salary-section">
                        <h5 class="text-primary mb-3">Detail Karyawan Tetap</h5>
                        
                        <div class="form-group mb-3">
                            <label>Gaji Pokok (Maksimal Rp 6.000.000)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="basic_salary" class="form-control rupiahe" 
                                       value="{{ $user->employeeSalary->basic_salary ?? 0 }}" placeholder="0">
                            </div>
                            <small class="text-danger">* Wajib diisi (Max 6 Juta)</small>
                        </div>

                        <div class="form-group mb-3">
                            <label>Tunjangan Jabatan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="position_allowance" class="form-control rupiahe" 
                                       value="{{ $user->employeeSalary->position_allowance ?? 0 }}" placeholder="0">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Privilege Owner</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="owner_privilege" class="form-control rupiahe" 
                                       value="{{ $user->employeeSalary->owner_privilege ?? 0 }}" placeholder="0">
                            </div>
                        </div>
                    </div>

                    {{-- 3. FORM PROMOTOR (UPDATED LABELS) --}}
                    <div id="form_promotor" class="salary-section" style="display: none;">
                        <h5 class="text-info mb-3">Detail Promotor</h5>
                        <div class="alert alert-info py-2 small">
                            <i class="mdi mdi-information-outline"></i> Promotor dibayar berdasarkan <strong>Insentif Tetap</strong> dan <strong>Komisi Tambahan</strong>.
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Insentif Tetap (Base Fee Bulanan)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                {{-- Menggunakan field basic_salary di database --}}
                                <input type="text" name="promotor_monthly_salary" class="form-control rupiahe" 
                                       value="{{ ($user->employeeSalary->category ?? '') == 'promotor' ? ($user->employeeSalary->basic_salary ?? 0) : 0 }}" placeholder="0">
                            </div>
                            <small class="text-muted">Uang kehadiran/transport tetap bulanan (jika ada).</small>
                        </div>

                        <div class="form-group mb-3">
                            <label>Insentif Tambahan (Target / Komisi)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="promotor_bonus" class="form-control rupiahe" 
                                       value="{{ $user->employeeSalary->promotor_bonus ?? 0 }}" placeholder="0">
                            </div>
                            <small class="text-muted">Estimasi komisi bulanan (bisa disesuaikan saat payroll).</small>
                        </div>
                    </div>

                    {{-- 4. FORM FREELANCE --}}
                    <div id="form_freelance" class="salary-section" style="display: none;">
                        <h5 class="text-warning mb-3">Detail Freelance</h5>
                        <div class="alert alert-warning text-small">
                            <i class="mdi mdi-information"></i> Total gaji akan dihitung otomatis saat Payroll berdasarkan jumlah kehadiran (Masuk/WFH).
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Gaji Per Hari (Daily Rate)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="daily_salary" class="form-control rupiahe" 
                                       value="{{ $user->employeeSalary->daily_salary ?? 0 }}" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- 5. INFO REKENING --}}
                    <div class="mb-4">
                        <h5 class="text-secondary mb-3">Informasi Pembayaran</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Bank</label>
                                    <input type="text" name="bank_name" class="form-control" 
                                           value="{{ $user->employeeSalary->bank_name ?? '' }}" placeholder="Contoh: BCA">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nomor Rekening</label>
                                    <input type="number" name="bank_account_number" class="form-control" 
                                           value="{{ $user->employeeSalary->bank_account_number ?? '' }}" placeholder="Contoh: 1234567890">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">
                            <i class="mdi mdi-content-save"></i> SIMPAN MASTER GAJI
                        </button>
                        <a href="{{ route('employee-salaries.index') }}" class="btn btn-light">Batal</a>
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
        
        function toggleForm() {
            const val = categorySelect.value;
            sections.forEach(el => el.style.display = 'none');

            if (val === 'employee') document.getElementById('form_employee').style.display = 'block';
            if (val === 'promotor') document.getElementById('form_promotor').style.display = 'block';
            if (val === 'freelance') document.getElementById('form_freelance').style.display = 'block';
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

        function formatRupiah(angka, prefix) {
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

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }
    });
</script>
@endsection