@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-modern">
            <div class="card-header bg-white pt-4 px-4 border-0">
                <h4 class="fw-bold text-dark-contrast">Pengaturan Master Gaji</h4>
                <p class="text-muted small">Karyawan: <strong>{{ $user->name }}</strong> ({{ $user->login_id }})</p>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('employee-salaries.update', $user->id) }}" method="POST" id="salaryForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="fw-bold small mb-2">Kategori Karyawan</label>
                        <select name="category" id="categorySelect" class="form-select form-control-clean" required>
                            <option value="employee" {{ optional($user->employeeSalary)->category == 'employee' ? 'selected' : '' }}>Karyawan Tetap</option>
                            <option value="promotor" {{ optional($user->employeeSalary)->category == 'promotor' ? 'selected' : '' }}>Promotor</option>
                            <option value="freelance" {{ optional($user->employeeSalary)->category == 'freelance' ? 'selected' : '' }}>Freelance</option>
                        </select>
                    </div>

                    {{-- Section Karyawan Tetap --}}
                    <div id="section-employee" class="salary-section" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-12 mb-2">
                                <label class="fw-bold small mb-1">Gaji Pokok (Maks. 6.000.000)</label>
                                <input type="text" name="basic_salary" id="basic_salary" class="form-control form-control-clean rupiah" 
                                       value="{{ number_format(optional($user->employeeSalary)->basic_salary ?? 0, 0, ',', '.') }}">
                                <small id="salary-error" class="text-danger fw-bold" style="display:none;">⚠️ Gaji pokok tidak boleh melebihi Rp 6.000.000</small>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold small mb-1">Tunjangan Jabatan</label>
                                <input type="text" name="position_allowance" class="form-control form-control-clean rupiah" 
                                       value="{{ number_format(optional($user->employeeSalary)->position_allowance ?? 0, 0, ',', '.') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold small mb-1">Privilege Owner</label>
                                <input type="text" name="owner_privilege" class="form-control form-control-clean rupiah" 
                                       value="{{ number_format(optional($user->employeeSalary)->owner_privilege ?? 0, 0, ',', '.') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Section Promotor --}}
                    <div id="section-promotor" class="salary-section" style="display:none;">
                        <label class="fw-bold small mb-1">Bonus/Insentif Bulanan</label>
                        <input type="text" name="promotor_bonus" class="form-control form-control-clean rupiah" 
                               value="{{ number_format(optional($user->employeeSalary)->promotor_bonus ?? 0, 0, ',', '.') }}">
                    </div>

                    {{-- Section Freelance --}}
                    <div id="section-freelance" class="salary-section" style="display:none;">
                        <label class="fw-bold small mb-1">Gaji Per Kehadiran (Daily)</label>
                        <input type="text" name="daily_salary" class="form-control form-control-clean rupiah" 
                               value="{{ number_format(optional($user->employeeSalary)->daily_salary ?? 0, 0, ',', '.') }}">
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="fw-bold small mb-1">Nomor Rekening</label>
                        <input type="text" name="bank_account_number" class="form-control form-control-clean" value="{{ optional($user->employeeSalary)->bank_account_number }}">
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" class="form-control form-control-clean" placeholder="Contoh: BCA / Mandiri" value="{{ optional($user->employeeSalary)->bank_name }}">
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small mb-1">Catatan Internal</label>
                        <textarea name="notes" class="form-control form-control-clean" rows="3">{{ optional($user->employeeSalary)->notes }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('employee-salaries.index') }}" class="btn btn-light px-4">Batal</a>
                        <button type="submit" id="btnSubmit" class="btn btn-primary px-5 fw-bold text-white">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('categorySelect');
        const basicSalaryInput = document.getElementById('basic_salary');
        const salaryError = document.getElementById('salary-error');
        const btnSubmit = document.getElementById('btnSubmit');

        function toggleSections() {
            document.querySelectorAll('.salary-section').forEach(s => s.style.display = 'none');
            const selected = categorySelect.value;
            if(selected) document.getElementById('section-' + selected).style.display = 'block';
        }

        // Validasi Gaji Pokok Max 6 Juta
        function validateSalary() {
            if (categorySelect.value === 'employee') {
                const val = parseInt(basicSalaryInput.value.replace(/\./g, '')) || 0;
                if (val > 6000000) {
                    salaryError.style.display = 'block';
                    basicSalaryInput.classList.add('is-invalid');
                    btnSubmit.disabled = true;
                    return false;
                }
            }
            salaryError.style.display = 'none';
            basicSalaryInput.classList.remove('is-invalid');
            btnSubmit.disabled = false;
            return true;
        }

        categorySelect.addEventListener('change', () => { toggleSections(); validateSalary(); });
        basicSalaryInput.addEventListener('input', validateSalary);

        // Masking Rupiah
        document.querySelectorAll('.rupiah').forEach(input => {
            input.addEventListener('keyup', function(e) {
                let value = this.value.replace(/[^,\d]/g, '').toString();
                let split = value.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                this.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            });
        });

        toggleSections();
        validateSalary();
    });
</script>
@endsection