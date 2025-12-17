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
                
                {{-- Form Start --}}
                <form action="{{ route('salaries.store') }}" method="POST" id="payrollForm">
                    @csrf
                    
                    {{-- ==================================================== --}}
                    {{-- SECTION 1: PILIH USER & PERIODE --}}
                    {{-- ==================================================== --}}
                    <div class="row mb-4 bg-light p-3 rounded border">
                        <div class="col-md-5">
                            <label class="fw-bold mb-1">Pilih Karyawan</label>
                            @if($selectedUser)
                                <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="mdi mdi-account"></i></span>
                                    <input type="text" class="form-control fw-bold bg-white text-dark" 
                                           value="{{ $selectedUser->name }} ({{ $selectedUser->branch->name ?? '-' }})" readonly>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Jabatan: {{ $selectedUser->division->name ?? '-' }}</small>
                                    <a href="{{ route('salaries.create') }}" class="small text-decoration-none"><i class="mdi mdi-refresh"></i> Reset Pilihan</a>
                                </div>
                            @else
                                <select name="user_id" class="form-control text-dark" onchange="updateParams('user_id', this.value)">
                                    <option value="">-- Cari Karyawan --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->branch->name ?? '' }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Periode Gaji (Cek Absensi)</label>
                            <div class="d-flex gap-2">
                                <select name="month" class="form-select text-center fw-bold text-dark bg-white border-secondary" 
                                        onchange="updateParams('month', this.value)" style="color: #000 !important; opacity: 1;">
                                    @for($m=1; $m<=12; $m++)
                                        <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                                        </option>
                                    @endfor
                                </select>
                                <select name="year" class="form-select text-center fw-bold text-dark bg-white border-secondary" 
                                        onchange="updateParams('year', this.value)" style="color: #000 !important; opacity: 1;">
                                    @for($y=date('Y'); $y>=date('Y')-1; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <small class="text-muted fst-italic" style="font-size: 0.7rem">*Ubah periode untuk refresh hitungan absen</small>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold mb-1">Kategori Karyawan</label>
                            @if(isset($masterSalary) && $masterSalary->category)
                                @php
                                    $catLabel = match($masterSalary->category) {
                                        'employee' => 'Karyawan Tetap',
                                        'promotor' => 'Promotor',
                                        'freelance' => 'Freelance',
                                        default => ucfirst($masterSalary->category)
                                    };
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white"><i class="mdi mdi-check-circle"></i></span>
                                    <input type="text" class="form-control fw-bold bg-white text-success" value="{{ $catLabel }}" readonly>
                                </div>
                                <input type="hidden" name="category" value="{{ $masterSalary->category }}">
                            @else
                                <select name="category" class="form-select text-dark">
                                    <option value="employee">Karyawan Tetap</option>
                                    <option value="promotor">Promotor</option>
                                    <option value="freelance">Freelance</option>
                                </select>
                                <small class="text-danger" style="font-size: 0.7rem">*User ini belum atur Master Gaji</small>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        {{-- ==================================================== --}}
                        {{-- SECTION 2: PENDAPATAN --}}
                        {{-- ==================================================== --}}
                        <div class="col-md-6 border-end">
                            <h5 class="text-success mb-3 fw-bold border-bottom pb-2">
                                <i class="mdi mdi-arrow-up-circle"></i> PENDAPATAN
                            </h5>
                            
                            {{-- Gaji Pokok --}}
                            <div class="mb-3">
                                <label class="fw-bold">Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    {{-- Menggunakan type="text" dan number_format agar ada titiknya --}}
                                    <input type="text" name="employee_basic_salary" id="basic_salary" 
                                           class="form-control income-input fw-bold text-dark rupiah-input" 
                                           placeholder="0" 
                                           value="{{ number_format($masterSalary->basic_salary ?? 0, 0, ',', '.') }}"
                                           {{ (isset($masterSalary) && $masterSalary->category != 'employee') ? 'readonly' : '' }}>
                                </div>
                            </div>

                            {{-- Tunjangan --}}
                            <div class="mb-3">
                                <label>Tunjangan Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="employee_position_allowance" id="allowance" 
                                           class="form-control income-input rupiah-input" 
                                           placeholder="0" 
                                           value="{{ number_format($masterSalary->position_allowance ?? 0, 0, ',', '.') }}">
                                </div>
                            </div>

                            {{-- Privilege --}}
                            <div class="mb-3 p-3 border rounded bg-light">
                                <label>Privilege Owner</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="employee_owner_privilege" id="privilege" 
                                           class="form-control income-input rupiah-input" 
                                           placeholder="0" 
                                           value="{{ number_format($masterSalary->owner_privilege ?? 0, 0, ',', '.') }}">
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="override_attendance">
                                    <label class="form-check-label text-muted small fw-bold cursor-pointer" for="override_attendance">
                                        <i class="mdi mdi-shield-check"></i> Privilege User (Abaikan Potongan Absen)
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Bonus / Insentif</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="promotor_bonus" id="bonus" class="form-control income-input rupiah-input" 
                                           placeholder="0" value="{{ number_format($masterSalary->promotor_bonus ?? 0, 0, ',', '.') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Dispensasi (Manual)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="dispensation_amount" id="dispensation" class="form-control income-input rupiah-input" placeholder="0" value="0">
                                </div>
                                <input type="text" name="dispensation_note" class="form-control form-control-sm mt-1" placeholder="Catatan untuk dispensasi...">
                            </div>

                            <div class="d-flex justify-content-between align-items-center alert alert-success mt-4">
                                <span class="fw-bold">Total Pendapatan Kotor</span>
                                <h4 class="mb-0 fw-bold" id="total_income_display">Rp 0</h4>
                            </div>
                        </div>

                        {{-- ==================================================== --}}
                        {{-- SECTION 3: POTONGAN --}}
                        {{-- ==================================================== --}}
                        <div class="col-md-6">
                            <h5 class="text-danger mb-3 fw-bold border-bottom pb-2">
                                <i class="mdi mdi-arrow-down-circle"></i> POTONGAN
                            </h5>

                            {{-- ABSENSI: ALPHA --}}
                            <div class="row mb-2 align-items-center bg-light p-2 rounded mx-0 border">
                                <div class="col-4">
                                    <label class="small fw-bold mb-0">Alpha (Hari)</label>
                                    <input type="number" name="alpha_days" id="alpha_days" 
                                           class="form-control form-control-sm mt-1 fw-bold text-danger" 
                                           value="{{ $alphaCount ?? 0 }}" readonly>
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic mb-0" style="font-size: 0.75rem">Rumus: (Total Fixed / 31) x Alpha</label>
                                    <div class="input-group input-group-sm mt-1">
                                        <span class="input-group-text text-danger bg-white">Rp</span>
                                        <input type="text" name="alpha_deduction" id="alpha_deduction" class="form-control deduction-input fw-bold text-danger" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- ABSENSI: TELAT --}}
                            <div class="row mb-3 align-items-center bg-light p-2 rounded mx-0 border">
                                <div class="col-4">
                                    <label class="small fw-bold mb-0">Telat (Kali)</label>
                                    <input type="number" name="late_days" id="late_days" 
                                           class="form-control form-control-sm mt-1 fw-bold text-danger" 
                                           value="{{ $lateCount ?? 0 }}" readonly>
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic mb-0" style="font-size: 0.75rem">Rumus: (Total Fixed / 93) x Telat</label>
                                    <div class="input-group input-group-sm mt-1">
                                        <span class="input-group-text text-danger bg-white">Rp</span>
                                        <input type="text" name="late_deduction" id="late_deduction" class="form-control deduction-input fw-bold text-danger" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- KASBON --}}
                            <div class="mb-3 p-3 border border-warning rounded" style="background-color: #fffbf0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="fw-bold text-warning mb-0"><i class="mdi mdi-bank"></i> Potong Hutang</label>
                                    <span class="badge badge-outline-danger">Sisa Hutang: Rp {{ number_format($remainingDebt ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text text-danger">Rp</span>
                                    <input type="text" name="kasbon_deduction" id="kasbon_deduction" 
                                           class="form-control deduction-input rupiah-input" 
                                           data-max="{{ $remainingDebt ?? 0 }}" 
                                           placeholder="0" value="0">
                                </div>
                                @if(($remainingDebt ?? 0) > 0)
                                    <small class="text-muted d-block mt-1 fst-italic text-end">*Cicilan otomatis tercatat di Kasbon</small>
                                @else
                                    <small class="text-success d-block mt-1 fst-italic text-end"><i class="mdi mdi-check"></i> Tidak ada hutang</small>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label>Potongan Lainnya</label>
                                <div class="input-group">
                                    <span class="input-group-text text-danger">Rp</span>
                                    <input type="text" name="other_deduction" id="other_deduction" class="form-control deduction-input rupiah-input" placeholder="0" value="0">
                                </div>
                                <input type="text" name="other_deduction_note" class="form-control form-control-sm mt-1" placeholder="Keterangan potongan lain...">
                            </div>

                            <div class="d-flex justify-content-between align-items-center alert alert-danger mt-4">
                                <span class="fw-bold">Total Potongan</span>
                                <h4 class="mb-0 fw-bold" id="total_deduction_display">Rp 0</h4>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-2">

                    {{-- SECTION 4: GRAND TOTAL --}}
                    <div class="row justify-content-center text-center">
                        <div class="col-md-8">
                            <h5 class="text-muted mb-2 text-uppercase ls-1">Take Home Pay (Gaji Bersih)</h5>
                            <h1 class="display-3 fw-bold text-primary mb-4" id="take_home_pay">Rp 0</h1>
                            
                            <div class="d-grid gap-2 col-6 mx-auto">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm p-3">
                                    <i class="mdi mdi-content-save-check me-1"></i> SIMPAN & PROSES PAYROLL
                                </button>
                                <a href="{{ route('branch-salary.index') }}" class="btn btn-light text-muted">Batal</a>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT --}}
<script>
    function updateParams(key, value) {
        let url = new URL(window.location.href);
        url.searchParams.set(key, value);
        window.location.href = url.toString();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Definisi Elemen ---
        // Kita select class 'rupiah-input'
        const rupiahInputs = document.querySelectorAll('.rupiah-input');
        
        const basicSalary = document.getElementById('basic_salary');
        const allowance = document.getElementById('allowance');
        const privilege = document.getElementById('privilege');
        
        const alphaDays = document.getElementById('alpha_days');
        const alphaDed = document.getElementById('alpha_deduction');
        const lateDays = document.getElementById('late_days');
        const lateDed = document.getElementById('late_deduction');
        
        const overrideCheck = document.getElementById('override_attendance');

        // --- 2. Helper Bersihkan Titik (String -> Float) ---
        function cleanNumber(value) {
            if(!value) return 0;
            // Hapus karakter selain angka
            return parseFloat(value.toString().replace(/\./g, '')) || 0;
        }

        // --- 3. Helper Format Rupiah (Float -> String "2.000.000") ---
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
            
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        // --- 4. Event Listener Input (Format Saat Ketik) ---
        rupiahInputs.forEach(input => {
            input.addEventListener('keyup', function(e) {
                // Saat user ngetik, format valuenya jadi ada titik
                this.value = formatRupiah(this.value);
                calculate(); // Hitung ulang
            });
        });

        // --- 5. Fungsi Kalkulasi Utama ---
        function calculate() {
            let basic = cleanNumber(basicSalary.value);
            let allow = cleanNumber(allowance.value);
            let priv = cleanNumber(privilege.value);
            
            let totalFixed = basic + allow + priv;

            // Hitung Potongan Absen
            if (overrideCheck.checked) {
                alphaDed.value = "0";
                lateDed.value = "0";
            } else {
                let alphaVal = 0;
                if(totalFixed > 0) alphaVal = (totalFixed / 31) * (parseFloat(alphaDays.value) || 0);
                alphaDed.value = formatRupiah(Math.floor(alphaVal)); // Tampilkan dengan titik

                let lateVal = 0;
                if(totalFixed > 0) lateVal = (totalFixed / 93) * (parseFloat(lateDays.value) || 0);
                lateDed.value = formatRupiah(Math.floor(lateVal)); // Tampilkan dengan titik
            }

            // Hitung Total Income
            let totalIncome = 0;
            document.querySelectorAll('.income-input').forEach(el => totalIncome += cleanNumber(el.value));

            // Hitung Total Deduction
            let totalDeduction = 0;
            document.querySelectorAll('.deduction-input').forEach(el => totalDeduction += cleanNumber(el.value));

            // Display
            document.getElementById('total_income_display').innerText = "Rp " + formatRupiah(totalIncome);
            document.getElementById('total_deduction_display').innerText = "Rp " + formatRupiah(totalDeduction);
            document.getElementById('take_home_pay').innerText = "Rp " + formatRupiah(totalIncome - totalDeduction);
        }

        overrideCheck.addEventListener('change', calculate);
        
        // Initial Run
        calculate();
    });
</script>
@endsection