@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10 grid-margin stretch-card">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            {{-- Header --}}
            <div class="bg-primary p-4 p-md-5 text-white position-relative">
                <div class="d-flex align-items-center position-relative z-1">
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle me-4 shadow-sm">
                        <i class="mdi mdi-wallet-giftcard text-white fs-1 mb-0"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">Input Bonus & THR</h3>
                        <p class="mb-0 text-white-50 fs-6">Formulir pemberian kompensasi tambahan karyawan</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4 p-md-5 bg-white">

                <form action="{{ route('bonuses.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">

                    {{-- Profil Info Card --}}
                    <div class="bg-light p-4 rounded-4 mb-5 border-0 shadow-sm">
                        <div class="row g-4 align-items-center">
                            <div class="col-md-6 border-end-md">
                                <div>
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-1">Nama Karyawan</label>
                                    <h5 class="fw-bold text-dark mb-0 fs-4">{{ $user->name }}</h5>
                                </div>
                            </div>
                            <div class="col-md-6 ps-md-4">
                                <div>
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-1">Unit Kerja / Cabang</label>
                                    <div class="fs-5 fw-medium text-dark d-flex align-items-center gap-2">
                                        {{ $user->branch->name ?? 'Pusat' }} 
                                        <span class="badge bg-info text-white rounded-pill px-3">{{ $user->division->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Input Nominal Section --}}
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2"><i class="mdi mdi-cash-register text-primary me-2"></i> Rincian Nominal</h5>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold text-dark mb-2">Pilih Kategori</label>
                                <select name="category" id="category_dropdown" class="form-select form-select-lg shadow-sm border-light-subtle fw-medium text-dark focus-ring-primary">
                                    <option value="bonus" selected>🎁 Bonus Karyawan</option>
                                    <option value="thr">🕌 Tunjangan Hari Raya (THR)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2">
                                    <span>Nominal</span>
                                    <span id="saved-badge" class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2 py-1 d-none"><i class="mdi mdi-check-circle me-1"></i> Tersimpan</span>
                                </label>
                                <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden transition-all focus-ring-primary">
                                    <span class="input-group-text bg-light border-end-0 text-muted fw-bold px-4">Rp</span>
                                    <input type="text" class="form-control border-start-0 ps-0 fw-bold fs-5 text-dark rupiah-input bg-white" 
                                        name="amount" id="amount_input" placeholder="0" required>
                                </div>
                                <small class="text-muted mt-2 d-block" id="amount-hint"><i class="mdi mdi-information-outline"></i> Masukkan nominal Bonus.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Metode Pembayaran Section --}}
                    <h6 class="fw-bold text-dark mb-3 mt-4">Konfirmasi Pembayaran</h6>
                    <div class="row mb-5">
                        <div class="col-md-7">
                            <label class="form-label fw-bold text-dark mb-3">Metode Pembayaran</label>
                            <div class="d-flex mb-3">
                                <label class="btn btn-outline-success flex-fill rounded-start py-3 mb-0 border-end-0 payment-toggle {{ ($existingBonus && $existingBonus->payment_method == 'cash') || !$existingBonus ? 'active text-white' : '' }}" id="btn-tunai" for="pay_cash">
                                    <input type="radio" name="payment_method" value="cash" class="d-none" id="pay_cash" {{ ($existingBonus && $existingBonus->payment_method == 'cash') || !$existingBonus ? 'checked' : '' }}>
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <i class="mdi mdi-cash fs-1 mb-1"></i>
                                        <span class="fw-bold">TUNAI</span>
                                    </div>
                                </label>
                                <label class="btn btn-outline-primary flex-fill rounded-end py-3 mb-0 payment-toggle {{ ($existingBonus && $existingBonus->payment_method == 'transfer') ? 'active text-white' : '' }}" id="btn-transfer" for="pay_transfer">
                                    <input type="radio" name="payment_method" value="transfer" class="d-none" id="pay_transfer" {{ ($existingBonus && $existingBonus->payment_method == 'transfer') ? 'checked' : '' }}>
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <i class="mdi mdi-bank fs-1 mb-1"></i>
                                        <span class="fw-bold text-uppercase">Transfer</span>
                                    </div>
                                </label>
                            </div>

                            <div class="d-flex gap-4 {{ ($existingBonus && $existingBonus->payment_method == 'transfer') ? 'd-block' : 'd-none' }}" id="transfer-info">
                                <div class="flex-fill p-3 bg-info bg-opacity-10 rounded border border-info border-opacity-25 mt-2">
                                    <span class="text-secondary small fw-bold d-block mb-1 text-uppercase">REKENING TUJUAN:</span>
                                    <div class="d-flex align-items-center mt-2">
                                        <i class="mdi mdi-credit-card-outline text-primary fs-2 me-2"></i>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $user->employeeSalary->bank_name ?? 'N/A' }} a.n {{ $user->name }}</div>
                                            <div class="fs-6 fw-normal text-muted">{{ $user->employeeSalary->bank_account_number ?? 'Belum ada rekening diatur' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Aksi --}}
                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-3 pt-4 border-top">
                        <a href="{{ route('branch-salary.show', $user->branch_id ?? 0) }}" class="btn btn-light btn-lg px-5 fw-bold text-dark shadow-sm rounded-pill hover-elevate">
                            <i class="mdi mdi-arrow-left me-2"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm rounded-pill hover-elevate">
                            <i class="mdi mdi-content-save-check me-2"></i> Simpan Data
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hover-elevate { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-elevate:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    
    .input-group:focus-within {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
    }
    .input-group:focus-within .input-group-text, .input-group:focus-within .form-control {
        border-color: transparent;
    }
    
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
    }

    .payment-toggle {
        border: 2px solid;
        transition: all 0.3s;
    }
    
    .payment-toggle.btn-outline-success {
        border-color: #dee2e6;
        color: #198754;
    }
    .payment-toggle.btn-outline-success.active {
        background-color: #198754;
        border-color: #198754;
        color: white !important;
    }
    .payment-toggle.btn-outline-success.active i {
        color: white !important;
    }
    
    .payment-toggle.btn-outline-primary {
        border-color: #dee2e6;
        color: #0d6efd;
    }
    .payment-toggle.btn-outline-primary.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white !important;
    }
    .payment-toggle.btn-outline-primary.active i {
        color: white !important;
    }

    @media (min-width: 768px) {
        .border-end-md { border-right: 1px solid #dee2e6; }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Variables for existing data
        let savedBonus = {{ $existingBonus ? $existingBonus->bonus_amount : 0 }};
        let savedThr = {{ $existingBonus ? $existingBonus->thr_amount : 0 }};

        function formatRupiahJs(angka) {
            if (angka == 0) return '';
            let reverse = angka.toString().split('').reverse().join(''),
            ribuan = reverse.match(/\d{1,3}/g);
            if(ribuan) {
                ribuan = ribuan.join('.').split('').reverse().join('');
                return ribuan;
            }
            return '';
        }

        // Handle category change
        $('#category_dropdown').on('change', function() {
            let val = $(this).val();
            if (val === 'bonus') {
                $('#amount_input').val(formatRupiahJs(savedBonus));
                $('#amount-hint').html('<i class="mdi mdi-information-outline"></i> Masukkan nominal Bonus.');
                if(savedBonus > 0) {
                    $('#saved-badge').removeClass('d-none');
                } else {
                    $('#saved-badge').addClass('d-none');
                }
            } else {
                $('#amount_input').val(formatRupiahJs(savedThr));
                $('#amount-hint').html('<i class="mdi mdi-information-outline"></i> Tunjangan Hari Raya tahunan.');
                if(savedThr > 0) {
                    $('#saved-badge').removeClass('d-none');
                } else {
                    $('#saved-badge').addClass('d-none');
                }
            }
        });

        // Trigger on load
        $('#category_dropdown').trigger('change');

        // Payment Method Toggles
        $('input[name="payment_method"]').on('change', function() {
            $('.payment-toggle').removeClass('active text-white');
            if ($('#pay_cash').is(':checked')) {
                $('#btn-tunai').addClass('active text-white');
                $('#transfer-info').addClass('d-none').removeClass('d-block');
            } else {
                $('#btn-transfer').addClass('active text-white');
                $('#transfer-info').removeClass('d-none').addClass('d-block');
            }
        });

        // Format Rupiah Input
        $('.rupiah-input').on('keyup', function(e) {
            let value = $(this).val();
            let cleanValue = value.replace(/[^,\d]/g, '').toString();
            let split = cleanValue.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            $(this).val(rupiah);
        });
    });
</script>
@endpush