@extends('layout.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 grid-margin stretch-card">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                {{-- Header --}}
                <div class="bg-white p-4 p-md-5 border-bottom position-relative">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-4">
                            <i class="mdi mdi-gift text-primary fs-2 mb-0"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-1">Input Bonus & THR</h3>
                            <p class="mb-0 text-muted fs-6">Formulir pemberian kompensasi tambahan karyawan</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5 bg-light bg-opacity-50">

                    <form action="{{ route('bonuses.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">

                        {{-- Profil Info Card --}}
                        <div class="bg-white p-4 rounded-4 shadow-sm mb-4 border">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6 border-end-md">
                                    <label class="text-uppercase tracking-wider fw-bold text-muted small mb-1">Nama
                                        Karyawan</label>
                                    <h5 class="fw-bold text-dark mb-0 fs-5">{{ $user->name }}</h5>
                                    <small class="text-secondary">{{ $user->login_id }}</small>
                                </div>
                                <div class="col-md-6 ps-md-4">
                                    <label class="text-uppercase tracking-wider fw-bold text-muted small mb-1">Unit Kerja &
                                        Divisi</label>
                                    <div class="fs-6 fw-medium text-dark d-flex align-items-center gap-2">
                                        <i class="mdi mdi-storefront text-primary"></i>
                                        {{ $user->branch->name ?? 'Pusat' }}
                                        <span
                                            class="badge bg-soft-info text-info rounded-pill px-2 border border-info">{{ $user->division->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Input Nominal Section --}}
                        <div class="bg-white p-4 rounded-4 shadow-sm mb-4 border">
                            <h6 class="fw-bold text-dark mb-4 border-bottom pb-3"><i
                                    class="mdi mdi-cash-register text-primary me-2"></i> Rincian Nominal</h6>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label fw-bold text-dark mb-2">Pilih Kategori</label>
                                        <select name="category" id="category_dropdown"
                                            class="form-select form-select-lg bg-light border-0 shadow-none text-dark focus-ring-primary">
                                            <option value="bonus" selected>🎁 Bonus Karyawan</option>
                                            <option value="thr">🕌 Tunjangan Hari Raya (THR)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label
                                            class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2">
                                            <span>Nominal</span>
                                            <span id="saved-badge"
                                                class="badge bg-success text-white px-2 py-1 rounded-pill d-none shadow-sm"><i
                                                    class="mdi mdi-check-circle me-1"></i> Tersimpan</span>
                                        </label>
                                        <div
                                            class="input-group input-group-lg bg-light rounded-3 overflow-hidden border-0 shadow-none focus-within-ring">
                                            <span
                                                class="input-group-text bg-transparent border-0 text-muted fw-bold ps-3 pe-2">Rp</span>
                                            <input type="text"
                                                class="form-control bg-transparent border-0 ps-1 fw-bold fs-5 text-dark rupiah-input"
                                                name="amount" id="amount_input" placeholder="0" required>
                                        </div>
                                        <small class="text-muted mt-2 d-block" id="amount-hint"><i
                                                class="mdi mdi-information-outline text-primary"></i> Masukkan nominal
                                            Bonus.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Metode Pembayaran Section --}}
                        <div class="bg-white p-4 rounded-4 shadow-sm mb-4 border">
                            <h6 class="fw-bold text-dark mb-4 border-bottom pb-3"><i
                                    class="mdi mdi-credit-card-check text-primary me-2"></i> Konfirmasi Pembayaran</h6>
                            <div class="row">
                                <div class="col-md-8 col-lg-7">
                                    <label class="form-label fw-bold text-dark mb-3">Pilih Metode</label>
                                    <div class="d-flex mb-3 bg-light p-1 rounded-3">
                                        <label
                                            class="btn btn-outline-success border-0 flex-fill py-3 mb-0 payment-toggle rounded {{ ($existingBonus && $existingBonus->payment_method == 'cash') || !$existingBonus ? 'active shadow-sm bg-white text-success' : 'text-muted' }}"
                                            id="btn-tunai" for="pay_cash">
                                            <input type="radio" name="payment_method" value="cash" class="d-none"
                                                id="pay_cash" {{ ($existingBonus && $existingBonus->payment_method == 'cash') || !$existingBonus ? 'checked' : '' }}>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="mdi mdi-cash-multiple fs-4 me-2"></i>
                                                <span class="fw-bold tracking-wider">TUNAI</span>
                                            </div>
                                        </label>
                                        <label
                                            class="btn btn-outline-primary border-0 flex-fill py-3 mb-0 payment-toggle rounded {{ ($existingBonus && $existingBonus->payment_method == 'transfer') ? 'active shadow-sm bg-white text-primary' : 'text-muted' }}"
                                            id="btn-transfer" for="pay_transfer">
                                            <input type="radio" name="payment_method" value="transfer" class="d-none"
                                                id="pay_transfer" {{ ($existingBonus && $existingBonus->payment_method == 'transfer') ? 'checked' : '' }}>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="mdi mdi-bank fs-4 me-2"></i>
                                                <span class="fw-bold tracking-wider">TRANSFER</span>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="mt-3 transition-all {{ ($existingBonus && $existingBonus->payment_method == 'transfer') ? 'd-block' : 'd-none' }}"
                                        id="transfer-info">
                                        <div
                                            class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 d-flex align-items-start gap-3">
                                            <i class="mdi mdi-information-outline text-primary fs-3 mt-1"></i>
                                            <div>
                                                <span
                                                    class="text-primary fw-bold small text-uppercase d-block mb-1">Rekening
                                                    Tujuan Transfer:</span>
                                                <div class="fw-bold text-dark fs-6">
                                                    {{ $user->employeeSalary->bank_name ?? 'N/A' }}</div>
                                                <div class="text-dark">
                                                    {{ $user->employeeSalary->bank_account_number ?? 'Belum ada rekening diatur' }}
                                                    a.n {{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Aksi --}}
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-3 pt-3">
                            <a href="{{ route('branch-salary.show', $user->branch_id ?? 0) }}"
                                class="btn btn-light border btn-lg px-4 fw-bold text-dark hover-elevate rounded-3">
                                Batal & Kembali
                            </a>
                            <button type="submit"
                                class="btn btn-primary btn-lg px-5 fw-bold shadow-sm hover-elevate rounded-3 d-flex align-items-center justify-content-center gap-2">
                                Simpan Data <i class="mdi mdi-content-save"></i>
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
        .tracking-wider {
            letter-spacing: 0.05em;
        }

        .bg-soft-info {
            background-color: rgba(13, 202, 240, 0.1);
        }

        .hover-elevate {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-elevate:hover {
            transform: translateY(-2px);
            box-shadow: 0 .25rem .5rem rgba(0, 0, 0, .1) !important;
        }

        .focus-within-ring:focus-within {
            background-color: #fff !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25) !important;
            border: 1px solid #86b7fe !important;
        }

        .focus-ring-primary:focus {
            background-color: #fff !important;
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25);
        }

        .payment-toggle {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .payment-toggle:hover:not(.active) {
            background-color: rgba(0, 0, 0, .03);
        }

        @media (min-width: 768px) {
            .border-end-md {
                border-right: 1px solid #dee2e6;
            }
        }

        /* Clean inputs */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Variables for existing data
            let savedBonus = {{ $existingBonus ? $existingBonus->bonus_amount : 0 }};
            let savedThr = {{ $existingBonus ? $existingBonus->thr_amount : 0 }};

            function formatRupiahJs(angka) {
                if (angka == 0) return '';
                let reverse = angka.toString().split('').reverse().join(''),
                    ribuan = reverse.match(/\d{1,3}/g);
                if (ribuan) {
                    ribuan = ribuan.join('.').split('').reverse().join('');
                    return ribuan;
                }
                return '';
            }

            // Handle category change
            $('#category_dropdown').on('change', function () {
                let val = $(this).val();
                if (val === 'bonus') {
                    $('#amount_input').val(formatRupiahJs(savedBonus));
                    $('#amount-hint').html('<i class="mdi mdi-information-outline text-primary"></i> Masukkan nominal Bonus bulanan.');
                    if (savedBonus > 0) {
                        $('#saved-badge').removeClass('d-none');
                    } else {
                        $('#saved-badge').addClass('d-none');
                    }
                } else {
                    $('#amount_input').val(formatRupiahJs(savedThr));
                    $('#amount-hint').html('<i class="mdi mdi-information-outline text-primary"></i> Tunjangan Hari Raya tahunan.');
                    if (savedThr > 0) {
                        $('#saved-badge').removeClass('d-none');
                    } else {
                        $('#saved-badge').addClass('d-none');
                    }
                }
            });

            // Trigger on load
            $('#category_dropdown').trigger('change');

            // Payment Method Toggles
            $('input[name="payment_method"]').on('change', function () {
                $('.payment-toggle').removeClass('active shadow-sm bg-white text-success text-primary').addClass('text-muted');
                if ($('#pay_cash').is(':checked')) {
                    $('#btn-tunai').addClass('active shadow-sm bg-white text-success').removeClass('text-muted');
                    $('#transfer-info').slideUp(200);
                } else {
                    $('#btn-transfer').addClass('active shadow-sm bg-white text-primary').removeClass('text-muted');
                    $('#transfer-info').slideDown(200);
                }
            });

            // Format Rupiah Input
            $('.rupiah-input').on('keyup', function (e) {
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