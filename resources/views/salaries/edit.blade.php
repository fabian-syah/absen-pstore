@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="mdi mdi-pencil"></i> Edit Payroll Karyawan</h4>
                    <a href="{{ route('salaries.show', $salary->id) }}" class="btn btn-sm btn-light"><i
                            class="mdi mdi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">

                    <form action="{{ route('salaries.update', $salary->id) }}" method="POST" id="payrollForm">
                        @csrf
                        @method('PUT')

                        {{-- HEADER --}}
                        <div class="row mb-4 bg-light p-3 rounded border">
                            <div class="col-md-4">
                                <label class="fw-bold mb-1">Karyawan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i
                                            class="mdi mdi-account"></i></span>
                                    <input type="text" class="form-control fw-bold bg-white text-dark"
                                        value="{{ $salary->user->name }} ({{ $salary->user->branch->name ?? '-' }})"
                                        readonly>
                                </div>
                                <input type="hidden" name="user_id" value="{{ $salary->user->id }}">
                            </div>

                            <div class="col-md-3">
                                <label class="fw-bold mb-1">Periode Gaji</label>
                                <input type="text" class="form-control fw-bold bg-white text-dark text-center"
                                    value="{{ \Carbon\Carbon::create()->month($salary->month)->isoFormat('MMMM') }} {{ $salary->year }}"
                                    readonly>
                                <input type="hidden" name="month" value="{{ $salary->month }}">
                                <input type="hidden" name="year" value="{{ $salary->year }}">
                            </div>

                            <div class="col-md-2">
                                <label class="fw-bold mb-1">Kategori</label>
                                <input type="text" class="form-control fw-bold bg-white text-success text-uppercase"
                                    value="{{ $salary->category }}" readonly>
                                <input type="hidden" name="category" id="category" value="{{ $salary->category }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 border-end">
                                <h5 class="text-success mb-3 fw-bold border-bottom pb-2">
                                    <i class="mdi mdi-arrow-up-circle"></i> PENDAPATAN
                                </h5>

                                {{-- EMPLOYEE --}}
                                <div id="form_employee" class="category-section"
                                    style="display: {{ $salary->category == 'employee' ? 'block' : 'none' }}">
                                    <div class="mb-3">
                                        <label class="fw-bold">Gaji Pokok</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">Rp</span>
                                            <input type="text" name="employee_basic_salary" id="employee_basic"
                                                class="form-control income-input fw-bold text-dark rupiah-input"
                                                value="{{ number_format($salary->employee_basic_salary, 0, ',', '.') }}">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label>Tunjangan Jabatan</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="employee_position_allowance" id="allowance"
                                                class="form-control income-input rupiah-input"
                                                value="{{ number_format($salary->employee_position_allowance, 0, ',', '.') }}">
                                        </div>
                                    </div>
                                    <div class="mb-3 p-3 border rounded bg-light">
                                        <label>Privilege Owner</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="employee_owner_privilege" id="privilege"
                                                class="form-control income-input rupiah-input"
                                                value="{{ number_format($salary->employee_owner_privilege, 0, ',', '.') }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- PROMOTOR --}}
                                <div id="form_promotor" class="category-section"
                                    style="display: {{ $salary->category == 'promotor' ? 'block' : 'none' }}">
                                    <div class="mb-3">
                                        <label class="fw-bold">Insentif Tetap</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">Rp</span>
                                            <input type="text" id="promotor_basic"
                                                class="form-control income-input fw-bold text-dark rupiah-input"
                                                value="{{ number_format($salary->employee_basic_salary, 0, ',', '.') }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- FREELANCE --}}
                                <div id="form_freelance" class="category-section"
                                    style="display: {{ $salary->category == 'freelance' ? 'block' : 'none' }}">
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="fw-bold small">Rate Harian</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light">Rp</span>
                                                <input type="text" name="freelance_daily_salary" id="daily_salary"
                                                    class="form-control rupiah-input fw-bold text-dark"
                                                    value="{{ number_format($salary->employee_basic_salary, 0, ',', '.') }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="fw-bold small">Total Pendapatan Freelance</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" name="freelance_total_income" id="freelance_total_income"
                                                    class="form-control rupiah-input fw-bold text-primary"
                                                    value="{{ number_format($salary->total_amount, 0, ',', '.') }}">
                                                <!-- Freelance amount roughly equals total amount here if no other logic -->
                                            </div>
                                            <small class="text-muted">Edit manual jika perlu</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- GLOBAL --}}
                                <div id="global_income">
                                    <div class="mb-3">
                                        <label>Bonus / Insentif Tambahan</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="promotor_bonus" id="bonus"
                                                class="form-control income-input rupiah-input"
                                                value="{{ number_format($salary->promotor_bonus, 0, ',', '.') }}">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label>Dispensasi</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="dispensation_amount" id="dispensation"
                                                class="form-control income-input rupiah-input"
                                                value="{{ number_format($salary->dispensation_amount, 0, ',', '.') }}">
                                        </div>
                                        <input type="text" name="dispensation_note"
                                            class="form-control form-control-sm mt-1"
                                            value="{{ $salary->dispensation_note }}" placeholder="Catatan...">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center alert alert-success mt-4">
                                    <span class="fw-bold">Total Pendapatan Kotor</span>
                                    <h4 class="mb-0 fw-bold" id="total_income_display">Rp 0</h4>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="text-danger mb-3 fw-bold border-bottom pb-2"><i
                                        class="mdi mdi-arrow-down-circle"></i> POTONGAN</h5>

                                {{-- ALPHA --}}
                                <div class="row mb-2 align-items-center bg-light p-2 rounded mx-0 border">
                                    <div class="col-4">
                                        <label class="small fw-bold mb-0">Alpha (Hari)</label>
                                        <input type="number" name="alpha_days" id="alpha_days"
                                            class="form-control form-control-sm mt-1 fw-bold text-danger"
                                            value="{{ $salary->alpha_days }}">
                                        {{-- REMOVED READONLY TO ENABLE EDITING --}}
                                    </div>
                                    <div class="col-8">
                                        <label class="small text-muted fst-italic mb-0">Rumus: (Fixed / 31) x Alpha</label>
                                        <div class="input-group input-group-sm mt-1">
                                            <span class="input-group-text text-danger bg-white">Rp</span>
                                            <input type="text" name="alpha_deduction" id="alpha_deduction"
                                                class="form-control deduction-input fw-bold text-danger" readonly>
                                        </div>
                                    </div>
                                </div>

                                {{-- TELAT --}}
                                <div class="row mb-3 align-items-center bg-light p-2 rounded mx-0 border">
                                    <div class="col-4">
                                        <label class="small fw-bold mb-0">Telat (Kali)</label>
                                        <input type="number" name="late_days" id="late_days"
                                            class="form-control form-control-sm mt-1 fw-bold text-danger"
                                            value="{{ $salary->late_days }}">
                                        {{-- REMOVED READONLY TO ENABLE EDITING --}}
                                    </div>
                                    <div class="col-8">
                                        <label class="small text-muted fst-italic mb-0">Rumus: (Fixed / 93) x Telat</label>
                                        <div class="input-group input-group-sm mt-1">
                                            <span class="input-group-text text-danger bg-white">Rp</span>
                                            <input type="text" name="late_deduction" id="late_deduction"
                                                class="form-control deduction-input fw-bold text-danger" readonly>
                                        </div>
                                    </div>
                                </div>

                                {{-- LIST HUTANG --}}
                                <div class="mb-3 p-3 border border-warning rounded" style="background-color: #fffbf0;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="fw-bold text-warning mb-0"><i class="mdi mdi-wallet"></i> Potongan
                                            Kasbon</label>
                                    </div>
                                    <div class="alert alert-warning small py-1 mb-2">
                                        Edit kasbon tidak disarankan dari sini. Ubah manual nominal jika perlu.
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text text-danger">Rp</span>
                                        <input type="text" name="kasbon_deduction" id="kasbon_deduction"
                                            class="form-control deduction-input rupiah-input"
                                            value="{{ number_format($salary->kasbon_deduction, 0, ',', '.') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Potongan Lainnya</label>
                                    <div class="input-group">
                                        <span class="input-group-text text-danger">Rp</span>
                                        <input type="text" name="other_deduction" id="other_deduction"
                                            class="form-control deduction-input rupiah-input"
                                            value="{{ number_format($salary->other_deduction, 0, ',', '.') }}">
                                    </div>
                                    <input type="text" name="other_deduction_note" class="form-control form-control-sm mt-1"
                                        value="{{ $salary->other_deduction_note }}" placeholder="Keterangan...">
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
                                        <label class="fw-bold mb-2 text-secondary">Catatan Payroll</label>
                                        <textarea name="notes" class="form-control" rows="2">{{ $salary->notes }}</textarea>
                                    </div>
                                </div>

                                <div class="card bg-white border mb-4">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Konfirmasi Edit</h5>

                                        <div class="alert alert-info small">
                                            Perubahan data di sini akan recalculate Total Gaji. Pastikan data sudah benar.
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <h5 class="text-muted mb-2 text-uppercase ls-1">Take Home Pay (Gaji Bersih)</h5>
                                    <h1 class="display-3 fw-bold text-primary mb-4" id="take_home_pay">Rp 0</h1>
                                    <button type="submit"
                                        class="btn btn-warning btn-lg fw-bold shadow-lg p-3 rounded-pill text-dark">
                                        UPDATE PAYROLL
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
        document.addEventListener('DOMContentLoaded', function () {
            const categoryInput = document.getElementById('category');

            function cleanNumber(value) {
                if (!value) return 0;
                return parseFloat(value.toString().replace(/\./g, '').replace(/,/g, '')) || 0;
            }

            function formatRupiah(angka) {
                let isNegative = false;
                if (angka < 0) { isNegative = true; angka = Math.abs(angka); }
                let number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return (isNegative ? '-' : '') + rupiah;
            }

            document.querySelectorAll('.rupiah-input').forEach(input => {
                input.addEventListener('keyup', function (e) {
                    this.value = formatRupiah(this.value);
                    calculate();
                });
                // Init format on load
                // this.value = formatRupiah(cleanNumber(this.value)); // Already formatted by blade
            });

            // Auto Calculate when Days change
            document.getElementById('alpha_days').addEventListener('input', calculate);
            document.getElementById('late_days').addEventListener('input', calculate);

            function calculate() {
                if (!categoryInput) return;
                let cat = categoryInput.value;
                let totalIncome = 0;
                let totalFixed = 0;

                if (cat === 'freelance') {
                    let freelanceTotal = cleanNumber(document.getElementById('freelance_total_income').value);
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

                // Hitung Potongan
                const alphaDed = document.getElementById('alpha_deduction');
                const lateDed = document.getElementById('late_deduction');
                const kasbonInput = document.getElementById('kasbon_deduction');

                if (cat !== 'freelance') {
                    let aDays = parseFloat(document.getElementById('alpha_days').value) || 0;
                    let lDays = parseFloat(document.getElementById('late_days').value) || 0;

                    let alphaVal = 0;
                    if (totalFixed > 0 && aDays > 0) alphaVal = (totalFixed / 31) * aDays;
                    if (alphaDed) alphaDed.value = formatRupiah(Math.floor(alphaVal));

                    let lateVal = 0;
                    if (totalFixed > 0 && lDays > 0) lateVal = (totalFixed / 93) * lDays;
                    if (lateDed) lateDed.value = formatRupiah(Math.floor(lateVal));
                }

                // Sum Deduction
                let totalDeduction = cleanNumber(alphaDed ? alphaDed.value : 0) +
                    cleanNumber(lateDed ? lateDed.value : 0) +
                    cleanNumber(kasbonInput.value);

                document.querySelectorAll('.deduction-input').forEach(el => {
                    if (el.id !== 'alpha_deduction' && el.id !== 'late_deduction' && el.id !== 'kasbon_deduction') {
                        totalDeduction += cleanNumber(el.value);
                    }
                });

                // Update UI
                document.getElementById('total_income_display').innerText = "Rp " + formatRupiah(totalIncome);
                document.getElementById('total_deduction_display').innerText = "Rp " + formatRupiah(totalDeduction);

                let thp = totalIncome - totalDeduction;
                const thpEl = document.getElementById('take_home_pay');
                thpEl.innerText = "Rp " + formatRupiah(thp);

                if (thp < 0) {
                    thpEl.classList.remove('text-primary');
                    thpEl.classList.add('text-danger');
                } else {
                    thpEl.classList.remove('text-danger');
                    thpEl.classList.add('text-primary');
                }

                // Sync fields for Promotor
                if (cat === 'promotor') {
                    // Sync basic input to hidden employee basic if needed, or just let controller handle
                }
            }

            // Initial Calculation
            calculate();

            // Sync Promotor Basic
            if (categoryInput.value === 'promotor') {
                const promoBasic = document.getElementById('promotor_basic');
                const empBasic = document.getElementById('employee_basic'); // If exists
                if (promoBasic && empBasic) {
                    promoBasic.addEventListener('input', function () {
                        // We might need to sync hidden inputs or similar but for now display logic
                        calculate();
                    });
                }
            }

            document.querySelectorAll('.income-input').forEach(input => {
                input.addEventListener('keyup', calculate);
            });
            document.querySelectorAll('.deduction-input').forEach(input => {
                input.addEventListener('keyup', calculate);
            });
        });
    </script>
@endsection