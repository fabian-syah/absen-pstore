@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="mdi mdi-pencil"></i> Edit Status Payroll</h4>
                <span class="badge badge-light text-dark fs-6">ID: {{ $salary->id }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('salaries.update', $salary->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="mdi mdi-information-outline fs-4 me-2"></i>
                        <div>
                            <strong>Mode Edit:</strong> Anda hanya dapat mengubah <b>Metode Pembayaran</b>, <b>Jadwal Pengiriman</b>, dan <b>Catatan</b>. 
                            <br>Nominal gaji tidak dapat diubah di sini. Jika salah nominal, silakan Hapus data ini dan buat Payroll baru.
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            
                            {{-- CARD INFO GAJI (READONLY) --}}
                            <div class="card bg-light border mb-4">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-3 text-secondary">Rincian Gaji (Readonly)</h5>
                                    
                                    <div class="mb-3">
                                        <label class="small text-muted fw-bold">Total Take Home Pay</label>
                                        <h2 class="display-5 fw-bold text-primary mb-0">Rp {{ number_format($salary->total_amount, 0, ',', '.') }}</h2>
                                        <p class="text-muted small mt-1 mb-0">
                                            Karyawan: <strong>{{ $salary->user->name }}</strong> | Periode: {{ $salary->month }}/{{ $salary->year }}
                                        </p>
                                    </div>

                                    <hr>

                                    {{-- FIELD YANG DISABLED / READONLY --}}
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="small text-muted">Gaji Pokok / Insentif</label>
                                            <input type="text" class="form-control fw-bold bg-white" value="Rp {{ number_format($salary->employee_basic_salary, 0, ',', '.') }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Tunjangan Jabatan</label>
                                            <input type="text" class="form-control fw-bold bg-white" value="Rp {{ number_format($salary->employee_position_allowance, 0, ',', '.') }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Privilege Owner</label>
                                            <input type="text" class="form-control fw-bold bg-white" value="Rp {{ number_format($salary->employee_owner_privilege, 0, ',', '.') }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Bonus / Tambahan</label>
                                            <input type="text" class="form-control fw-bold bg-white" value="Rp {{ number_format($salary->promotor_bonus, 0, ',', '.') }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CARD FORM EDIT (YANG BISA DIEDIT) --}}
                            <div class="card bg-white border mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-4 border-bottom pb-2 text-dark">Update Pengaturan</h5>
                                    
                                    {{-- 1. Metode Pembayaran --}}
                                    <div class="mb-4">
                                        <label class="fw-bold mb-2">Metode Pembayaran</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash" 
                                                {{ $salary->payment_method == 'cash' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-success p-3 fw-bold" for="pay_cash">
                                                <i class="mdi mdi-cash-multiple fs-4 d-block"></i> TUNAI
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_transfer" value="transfer"
                                                {{ $salary->payment_method == 'transfer' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary p-3 fw-bold" for="pay_transfer">
                                                <i class="mdi mdi-bank fs-4 d-block"></i> TRANSFER
                                            </label>
                                        </div>
                                    </div>

                                    {{-- 2. Jadwal --}}
                                    <div class="mb-4">
                                        <label class="fw-bold mb-2">Status & Jadwal</label>
                                        <div class="d-flex flex-column gap-2">
                                            {{-- Opsi Paid --}}
                                            <div class="form-check card-radio p-3 border rounded">
                                                <input class="form-check-input" type="radio" name="send_type" id="send_now" value="now" 
                                                    {{ $salary->status == 'paid' ? 'checked' : '' }} onclick="toggleDate(false)">
                                                <label class="form-check-label fw-bold w-100 cursor-pointer" for="send_now">
                                                    <i class="mdi mdi-check-circle text-success me-1"></i> Sudah Lunas (Paid)
                                                    <small class="d-block text-muted fw-normal">Tandai gaji sudah dikirim/diterima.</small>
                                                </label>
                                            </div>

                                            {{-- Opsi Pending --}}
                                            <div class="form-check card-radio p-3 border rounded">
                                                <input class="form-check-input" type="radio" name="send_type" id="send_later" value="later" 
                                                    {{ $salary->status == 'pending' ? 'checked' : '' }} onclick="toggleDate(true)">
                                                <label class="form-check-label fw-bold w-100 cursor-pointer" for="send_later">
                                                    <i class="mdi mdi-clock text-warning me-1"></i> Jadwalkan (Pending)
                                                </label>
                                                
                                                <div id="date_box" class="mt-2" style="display: {{ $salary->status == 'pending' ? 'block' : 'none' }}">
                                                    <label class="small text-muted">Tanggal Rencana Kirim:</label>
                                                    <input type="date" name="scheduled_date" class="form-control" 
                                                           value="{{ $salary->published_at ? \Carbon\Carbon::parse($salary->published_at)->format('Y-m-d') : '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 3. Catatan (Bisa Diedit) --}}
                                    <div class="mb-2">
                                        <label class="fw-bold mb-2">Catatan Tambahan</label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan opsional...">{{ $salary->notes }}</textarea>
                                    </div>

                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark">
                                    <i class="mdi mdi-content-save"></i> UPDATE DATA
                                </button>
                                <a href="{{ route('branch-salary.show', $salary->user->branch_id) }}" class="btn btn-light">Batal</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDate(show) { document.getElementById('date_box').style.display = show ? 'block' : 'none'; }
</script>

<style>
    .card-radio { transition: all 0.2s; cursor: pointer; }
    .card-radio:hover { background-color: #f8f9fa; }
    .btn-check:checked + .btn-outline-primary { background-color: #0d6efd; color: white; }
    .btn-check:checked + .btn-outline-success { background-color: #198754; color: white; }
    /* Style untuk Readonly Inputs agar terlihat seperti teks biasa yang tegas */
    input:disabled { background-color: #e9ecef !important; opacity: 1; color: #495057; cursor: not-allowed; }
</style>
@endsection