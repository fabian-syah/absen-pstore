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
                
                {{-- FORM FILTER UTAMA (GET REQUEST UNTUK REFRESH DATA) --}}
                {{-- Kita gunakan JS untuk handle refresh parameter --}}
                
                <form action="{{ route('salaries.store') }}" method="POST" id="payrollForm">
                    @csrf
                    
                    {{-- HEADER --}}
                    <div class="row mb-4 bg-light p-3 rounded border">
                        <div class="col-md-4">
                            <label class="fw-bold mb-1">Pilih Karyawan</label>
                            @if($selectedUser)
                                <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="mdi mdi-account"></i></span>
                                    <input type="text" class="form-control fw-bold bg-white text-dark" 
                                           value="{{ $selectedUser->name }} ({{ $selectedUser->branch->name ?? '-' }})" readonly>
                                    <a href="{{ route('users.show', $selectedUser->id) }}" class="btn btn-outline-primary d-flex align-items-center" title="Lihat Profil">
                                        <i class="mdi mdi-account-details"></i>
                                    </a>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Jabatan: {{ $selectedUser->division->name ?? '-' }}</small>
                                    <a href="{{ route('salaries.create') }}" class="small text-decoration-none"><i class="mdi mdi-refresh"></i> Reset</a>
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

                        {{-- PERIODE BULAN (UNTUK EMPLOYEE/PROMOTOR) --}}
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Periode Gaji (Bulanan)</label>
                            <div class="d-flex gap-2">
                                <select name="month" class="form-select text-center fw-bold text-dark border-secondary" 
                                        onchange="updateParams('month', this.value)">
                                    @for($m=1; $m<=12; $m++)
                                        <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                                        </option>
                                    @endfor
                                </select>
                                <select name="year" class="form-select text-center fw-bold text-dark border-secondary" 
                                        onchange="updateParams('year', this.value)">
                                    @for($y=date('Y'); $y>=date('Y')-1; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="fw-bold mb-1">Kategori</label>
                            @php $currentCat = $masterSalary->category ?? 'employee'; @endphp
                            
                            @if(isset($masterSalary) && $masterSalary->category)
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white"><i class="mdi mdi-check-circle"></i></span>
                                    <input type="text" class="form-control fw-bold bg-white text-success" value="{{ ucfirst($currentCat) }}" readonly>
                                </div>
                                <input type="hidden" name="category" id="category" value="{{ $currentCat }}">
                            @else
                                <select name="category" id="category" class="form-select text-dark" onchange="toggleCategoryForms()">
                                    <option value="employee">Karyawan Tetap</option>
                                    <option value="promotor">Promotor</option>
                                    <option value="freelance">Freelance</option>
                                </select>
                            @endif
                        </div>
                        
                        {{-- RANGE TANGGAL (KHUSUS FREELANCE) --}}
                        <div class="col-md-3" id="freelance_date_range" style="display: {{ $currentCat == 'freelance' ? 'block' : 'none' }};">
                            <label class="fw-bold mb-1 text-warning"><i class="mdi mdi-calendar-range"></i> Periode Kerja</label>
                            <div class="input-group input-group-sm">
                                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                                <button type="button" class="btn btn-warning text-dark" onclick="updateDateRange()">Cek</button>
                            </div>
                            <small class="text-muted" style="font-size: 0.65rem">Klik 'Cek' hitung kehadiran.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h5 class="text-success mb-3 fw-bold border-bottom pb-2">
                                <i class="mdi mdi-arrow-up-circle"></i> PENDAPATAN
                            </h5>
                            
                            {{-- EMPLOYEE --}}
                            <div id="form_employee" class="category-section">
                                <div class="mb-3">
                                    <label class="fw-bold">Gaji Pokok (Master)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <input type="text" name="employee_basic_salary" id="employee_basic" 
                                               class="form-control income-input fw-bold text-dark rupiah-input bg-light" 
                                               readonly
                                               value="{{ number_format($masterSalary->basic_salary ?? 0, 0, ',', '.') }}">
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
                                        <input class="form-check-input" type="checkbox" id="override_attendance"
                                               {{ ($masterSalary->use_privilege_mode ?? 0) > 0 ? 'checked' : '' }}>
                                        <label class="form-check-label text-muted small fw-bold cursor-pointer" for="override_attendance">
                                            <i class="mdi mdi-shield-check"></i> Privilege User (Abaikan Potongan Absen)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- PROMOTOR --}}
                            <div id="form_promotor" class="category-section" style="display: none;">
                                <div class="mb-3">
                                    <label class="fw-bold">Insentif Tetap (Master)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <input type="text" id="promotor_basic" class="form-control income-input fw-bold text-dark rupiah-input bg-light" 
                                               readonly
                                               value="{{ number_format($masterSalary->basic_salary ?? 0, 0, ',', '.') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- FREELANCE --}}
                            <div id="form_freelance" class="category-section" style="display: none;">
                                <div class="alert alert-warning py-2 small border-warning bg-soft-warning text-dark mb-3">
                                    <i class="mdi mdi-information-outline me-1"></i> <strong>Mode Freelance:</strong> Gaji dihitung dari jumlah kehadiran dalam rentang tanggal yang dipilih di atas.
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="fw-bold small">Rate Harian</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">Rp</span>
                                            <input type="text" name="freelance_daily_salary" id="daily_salary" 
                                                   class="form-control rupiah-input fw-bold text-dark bg-light" 
                                                   readonly
                                                   value="{{ number_format($masterSalary->daily_salary ?? 0, 0, ',', '.') }}">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="fw-bold small">Jml Hadir</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="freelance_days_count" class="form-control fw-bold text-center text-primary" 
                                                   value="{{ $freelanceAttendance }}" readonly>
                                            <span class="input-group-text">Hari</span>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Hidden input untuk total income freelance yang akan disubmit --}}
                                <input type="hidden" name="freelance_total_income" id="freelance_total_income" value="0">
                                
                                <div class="mb-3 p-2 bg-light rounded border text-center">
                                    <small class="text-muted">Total Gaji Pokok (Rate x Hari)</small>
                                    <h4 class="fw-bold text-primary mb-0" id="freelance_calc_display">Rp 0</h4>
                                </div>
                            </div>

                            {{-- GLOBAL --}}
                            <div id="global_income">
                                <div class="mb-3">
                                    <label>Bonus / Insentif Tambahan</label>
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

                        <div class="col-md-6">
                            <h5 class="text-danger mb-3 fw-bold border-bottom pb-2"><i class="mdi mdi-arrow-down-circle"></i> POTONGAN</h5>

                            {{-- ALPHA --}}
                            <div class="row mb-2 align-items-center bg-light p-2 rounded mx-0 border">
                                <div class="col-4">
                                    <label class="small fw-bold mb-0">Alpha (Hari)</label>
                                    <input type="number" name="alpha_days" id="alpha_days" class="form-control form-control-sm mt-1 fw-bold text-danger" value="{{ $alphaCount ?? 0 }}" readonly>
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
                            <div class="row mb-2 align-items-center bg-light p-2 rounded mx-0 border">
                                <div class="col-4">
                                    <label class="small fw-bold mb-0">Telat (Kali)</label>
                                    <input type="number" name="late_days" id="late_days" class="form-control form-control-sm mt-1 fw-bold text-danger" value="{{ $lateCount ?? 0 }}" readonly>
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic mb-0">Rumus: (Fixed / 93) x Telat</label>
                                    <div class="input-group input-group-sm mt-1">
                                        <span class="input-group-text text-danger bg-white">Rp</span>
                                        <input type="text" name="late_deduction" id="late_deduction" class="form-control deduction-input fw-bold text-danger" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- CUTI LEBIH (Excess Leave - Melebihi Jatah Tahunan) --}}
                            @php $cutiLebihVal = $cutiLebih ?? 0; @endphp
                            <div class="row mb-3 align-items-center p-2 rounded mx-0 border {{ $cutiLebihVal > 0 ? 'bg-danger bg-opacity-10 border-danger' : 'bg-light' }}">
                                <div class="col-4">
                                    <label class="small fw-bold mb-0 {{ $cutiLebihVal > 0 ? 'text-danger' : '' }}">Cuti Lebih (Hari)</label>
                                    <input type="number" name="cuti_lebih_days" id="cuti_lebih_days" class="form-control form-control-sm mt-1 fw-bold {{ $cutiLebihVal > 0 ? 'text-danger' : '' }}" value="{{ $cutiLebihVal }}" readonly>
                                    @if($cutiLebihVal > 0)
                                        <small class="text-danger" style="font-size: 9px;">Melebihi jatah 12 hari/tahun</small>
                                    @else
                                        <small class="text-muted" style="font-size: 9px;">Jatah tahunan: 12 hari</small>
                                    @endif
                                </div>
                                <div class="col-8">
                                    <label class="small text-muted fst-italic mb-0">Rumus: (Fixed / 31) x Cuti Lebih</label>
                                    <div class="input-group input-group-sm mt-1">
                                        <span class="input-group-text {{ $cutiLebihVal > 0 ? 'text-danger' : '' }} bg-white">Rp</span>
                                        <input type="text" name="cuti_lebih_deduction" id="cuti_lebih_deduction" class="form-control deduction-input fw-bold {{ $cutiLebihVal > 0 ? 'text-danger' : '' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- LIST HUTANG --}}
                            <div class="mb-3 p-3 border border-warning rounded" style="background-color: #fffbf0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="fw-bold text-warning mb-0"><i class="mdi mdi-wallet"></i> Bayar Hutang (Kasbon)</label>
                                    <span class="badge badge-outline-danger">Total Sisa: Rp {{ number_format($totalRemainingDebt ?? 0, 0, ',', '.') }}</span>
                                </div>
                                
                                @if($activeLoans && $activeLoans->count() > 0)
                                    <div class="list-group list-group-flush border rounded bg-white mb-2" style="max-height: 200px; overflow-y: auto;">
                                        @foreach($activeLoans as $loan)
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
                                                <div style="flex: 1;">
                                                    <small class="d-block fw-bold text-dark">{{ $loan->description }}</small>
                                                    <small class="text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::parse($loan->created_at)->format('d M Y') }}</small>
                                                </div>
                                                <div class="text-end" style="width: 140px;">
                                                    <small class="d-block text-danger mb-1">Sisa: Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}</small>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="text" name="selected_loans[{{ $loan->id }}]" 
                                                               class="form-control loan-input rupiah-input text-end" 
                                                               placeholder="0" 
                                                               data-max="{{ $loan->remaining_amount }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted fst-italic" style="font-size: 0.7rem">*Isi nominal pada hutang yang ingin dibayar.</small>
                                @else
                                    <div class="alert alert-secondary py-2 small text-center mb-0">Tidak ada hutang aktif.</div>
                                @endif
                                
                                <input type="hidden" name="kasbon_deduction" id="kasbon_deduction" value="0">
                            </div>

                            <div class="mb-3">
                                <label>Potongan Lainnya</label>
                                <div class="input-group">
                                    <span class="input-group-text text-danger">Rp</span>
                                    <input type="text" name="other_deduction" id="other_deduction" class="form-control deduction-input rupiah-input" placeholder="0" value="0">
                                </div>
                                <input type="text" name="other_deduction_note" class="form-control form-control-sm mt-1" placeholder="Keterangan...">
                            </div>

                            <div class="d-flex justify-content-between align-items-center alert alert-danger mt-4">
                                <span class="fw-bold">Total Potongan</span>
                                <h4 class="mb-0 fw-bold" id="total_deduction_display">Rp 0</h4>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-2">
                    
                    {{-- CATATAN & PEMBAYARAN --}}
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div class="card bg-light border mb-4">
                                <div class="card-body p-3">
                                    <label class="fw-bold mb-2 text-secondary"><i class="mdi mdi-note-text-outline"></i> Catatan Payroll (Opsional)</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Bonus Target Lebaran, Potongan Kerusakan Alat, dll..."></textarea>
                                </div>
                            </div>

                            <div class="card bg-white border mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Konfirmasi Pembayaran</h5>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="fw-bold mb-2">Metode Pembayaran</label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash" onclick="toggleBankInfo(false)">
                                                <label class="btn btn-outline-success p-3 fw-bold" for="pay_cash">
                                                    <i class="mdi mdi-cash-multiple fs-4 d-block mb-1"></i> TUNAI
                                                </label>
                                                <input type="radio" class="btn-check" name="payment_method" id="pay_transfer" value="transfer" checked onclick="toggleBankInfo(true)">
                                                <label class="btn btn-outline-primary p-3 fw-bold" for="pay_transfer">
                                                    <i class="mdi mdi-bank fs-4 d-block mb-1"></i> TRANSFER
                                                </label>
                                            </div>
                                            
                                            {{-- Bank Info Section --}}
                                            <div id="bank_info_section" class="mt-3 p-3 bg-soft-info border border-info rounded" style="display: block;">
                                                <small class="text-muted d-block fw-bold text-uppercase mb-1">Rekening Tujuan:</small>
                                                @if($selectedUser && $selectedUser->employeeSalary && $selectedUser->employeeSalary->bank_name)
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-credit-card-outline text-primary fs-4 me-2"></i>
                                                        <div>
                                                            <div class="fw-bold text-dark">{{ $selectedUser->employeeSalary->bank_name }}</div>
                                                            <div class="text-dark font-monospace">{{ $selectedUser->employeeSalary->bank_account_number ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-muted small fst-italic"><i class="mdi mdi-alert-circle-outline"></i> Data rekening karyawan belum diatur.</div>
                                                    @if($selectedUser)
                                                        <a href="{{ route('employee-salaries.edit', $selectedUser->employeeSalary->id ?? 0) }}" target="_blank" class="small text-decoration-none">Atur Rekening</a>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold mb-2">Waktu Pengiriman</label>
                                            <div class="d-flex flex-column gap-2">
                                                <div class="form-check card-radio p-3 border rounded">
                                                    <input class="form-check-input" type="radio" name="send_type" id="send_now" value="now" checked onclick="toggleDateInput(false)">
                                                    <label class="form-check-label fw-bold w-100 cursor-pointer" for="send_now">
                                                        <i class="mdi mdi-send text-primary me-1"></i> Kirim Sekarang
                                                    </label>
                                                </div>
                                                <div class="form-check card-radio p-3 border rounded">
                                                    <input class="form-check-input" type="radio" name="send_type" id="send_later" value="later" onclick="toggleDateInput(true)">
                                                    <label class="form-check-label fw-bold w-100 cursor-pointer" for="send_later">
                                                        <i class="mdi mdi-calendar-clock text-warning me-1"></i> Jadwalkan
                                                    </label>
                                                    <div id="schedule_input_box" class="mt-2" style="display: none;">
                                                        <input type="date" name="scheduled_date" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <h5 class="text-muted mb-2 text-uppercase ls-1">Take Home Pay (Gaji Bersih)</h5>
                                <h1 class="display-3 fw-bold text-primary mb-4" id="take_home_pay">Rp 0</h1>
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-lg p-3 rounded-pill">
                                    PROSES PAYROLL
                                </button>
                            </div>
                        </div>
                    </div>

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
    
    // Fungsi khusus untuk refresh range tanggal
    function updateDateRange() {
        let url = new URL(window.location.href);
        let start = document.getElementById('start_date').value;
        let end = document.getElementById('end_date').value;
        
        if(start && end) {
            url.searchParams.set('start_date', start);
            url.searchParams.set('end_date', end);
            window.location.href = url.toString();
        }
    }
    
    function toggleDateInput(show) { 
        const box = document.getElementById('schedule_input_box');
        if(box) box.style.display = show ? 'block' : 'none'; 
    }

    function toggleBankInfo(show) {
        const box = document.getElementById('bank_info_section');
        if(box) box.style.display = show ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const categoryInput = document.getElementById('category');
        const sections = document.querySelectorAll('.category-section');
        const dateRangeBox = document.getElementById('freelance_date_range');
        
        function toggleCategoryForms() {
            if(!categoryInput) return;
            const cat = categoryInput.value;
            sections.forEach(el => el.style.display = 'none');
            
            if(cat === 'employee') document.getElementById('form_employee').style.display = 'block';
            else if(cat === 'promotor') document.getElementById('form_promotor').style.display = 'block';
            else if(cat === 'freelance') document.getElementById('form_freelance').style.display = 'block';
            
            // Show/Hide Range Date
            if(dateRangeBox) {
                dateRangeBox.style.display = (cat === 'freelance') ? 'block' : 'none';
            }
            
            if(cat === 'promotor') {
                const promoBasic = document.getElementById('promotor_basic');
                const empBasic = document.getElementById('employee_basic');
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

        function cleanNumber(value) {
            if(!value) return 0;
            return parseFloat(value.toString().replace(/\./g, '')) || 0;
        }

        function formatRupiah(angka) {
            let isNegative = false;
            if(angka < 0) { isNegative = true; angka = Math.abs(angka); }
            var number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa  = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if(ribuan){
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return (isNegative ? '-' : '') + rupiah;
        }

        document.querySelectorAll('.rupiah-input').forEach(input => {
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
                calculate();
            });
        });

        // Event Listener untuk Loan List (Multi)
        document.querySelectorAll('.loan-input').forEach(input => {
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
                let max = parseFloat(this.getAttribute('data-max'));
                let current = cleanNumber(this.value);
                if(current > max) {
                    this.value = formatRupiah(max);
                }
                calculate();
            });
        });

        function calculate() {
            if(!categoryInput) return;
            let cat = categoryInput.value;
            let totalIncome = 0;
            let totalFixed = 0; 

            if (cat === 'freelance') {
                let daily = cleanNumber(document.getElementById('daily_salary').value);
                let days = parseFloat(document.getElementById('freelance_days_count').value) || 0;
                
                // Kalkulasi Total Freelance
                let freelanceTotal = daily * days;
                
                // Update Display & Hidden Input
                document.getElementById('freelance_calc_display').innerText = "Rp " + formatRupiah(freelanceTotal);
                document.getElementById('freelance_total_income').value = freelanceTotal;
                
                totalIncome = freelanceTotal; 
                totalFixed = 0; 
            } else if (cat === 'promotor') {
                let promoBasic = cleanNumber(document.getElementById('promotor_basic').value);
                totalFixed = promoBasic;
                totalIncome = promoBasic;
            } else {
                let basic = cleanNumber(document.getElementById('employee_basic').value);
                let allow = cleanNumber(document.getElementById('allowance').value);
                let priv = cleanNumber(document.getElementById('privilege').value);
                totalFixed = basic + allow + priv;
                totalIncome = totalFixed;
            }

            totalIncome += cleanNumber(document.getElementById('bonus').value);
            totalIncome += cleanNumber(document.getElementById('dispensation').value);

            // Hitung Potongan List Hutang
            let totalKasbon = 0;
            document.querySelectorAll('.loan-input').forEach(input => {
                totalKasbon += cleanNumber(input.value);
            });
            document.getElementById('kasbon_deduction').value = totalKasbon;

            // Hitung Potongan
            const alphaDed = document.getElementById('alpha_deduction');
            const lateDed = document.getElementById('late_deduction');
            const overrideCheck = document.getElementById('override_attendance');

            if (cat === 'freelance') {
                if(alphaDed) alphaDed.value = "0";
                if(lateDed) lateDed.value = "0";
                // [BARU] Reset cuti lebih jika freelance
                const cutiLebihDed = document.getElementById('cuti_lebih_deduction');
                if(cutiLebihDed) cutiLebihDed.value = "0";
            } else {
                if (overrideCheck && overrideCheck.checked) {
                    if(alphaDed) alphaDed.value = "0";
                    if(lateDed) lateDed.value = "0";
                    // [BARU] Reset cuti lebih jika privilege
                    const cutiLebihDed = document.getElementById('cuti_lebih_deduction');
                    if(cutiLebihDed) cutiLebihDed.value = "0";
                } else {
                    let aDays = parseFloat(document.getElementById('alpha_days').value) || 0;
                    let lDays = parseFloat(document.getElementById('late_days').value) || 0;
                    
                    let alphaVal = 0;
                    if(totalFixed > 0 && aDays > 0) alphaVal = (totalFixed / 31) * aDays;
                    if(alphaDed) alphaDed.value = formatRupiah(Math.floor(alphaVal)); 

                    let lateVal = 0;
                    if(totalFixed > 0 && lDays > 0) lateVal = (totalFixed / 93) * lDays;
                    if(lateDed) lateDed.value = formatRupiah(Math.floor(lateVal)); 

                    // [BARU] Hitung Cuti Lebih Deduction (Rumus sama seperti Alpha: Fixed/31 x Hari)
                    const cutiLebihDaysEl = document.getElementById('cuti_lebih_days');
                    const cutiLebihDed = document.getElementById('cuti_lebih_deduction');
                    if (cutiLebihDaysEl && cutiLebihDed) {
                        let clDays = parseFloat(cutiLebihDaysEl.value) || 0;
                        let clVal = 0;
                        if(totalFixed > 0 && clDays > 0) clVal = (totalFixed / 31) * clDays;
                        cutiLebihDed.value = formatRupiah(Math.floor(clVal));
                    }
                }
            }

            // Sum Deduction
            let totalDeduction = cleanNumber(alphaDed ? alphaDed.value : 0) + 
                                 cleanNumber(lateDed ? lateDed.value : 0) + 
                                 totalKasbon;

            document.querySelectorAll('.deduction-input').forEach(el => {
                if(el.id !== 'alpha_deduction' && el.id !== 'late_deduction' && el.id !== 'kasbon_deduction') {
                    totalDeduction += cleanNumber(el.value);
                }
            });

            // Update UI
            document.getElementById('total_income_display').innerText = "Rp " + formatRupiah(totalIncome);
            document.getElementById('total_deduction_display').innerText = "Rp " + formatRupiah(totalDeduction);
            
            let thp = totalIncome - totalDeduction;
            const thpEl = document.getElementById('take_home_pay');
            thpEl.innerText = "Rp " + formatRupiah(thp);
            
            if(thp < 0) {
                thpEl.classList.remove('text-primary');
                thpEl.classList.add('text-danger');
            } else {
                thpEl.classList.remove('text-danger');
                thpEl.classList.add('text-primary');
            }
        }

        const overrideCheck = document.getElementById('override_attendance');
        if(overrideCheck) overrideCheck.addEventListener('change', calculate);
        
        setTimeout(calculate, 500);
    });
</script>

<style>
    .bg-soft-info { background-color: rgba(13,202,240,0.15); }
    .bg-soft-warning { background-color: rgba(255,193,7,0.15); }
    .card-radio { transition: all 0.2s; cursor: pointer; }
    .card-radio:hover { background-color: #f8f9fa; }
    .btn-check:checked + .btn-outline-primary { background-color: #0d6efd; color: white; }
    .btn-check:checked + .btn-outline-success { background-color: #198754; color: white; }
</style>
@endsection