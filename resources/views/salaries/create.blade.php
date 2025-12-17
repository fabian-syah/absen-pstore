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
                    
                    {{-- SECTION 1: HEADER (User, Periode, Kategori) --}}
                    {{-- ... (Bagian HTML Atas TETAP SAMA seperti sebelumnya) ... --}}
                    @include('salaries.partials.form_header_create') 

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
                                        {{-- Tambahkan ID unik untuk Employee Basic --}}
                                        <input type="text" name="employee_basic_salary" id="employee_basic" 
                                               class="form-control income-input fw-bold text-dark rupiah-input" 
                                               placeholder="0" value="{{ number_format($masterSalary->basic_salary ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Tunjangan Jabatan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="employee_position_allowance" id="allowance" 
                                               class="form-control income-input rupiah-input" 
                                               placeholder="0" value="{{ number_format($masterSalary->position_allowance ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="mb-3 p-3 border rounded bg-light">
                                    <label>Privilege Owner</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="employee_owner_privilege" id="privilege" 
                                               class="form-control income-input rupiah-input" 
                                               placeholder="0" value="{{ number_format($masterSalary->owner_privilege ?? 0, 0, ',', '.') }}">
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
                                    <label class="fw-bold">Gaji 1 Bulan (Basic)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" id="promotor_basic" class="form-control income-input fw-bold text-dark rupiah-input" 
                                               placeholder="0" value="{{ number_format($masterSalary->basic_salary ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- FORM FREELANCE --}}
                            <div id="form_freelance" class="category-section" style="display: none;">
                                <div class="alert alert-warning py-2 small">
                                    <i class="mdi mdi-information"></i> Gaji dihitung berdasarkan kehadiran.
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Gaji Per Hari</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="freelance_daily_salary" id="daily_salary" class="form-control rupiah-input" 
                                               placeholder="0" value="{{ number_format($masterSalary->daily_salary ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Jumlah Kehadiran (Realtime)</label>
                                    <input type="text" id="freelance_attendance" class="form-control bg-light" value="{{ $freelanceAttendance ?? 0 }}" readonly>
                                </div>
                                {{-- Hasil kali freelance masuk sini --}}
                                <input type="hidden" id="freelance_total" class="income-input" value="0">
                            </div>

                            {{-- GLOBAL INCOME --}}
                            <div id="global_income">
                                <div class="mb-3">
                                    <label>Bonus / Insentif</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="promotor_bonus" id="bonus" class="form-control income-input rupiah-input" 
                                               placeholder="0" value="{{ number_format($masterSalary->promotor_bonus ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Dispensasi</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="dispensation_amount" id="dispensation" class="form-control income-input rupiah-input" placeholder="0" value="0">
                                    </div>
                                    <input type="text" name="dispensation_note" class="form-control form-control-sm mt-1" placeholder="Catatan...">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center alert alert-success mt-4">
                                <span class="fw-bold">Total Pendapatan Kotor</span>
                                <h4 class="mb-0 fw-bold" id="total_income_display">Rp 0</h4>
                            </div>
                        </div>

                        {{-- SECTION 3: POTONGAN --}}
                        <div class="col-md-6">
                            <h5 class="text-danger mb-3 fw-bold border-bottom pb-2"><i class="mdi mdi-arrow-down-circle"></i> POTONGAN</h5>

                            {{-- ALPHA --}}
                            <div class="row mb-2 align-items-center bg-light p-2 rounded mx-0 border">
                                <div class="col-4">
                                    <label class="small fw-bold mb-0">Alpha (Hari)</label>
                                    <input type="number" name="alpha_days" id="alpha_days" class="form-control form-control-sm mt-1 fw-bold text-danger" 
                                           value="{{ $alphaCount ?? 0 }}">
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic mb-0">Rumus: (Fixed / 31) x Alpha</label>
                                    <div class="input-group input-group-sm mt-1">
                                        <span class="input-group-text text-danger bg-white">Rp</span>
                                        <input type="text" name="alpha_deduction" id="alpha_deduction" class="form-control deduction-input fw-bold text-danger" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- TELAT --}}
                            <div class="row mb-3 align-items-center bg-light p-2 rounded mx-0 border">
                                <div class="col-4">
                                    <label class="small fw-bold mb-0">Telat (Kali)</label>
                                    <input type="number" name="late_days" id="late_days" class="form-control form-control-sm mt-1 fw-bold text-danger" 
                                           value="{{ $lateCount ?? 0 }}">
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic mb-0">Rumus: (Fixed / 93) x Telat</label>
                                    <div class="input-group input-group-sm mt-1">
                                        <span class="input-group-text text-danger bg-white">Rp</span>
                                        <input type="text" name="late_deduction" id="late_deduction" class="form-control deduction-input fw-bold text-danger" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- KASBON & LAINNYA --}}
                            @include('salaries.partials.form_deduction_extras')

                            <div class="d-flex justify-content-between align-items-center alert alert-danger mt-4">
                                <span class="fw-bold">Total Potongan</span>
                                <h4 class="mb-0 fw-bold" id="total_deduction_display">Rp 0</h4>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-2">
                    
                    {{-- PAYMENT SECTION --}}
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
        
        // --- 1. LOGIC GANTI FORM ---
        function toggleCategoryForms() {
            if(!categoryInput) return;
            const cat = categoryInput.value;
            sections.forEach(el => el.style.display = 'none');
            
            if(cat === 'employee') document.getElementById('form_employee').style.display = 'block';
            else if(cat === 'promotor') document.getElementById('form_promotor').style.display = 'block';
            else if(cat === 'freelance') document.getElementById('form_freelance').style.display = 'block';
            
            // Sync Promotor Basic ke Employee Basic (hidden field utama) agar tersimpan
            if(cat === 'promotor') {
                const promoBasic = document.getElementById('promotor_basic');
                const empBasic = document.getElementById('employee_basic'); // Pastikan ID ini ada di input employee
                if(promoBasic && empBasic) {
                    promoBasic.addEventListener('input', function() { 
                        empBasic.value = this.value; 
                        calculate(); 
                    });
                    empBasic.value = promoBasic.value;
                }
            }
            calculate();
        }
        
        if(categoryInput && categoryInput.tagName === 'SELECT') {
            categoryInput.addEventListener('change', toggleCategoryForms);
        }
        toggleCategoryForms();

        // --- 2. FORMAT RUPIAH & HELPER ---
        const rupiahInputs = document.querySelectorAll('.rupiah-input');
        
        function cleanNumber(value) {
            if(!value) return 0;
            // Hapus titik, lalu parse float
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

        // --- 3. CORE CALCULATION LOGIC (FIXED) ---
        function calculate() {
            if(!categoryInput) return;
            let cat = categoryInput.value;
            
            // --- A. Hitung Pendapatan (Income) & Dasar Potongan (Total Fixed) ---
            let totalIncome = 0;
            let totalFixed = 0; // Dasar pengali rumus alpha/telat

            if (cat === 'employee') {
                let basic = cleanNumber(document.getElementById('employee_basic').value);
                let allow = cleanNumber(document.getElementById('allowance').value);
                let priv = cleanNumber(document.getElementById('privilege').value);
                
                totalFixed = basic + allow + priv;
                totalIncome = totalFixed;
                
            } else if (cat === 'promotor') {
                // Promotor: Fixed = Gaji 1 Bulan
                let promoBasic = cleanNumber(document.getElementById('promotor_basic').value);
                totalFixed = promoBasic; 
                totalIncome = promoBasic;

            } else if (cat === 'freelance') {
                let daily = cleanNumber(document.getElementById('daily_salary').value);
                let days = parseFloat(document.getElementById('freelance_attendance').value) || 0;
                
                // Freelance: Income = Gaji Harian x Kehadiran
                totalIncome = daily * days;
                
                // [FIX] Freelance: Fixed Base = Gaji Harian x 31 (Agar rumus /31 valid)
                // Jadi (Daily*31 / 31) * Alpha = Daily * Alpha (Potong 1 hari gaji per alpha)
                totalFixed = daily * 31; 
                
                // Simpan total ke hidden input
                const ft = document.getElementById('freelance_total');
                if(ft) ft.value = totalIncome;
            }

            // Tambah Global Income (Bonus + Dispensasi)
            totalIncome += cleanNumber(document.getElementById('bonus').value);
            totalIncome += cleanNumber(document.getElementById('dispensation').value);


            // --- B. Hitung Potongan (Deduction) ---
            const alphaDays = document.getElementById('alpha_days');
            const alphaDed = document.getElementById('alpha_deduction');
            const lateDays = document.getElementById('late_days');
            const lateDed = document.getElementById('late_deduction');
            const overrideCheck = document.getElementById('override_attendance');

            if (overrideCheck && overrideCheck.checked) {
                alphaDed.value = "0";
                lateDed.value = "0";
            } else {
                // Rumus Alpha: (Total Fixed / 31) * Alpha Days
                let aDays = parseFloat(alphaDays ? alphaDays.value : 0) || 0;
                let alphaVal = 0;
                if(totalFixed > 0 && aDays > 0) {
                    alphaVal = (totalFixed / 31) * aDays;
                }
                alphaDed.value = formatRupiah(Math.floor(alphaVal)); 

                // Rumus Telat: (Total Fixed / 93) * Late Times
                let lDays = parseFloat(lateDays ? lateDays.value : 0) || 0;
                let lateVal = 0;
                if(totalFixed > 0 && lDays > 0) {
                    lateVal = (totalFixed / 93) * lDays;
                }
                lateDed.value = formatRupiah(Math.floor(lateVal)); 
            }

            // --- C. Sum Total ---
            let totalDeduction = cleanNumber(alphaDed.value) + cleanNumber(lateDed.value);
            
            document.querySelectorAll('.deduction-input').forEach(el => {
                // Loop semua input potongan selain alpha/telat (kasbon, lain-lain)
                if(el.id !== 'alpha_deduction' && el.id !== 'late_deduction') {
                    totalDeduction += cleanNumber(el.value);
                }
            });

            // --- D. Update UI ---
            document.getElementById('total_income_display').innerText = "Rp " + formatRupiah(totalIncome);
            document.getElementById('total_deduction_display').innerText = "Rp " + formatRupiah(totalDeduction);
            document.getElementById('take_home_pay').innerText = "Rp " + formatRupiah(totalIncome - totalDeduction);
        }

        // Attach Listeners
        const inputs = document.querySelectorAll('.rupiah-input, input[type="number"]');
        inputs.forEach(input => input.addEventListener('input', calculate));
        
        const overrideCheck = document.getElementById('override_attendance');
        if(overrideCheck) overrideCheck.addEventListener('change', calculate);
        
        // Initial Run (Delay agar value terisi dulu)
        setTimeout(calculate, 500);
    });
</script>
<style>
    /* Styling */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; margin: 0; 
    }
    .card-radio { transition: all 0.2s; cursor: pointer; }
    .card-radio:hover { background-color: #f8f9fa; }
    .btn-check:checked + .btn-outline-primary { background-color: #0d6efd; color: white; }
    .btn-check:checked + .btn-outline-success { background-color: #198754; color: white; }
</style>
@endsection