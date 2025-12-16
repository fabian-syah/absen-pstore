@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="mdi mdi-calculator"></i> Input Payroll Karyawan</h4>
                <span class="badge badge-light text-dark fs-6">{{ date('F Y') }}</span>
            </div>
            <div class="card-body">
                
                {{-- Form Start --}}
                <form action="{{ route('salaries.store') }}" method="POST" id="payrollForm">
                    @csrf
                    
                    {{-- SECTION 1: PILIH USER --}}
                    <div class="row mb-4 bg-light p-3 rounded">
                        <div class="col-md-5">
                            <label class="fw-bold">Pilih Karyawan</label>
                            @if($selectedUser)
                                {{-- Jika user sudah dipilih dari menu sebelumnya --}}
                                <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                                <input type="text" class="form-control fw-bold border-0 bg-white" 
                                       value="{{ $selectedUser->name }} ({{ $selectedUser->branch->name ?? '-' }})" readonly>
                            @else
                                {{-- Dropdown Search --}}
                                <select name="user_id" class="form-control" onchange="window.location.href='{{ route('salaries.create') }}?user_id='+this.value">
                                    <option value="">-- Cari Karyawan --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->branch->name ?? '' }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold">Bulan & Tahun</label>
                            <div class="d-flex">
                                <input type="text" name="month" class="form-control me-1" value="{{ date('m') }}" readonly>
                                <input type="text" name="year" class="form-control" value="{{ date('Y') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold">Kategori Karyawan</label>
                            <select name="category" class="form-control">
                                <option value="employee" selected>Karyawan Tetap</option>
                                <option value="promotor">Promotor</option>
                                <option value="freelance">Freelance</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        {{-- SECTION 2: PENDAPATAN (INCOME) --}}
                        <div class="col-md-6 border-end">
                            <h5 class="text-success mb-3 fw-bold"><i class="mdi mdi-arrow-up-circle"></i> PENDAPATAN</h5>
                            
                            <div class="mb-3">
                                <label>Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="employee_basic_salary" id="basic_salary" class="form-control income-input" placeholder="0" value="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Tunjangan Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="employee_position_allowance" id="allowance" class="form-control income-input" placeholder="0" value="0">
                                </div>
                            </div>

                            <div class="mb-3 p-2 border rounded" style="background-color: #f8f9fa;">
                                <label>Privilege Owner</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="employee_owner_privilege" id="privilege" class="form-control income-input" placeholder="0" value="0">
                                </div>
                                {{-- CHECKBOX ABAIKAN ABSENSI --}}
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="override_attendance">
                                    <label class="form-check-label text-muted small fw-bold" for="override_attendance">
                                        <i class="mdi mdi-shield-check"></i> Privilege User (Jangan Potong Absensi)
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Bonus / Insentif</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="promotor_bonus" id="bonus" class="form-control income-input" placeholder="0" value="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Dispensasi (Manual)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="dispensation_amount" id="dispensation" class="form-control income-input" placeholder="0" value="0">
                                </div>
                                <input type="text" name="dispensation_note" class="form-control form-control-sm mt-1" placeholder="Catatan dispensasi...">
                            </div>

                            <div class="d-flex justify-content-between align-items-center alert alert-success">
                                <span class="fw-bold">Total Pendapatan</span>
                                <h4 class="mb-0 fw-bold" id="total_income_display">Rp 0</h4>
                            </div>
                        </div>

                        {{-- SECTION 3: POTONGAN (DEDUCTION) --}}
                        <div class="col-md-6">
                            <h5 class="text-danger mb-3 fw-bold"><i class="mdi mdi-arrow-down-circle"></i> POTONGAN</h5>

                            {{-- ABSENSI: ALPHA --}}
                            <div class="row mb-2 align-items-center bg-light p-2 rounded mx-0">
                                <div class="col-4">
                                    <label class="small fw-bold">Alpha (Hari)</label>
                                    <input type="number" name="alpha_days" id="alpha_days" class="form-control" value="{{ $alphaCount ?? 0 }}">
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic">Rumus: (Fixed / 31) x Alpha</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text text-danger bg-white">Rp</span>
                                        <input type="number" name="alpha_deduction" id="alpha_deduction" class="form-control deduction-input" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- ABSENSI: TELAT --}}
                            <div class="row mb-3 align-items-center bg-light p-2 rounded mx-0">
                                <div class="col-4">
                                    <label class="small fw-bold">Telat (Kali)</label>
                                    <input type="number" name="late_days" id="late_days" class="form-control" value="{{ $lateCount ?? 0 }}">
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic">Rumus: (Fixed / 93) x Telat</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text text-danger bg-white">Rp</span>
                                        <input type="number" name="late_deduction" id="late_deduction" class="form-control deduction-input" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- KASBON --}}
                            <div class="mb-3 p-3 border border-warning rounded" style="background-color: #fffbf0;">
                                <label class="fw-bold text-warning"><i class="mdi mdi-bank"></i> Potong Hutang (Kasbon)</label>
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-dark">Sisa Hutang Karyawan:</small>
                                    <span class="badge badge-danger fs-6">Rp {{ number_format($remainingDebt ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text text-danger">Rp</span>
                                    <input type="number" name="kasbon_deduction" id="kasbon_deduction" class="form-control deduction-input" 
                                           max="{{ $remainingDebt ?? 0 }}" placeholder="0" value="0">
                                </div>
                                <small class="text-muted d-block mt-1">*Otomatis buat history cicilan jika diisi</small>
                            </div>

                            <div class="mb-3">
                                <label>Potongan Lainnya</label>
                                <div class="input-group">
                                    <span class="input-group-text text-danger">Rp</span>
                                    <input type="number" name="other_deduction" id="other_deduction" class="form-control deduction-input" placeholder="0" value="0">
                                </div>
                                <input type="text" name="other_deduction_note" class="form-control form-control-sm mt-1" placeholder="Keterangan potongan...">
                            </div>

                            <div class="d-flex justify-content-between align-items-center alert alert-danger">
                                <span class="fw-bold">Total Potongan</span>
                                <h4 class="mb-0 fw-bold" id="total_deduction_display">Rp 0</h4>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- TOTAL AKHIR --}}
                    <div class="row justify-content-center text-center">
                        <div class="col-md-8">
                            <h5 class="text-muted mb-2">TAKE HOME PAY (GAJI BERSIH)</h5>
                            <h1 class="display-3 fw-bold text-primary mb-4" id="take_home_pay">Rp 0</h1>
                            
                            <button type="submit" class="btn btn-lg btn-primary w-50 fw-bold shadow">
                                <i class="mdi mdi-content-save"></i> SIMPAN & PROSES
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements Inputs
        const inputs = document.querySelectorAll('input[type="number"]');
        const basicSalary = document.getElementById('basic_salary');
        const allowance = document.getElementById('allowance');
        const privilege = document.getElementById('privilege');
        
        // Elements Alpha & Late
        const alphaDays = document.getElementById('alpha_days');
        const alphaDed = document.getElementById('alpha_deduction');
        const lateDays = document.getElementById('late_days');
        const lateDed = document.getElementById('late_deduction');
        
        // Checkbox
        const overrideCheck = document.getElementById('override_attendance');

        // Fungsi Kalkulasi Utama
        function calculate() {
            // 1. Ambil Nilai Gaji Tetap (Gaji Pokok + Jabatan + Privilege)
            let basic = parseFloat(basicSalary.value) || 0;
            let allow = parseFloat(allowance.value) || 0;
            let priv = parseFloat(privilege.value) || 0;
            
            // Base Salary untuk pembagi rumus
            let totalFixed = basic + allow + priv;

            // 2. Hitung Potongan Absensi
            if (overrideCheck.checked) {
                // Jika Privilege User dicentang, Nol-kan potongan absen
                alphaDed.value = 0;
                lateDed.value = 0;
            } else {
                // RUMUS ALPHA: (Total Fixed / 31) * Jml Hari
                let alphaVal = (totalFixed / 31) * (parseFloat(alphaDays.value) || 0);
                alphaDed.value = Math.floor(alphaVal); 

                // RUMUS TELAT: (Total Fixed / 93) * Jml Kali
                let lateVal = (totalFixed / 93) * (parseFloat(lateDays.value) || 0);
                lateDed.value = Math.floor(lateVal);
            }

            // 3. Hitung Total Pendapatan
            let totalIncome = 0;
            document.querySelectorAll('.income-input').forEach(el => {
                totalIncome += parseFloat(el.value) || 0;
            });

            // 4. Hitung Total Potongan
            let totalDeduction = 0;
            document.querySelectorAll('.deduction-input').forEach(el => {
                totalDeduction += parseFloat(el.value) || 0;
            });

            // 5. Update Tampilan
            document.getElementById('total_income_display').innerText = formatRupiah(totalIncome);
            document.getElementById('total_deduction_display').innerText = formatRupiah(totalDeduction);
            
            // Hitung Gaji Bersih
            let thp = totalIncome - totalDeduction;
            document.getElementById('take_home_pay').innerText = formatRupiah(thp);
        }

        // Helper Format Rupiah JS
        function formatRupiah(angka) {
            return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Event Listeners: Jalankan calculate saat ada angka berubah
        inputs.forEach(input => {
            input.addEventListener('input', calculate);
        });
        overrideCheck.addEventListener('change', calculate);

        // Jalankan sekali saat load
        calculate();
    });
</script>
@endsection