@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="mdi mdi-calculator"></i> Input Payroll Karyawan</h4>
                <span class="badge badge-light text-dark fs-6">{{ date('F Y') }}</span>
            </div>
            <div class="card-body">
                
                <form action="{{ route('salaries.store') }}" method="POST" id="payrollForm">
                    @csrf
                    
                    {{-- SECTION 1 --}}
                    @include('salaries.partials.form_header_create') 
                    {{-- (Isi sama seperti sebelumnya: User, Periode, Kategori) --}}
                    {{-- Saya singkat agar fokus ke JS di bawah --}}

                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h5 class="text-success mb-3 fw-bold border-bottom pb-2">
                                <i class="mdi mdi-arrow-up-circle"></i> PENDAPATAN
                            </h5>
                            
                            {{-- FORM EMPLOYEE --}}
                            <div id="form_employee" class="category-section">
                                <div class="mb-3">
                                    <label class="fw-bold">Gaji Pokok</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="employee_basic_salary" id="basic_salary" 
                                               class="form-control income-input fw-bold text-dark rupiah-input" 
                                               value="{{ number_format($masterSalary->basic_salary ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Tunjangan Jabatan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="employee_position_allowance" id="allowance" 
                                               class="form-control income-input rupiah-input" 
                                               value="{{ number_format($masterSalary->position_allowance ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="mb-3 p-3 border rounded bg-light">
                                    <label>Privilege Owner</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="employee_owner_privilege" id="privilege" 
                                               class="form-control income-input rupiah-input" 
                                               value="{{ number_format($masterSalary->owner_privilege ?? 0, 0, ',', '.') }}">
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="override_attendance">
                                        <label class="form-check-label text-muted small fw-bold cursor-pointer" for="override_attendance">
                                            <i class="mdi mdi-shield-check"></i> Privilege User (Abaikan Potongan Absen)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- FORM PROMOTOR --}}
                            <div id="form_promotor" class="category-section" style="display: none;">
                                <div class="mb-3">
                                    <label class="fw-bold">Gaji 1 Bulan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" id="promotor_basic" class="form-control income-input fw-bold text-dark rupiah-input" 
                                               value="{{ number_format($masterSalary->basic_salary ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- FORM FREELANCE --}}
                            <div id="form_freelance" class="category-section" style="display: none;">
                                <div class="mb-3">
                                    <label class="fw-bold">Gaji Per Hari</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="freelance_daily_salary" id="daily_salary" class="form-control rupiah-input" 
                                               value="{{ number_format($masterSalary->daily_salary ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Jumlah Kehadiran</label>
                                    <input type="text" id="freelance_attendance" class="form-control bg-light" value="{{ $freelanceAttendance ?? 0 }}" readonly>
                                </div>
                                <input type="hidden" id="freelance_total" class="income-input" value="0">
                            </div>

                            {{-- GLOBAL INCOME --}}
                            <div id="global_income">
                                <div class="mb-3">
                                    <label>Bonus / Insentif</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="promotor_bonus" id="bonus" class="form-control income-input rupiah-input" 
                                               value="{{ number_format($masterSalary->promotor_bonus ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Dispensasi</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="dispensation_amount" class="form-control income-input rupiah-input" value="0">
                                    </div>
                                    <input type="text" name="dispensation_note" class="form-control form-control-sm mt-1" placeholder="Catatan...">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center alert alert-success mt-4">
                                <span class="fw-bold">Total Pendapatan</span>
                                <h4 class="mb-0 fw-bold" id="total_income_display">Rp 0</h4>
                            </div>
                        </div>

                        {{-- POTONGAN --}}
                        <div class="col-md-6">
                            <h5 class="text-danger mb-3 fw-bold border-bottom pb-2"><i class="mdi mdi-arrow-down-circle"></i> POTONGAN</h5>

                            <div class="row mb-2 bg-light p-2 rounded mx-0 border">
                                <div class="col-4">
                                    <label class="small fw-bold mb-0">Alpha (Hari)</label>
                                    <input type="number" name="alpha_days" id="alpha_days" class="form-control form-control-sm mt-1 fw-bold text-danger" 
                                           value="{{ $alphaCount ?? 0 }}" readonly>
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic mb-0">Rumus: (Fixed / 31) x Alpha</label>
                                    <div class="input-group input-group-sm mt-1">
                                        <span class="input-group-text text-danger bg-white">Rp</span>
                                        <input type="text" name="alpha_deduction" id="alpha_deduction" class="form-control deduction-input fw-bold text-danger" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 bg-light p-2 rounded mx-0 border">
                                <div class="col-4">
                                    <label class="small fw-bold mb-0">Telat (Kali)</label>
                                    <input type="number" name="late_days" id="late_days" class="form-control form-control-sm mt-1 fw-bold text-danger" 
                                           value="{{ $lateCount ?? 0 }}" readonly>
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic mb-0">Rumus: (Fixed / 93) x Telat</label>
                                    <div class="input-group input-group-sm mt-1">
                                        <span class="input-group-text text-danger bg-white">Rp</span>
                                        <input type="text" name="late_deduction" id="late_deduction" class="form-control deduction-input fw-bold text-danger" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- Kasbon & Lainnya (Sama) --}}
                            @include('salaries.partials.form_deduction_extras')

                            <div class="d-flex justify-content-between align-items-center alert alert-danger mt-4">
                                <span class="fw-bold">Total Potongan</span>
                                <h4 class="mb-0 fw-bold" id="total_deduction_display">Rp 0</h4>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-2">
                    
                    {{-- PAYMENT SECTION (Sama) --}}
                    @include('salaries.partials.payment_section')

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateParams(key, value) {
        let url = new URL(window.location.href);
        url.searchParams.set(key, value);
        window.location.href = url.toString();
    }
    
    function toggleDateInput(show) { 
        const box = document.getElementById('schedule_input_box');
        if(box) box.style.display = show ? 'block' : 'none'; 
    }

    document.addEventListener('DOMContentLoaded', function() {
        const categoryInput = document.getElementById('category');
        const sections = document.querySelectorAll('.category-section');
        
        function toggleCategoryForms() {
            const cat = categoryInput.value;
            sections.forEach(el => el.style.display = 'none');
            
            if(cat === 'employee') document.getElementById('form_employee').style.display = 'block';
            else if(cat === 'promotor') document.getElementById('form_promotor').style.display = 'block';
            else if(cat === 'freelance') document.getElementById('form_freelance').style.display = 'block';
            
            if(cat === 'promotor') {
                const promoBasic = document.getElementById('promotor_basic');
                const empBasic = document.getElementsByName('employee_basic_salary')[0];
                promoBasic.addEventListener('input', function() { empBasic.value = this.value; calculate(); });
                empBasic.value = promoBasic.value;
            }
            calculate();
        }
        
        if(categoryInput.tagName === 'SELECT') categoryInput.addEventListener('change', toggleCategoryForms);
        toggleCategoryForms();

        // --- CORE LOGIC KALKULASI ---
        const rupiahInputs = document.querySelectorAll('.rupiah-input');
        const alphaDays = document.getElementById('alpha_days');
        const alphaDed = document.getElementById('alpha_deduction');
        const lateDays = document.getElementById('late_days');
        const lateDed = document.getElementById('late_deduction');
        const overrideCheck = document.getElementById('override_attendance');
        
        // Freelance
        const dailySalary = document.getElementById('daily_salary');
        const freelanceAtt = document.getElementById('freelance_attendance');
        const freelanceTotal = document.getElementById('freelance_total');

        function cleanNumber(value) {
            if(!value) return 0;
            return parseFloat(value.toString().replace(/\./g, '')) || 0;
        }

        function formatRupiah(angka) {
            var number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa  = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if(ribuan){
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return rupiah;
        }

        rupiahInputs.forEach(input => {
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
                calculate();
            });
        });

        function calculate() {
            let cat = categoryInput.value;
            let totalIncome = 0;
            let totalFixed = 0;

            if (cat === 'freelance') {
                let daily = cleanNumber(dailySalary.value);
                let days = parseFloat(freelanceAtt.value) || 0;
                let total = daily * days;
                freelanceTotal.value = total;
                totalIncome = total;
            } else {
                let basic = cleanNumber(document.getElementsByName('employee_basic_salary')[0].value);
                let allow = cleanNumber(document.getElementsByName('employee_position_allowance')[0].value);
                let priv = cleanNumber(document.getElementsByName('employee_owner_privilege')[0].value);
                
                totalFixed = basic + allow + priv;
                totalIncome = totalFixed;
            }

            totalIncome += cleanNumber(document.getElementsByName('promotor_bonus')[0].value);
            totalIncome += cleanNumber(document.getElementsByName('dispensation_amount')[0].value);

            // Hitung Potongan Absen
            if (cat !== 'freelance') {
                if (overrideCheck && overrideCheck.checked) {
                    alphaDed.value = "0";
                    lateDed.value = "0";
                } else {
                    // Hitung Alpha (Input Days dikali Rumus)
                    let aDays = parseFloat(alphaDays.value) || 0;
                    let lDays = parseFloat(lateDays.value) || 0;
                    
                    let alphaVal = 0;
                    if(totalFixed > 0 && aDays > 0) {
                        alphaVal = (totalFixed / 31) * aDays;
                    }
                    alphaDed.value = formatRupiah(Math.floor(alphaVal)); 

                    let lateVal = 0;
                    if(totalFixed > 0 && lDays > 0) {
                        lateVal = (totalFixed / 93) * lDays;
                    }
                    lateDed.value = formatRupiah(Math.floor(lateVal)); 
                }
            } else {
                alphaDed.value = "0";
                lateDed.value = "0";
            }

            // Hitung Total Potongan
            let totalDeduction = cleanNumber(alphaDed.value) + cleanNumber(lateDed.value);
            document.querySelectorAll('.deduction-input').forEach(el => {
                // Jangan hitung lagi alpha/late karena sudah diambil value-nya diatas
                if(el.id !== 'alpha_deduction' && el.id !== 'late_deduction') {
                    totalDeduction += cleanNumber(el.value);
                }
            });

            // Update UI
            document.getElementById('total_income_display').innerText = "Rp " + formatRupiah(totalIncome);
            document.getElementById('total_deduction_display').innerText = "Rp " + formatRupiah(totalDeduction);
            document.getElementById('take_home_pay').innerText = "Rp " + formatRupiah(totalIncome - totalDeduction);
        }

        if(overrideCheck) overrideCheck.addEventListener('change', calculate);
        
        // JALANKAN SAAT LOAD (FIX UNTUK MENAMPILKAN ANGKA AWAL)
        setTimeout(calculate, 500); // Delay sedikit agar value terisi
    });
</script>
<style>
    .card-radio { transition: all 0.2s; cursor: pointer; }
    .card-radio:hover { background-color: #f8f9fa; }
    .btn-check:checked + .btn-outline-primary { background-color: #0d6efd; color: white; }
    .btn-check:checked + .btn-outline-success { background-color: #198754; color: white; }
</style>
@endsection