@extends('layout.master')

@section('content')
<style>
    .input-readonly { background-color: #e9ecef !important; cursor: not-allowed; font-weight: bold; color: #495057; }
</style>

<div class="row justify-content-center">
    <div class="col-md-9 grid-margin stretch-card">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0 fw-bold"><i class="mdi mdi-pencil-box-outline me-2"></i>Edit Data Gaji</h4>
                <span class="badge bg-white text-dark border">Periode: {{ $salary->month }} / {{ $salary->year }}</span>
            </div>
            
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="avatar-lg bg-primary rounded-circle text-white d-flex align-items-center justify-content-center fw-bold me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                        {{ substr($salary->user->name, 0, 1) }}
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">{{ $salary->user->name }}</h5>
                        <p class="text-muted mb-0 small">ID: {{ $salary->user->login_id ?? '-' }} | Divisi: {{ $salary->user->division->name ?? '-' }}</p>
                    </div>
                </div>

                <form action="{{ route('salaries.update', $salary->id) }}" method="POST" id="editSalaryForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        {{-- KIRI: KATEGORI --}}
                        <div class="col-md-4 border-end">
                            <label class="fw-bold mb-2">Kategori Gaji</label>
                            <select name="category" id="category" class="form-select mb-3">
                                <option value="employee" {{ $salary->category == 'employee' ? 'selected' : '' }}>Karyawan Tetap</option>
                                <option value="promotor" {{ $salary->category == 'promotor' ? 'selected' : '' }}>Promotor</option>
                                <option value="freelance" {{ $salary->category == 'freelance' ? 'selected' : '' }}>Freelance</option>
                            </select>
                            <div class="alert alert-info py-2 small">
                                <i class="mdi mdi-information-outline"></i> Mengubah kategori akan mereset form di sebelah kanan.
                            </div>
                        </div>

                        {{-- KANAN: FORM NOMINAL --}}
                        <div class="col-md-8">
                            
                            {{-- 1. FORM KARYAWAN BIASA --}}
                            <div id="form-employee" class="salary-section">
                                <h6 class="text-success fw-bold mb-3 border-bottom pb-2">Komponen Karyawan Tetap</h6>
                                
                                <div class="mb-3">
                                    <label class="fw-bold small">Gaji Pokok</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="employee_basic_salary" class="form-control rupiahe" 
                                               value="{{ number_format($salary->employee_basic_salary, 0, ',', '.') }}">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold small text-muted">Tunjangan Jabatan</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">Rp</span>
                                            {{-- READONLY --}}
                                            <input type="text" name="employee_position_allowance" class="form-control rupiahe input-readonly" 
                                                   value="{{ number_format($salary->employee_position_allowance, 0, ',', '.') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold small text-muted">Privilege Owner</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">Rp</span>
                                            {{-- READONLY --}}
                                            <input type="text" name="employee_owner_privilege" class="form-control rupiahe input-readonly" 
                                                   value="{{ number_format($salary->employee_owner_privilege, 0, ',', '.') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. FORM PROMOTOR --}}
                            <div id="form-promotor" class="salary-section" style="display: none;">
                                <h6 class="text-info fw-bold mb-3 border-bottom pb-2">Komponen Promotor</h6>
                                <div class="mb-3">
                                    <label class="fw-bold small">Gaji Pokok 1 Bulan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="promotor_basic_salary" class="form-control rupiahe" 
                                               value="{{ number_format($salary->employee_basic_salary, 0, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold small">Bonus</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="promotor_bonus" class="form-control rupiahe" 
                                               value="{{ number_format($salary->promotor_bonus, 0, ',', '.') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- 3. FORM FREELANCE --}}
                            <div id="form-freelance" class="salary-section" style="display: none;">
                                <h6 class="text-warning fw-bold mb-3 border-bottom pb-2">Komponen Freelance</h6>
                                <div class="mb-3">
                                    <label class="fw-bold small">Gaji Per Hari</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="freelance_daily_salary" class="form-control rupiahe" 
                                               value="{{ number_format($salary->daily_salary ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <div class="form-group mb-0">
                                    <label class="fw-bold small text-secondary">Catatan Payroll (Opsional)</label>
                                    <textarea name="notes" class="form-control bg-light" rows="2">{{ $salary->notes }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('salaries.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-bold text-dark">
                            <i class="mdi mdi-content-save-edit me-1"></i> Update Perubahan
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
        const formPromotor = document.getElementById('form-promotor');
        const formFreelance = document.getElementById('form-freelance');
        const formEmployee = document.getElementById('form-employee');

        // Logic Ganti Form
        function toggleForm() {
            const val = categorySelect.value;
            formPromotor.style.display = 'none';
            formFreelance.style.display = 'none';
            formEmployee.style.display = 'none';

            if(val === 'promotor') formPromotor.style.display = 'block';
            else if(val === 'freelance') formFreelance.style.display = 'block';
            else formEmployee.style.display = 'block';
        }

        categorySelect.addEventListener('change', toggleForm);
        toggleForm(); // Run on load

        // --- FORMAT RUPIAH ---
        const rupiahInputs = document.querySelectorAll('.rupiahe');
        
        // Helper clean
        function cleanNumber(val) {
            return val.replace(/\./g, '');
        }

        // Helper format
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

        // Apply event listener
        rupiahInputs.forEach(input => {
            // Format saat user mengetik
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        });

        // Submit Handler: Clean dots before submit
        document.getElementById('editSalaryForm').addEventListener('submit', function(e) {
            rupiahInputs.forEach(input => {
                input.value = cleanNumber(input.value); // Hapus titik sebelum kirim
            });
        });
    });
</script>
@endsection