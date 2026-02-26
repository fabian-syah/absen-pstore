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
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                            <i class="mdi mdi-account-tie text-primary fs-3"></i>
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold text-muted small text-uppercase mb-1">Nama
                                                Karyawan</label>
                                            <h5 class="fw-bold text-dark mb-0">{{ $user->name }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 ps-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                                            <i class="mdi mdi-office-building text-info fs-3"></i>
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold text-muted small text-uppercase mb-1">Unit
                                                Kerja / Cabang</label>
                                            <div class="fs-5 fw-medium text-dark d-flex align-items-center gap-2">
                                                {{ $user->branch->name ?? 'Pusat' }}
                                                <span
                                                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-3">{{ $user->division->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Input Nominal Section --}}
                        <h5 class="fw-bold text-dark mb-4 border-bottom pb-2"><i
                                class="mdi mdi-cash-register text-primary me-2"></i> Rincian Nominal</h5>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label
                                        class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2">
                                        <span>Nominal Bonus</span>
                                        @if($existingBonus && $existingBonus->bonus_amount > 0)
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2 py-1"><i
                                                    class="mdi mdi-check-circle me-1"></i> Tersimpan</span>
                                        @endif
                                    </label>
                                    <div class="input-group input-group-lg shadow-sm rounded-3">
                                        <span
                                            class="input-group-text bg-light border-end-0 text-muted fw-bold px-4">Rp</span>
                                        <input type="text"
                                            class="form-control border-start-0 ps-0 fw-bold fs-5 text-dark rupiah-input bg-white"
                                            name="bonus_amount" id="bonus_amount" placeholder="0"
                                            value="{{ $existingBonus ? number_format($existingBonus->bonus_amount, 0, '', '.') : '' }}">
                                    </div>
                                    <small class="text-muted mt-2 d-block"><i class="mdi mdi-information-outline"></i>
                                        Kosongkan jika tidak ada bonus.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label
                                        class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2">
                                        <span>Nominal T.H.R</span>
                                        @if($existingBonus && $existingBonus->thr_amount > 0)
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2 py-1"><i
                                                    class="mdi mdi-check-circle me-1"></i> Tersimpan</span>
                                        @endif
                                    </label>
                                    <div class="input-group input-group-lg shadow-sm rounded-3">
                                        <span
                                            class="input-group-text bg-light border-end-0 text-muted fw-bold px-4">Rp</span>
                                        <input type="text"
                                            class="form-control border-start-0 ps-0 fw-bold fs-5 text-dark rupiah-input bg-white"
                                            name="thr_amount" id="thr_amount" placeholder="0"
                                            value="{{ $existingBonus ? number_format($existingBonus->thr_amount, 0, '', '.') : '' }}">
                                    </div>
                                    <small class="text-muted mt-2 d-block"><i class="mdi mdi-information-outline"></i>
                                        Tunjangan Hari Raya tahunan.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Metode Pembayaran Section --}}
                        <div class="bg-light bg-opacity-50 p-4 rounded-4 mb-5 border border-1 border-light-subtle">
                            <label class="form-label fw-bold text-dark mb-3"><i class="mdi mdi-bank text-primary me-2"></i>
                                Opsi Pembayaran</label>
                            <select name="payment_method" id="payment_method"
                                class="form-select form-select-lg shadow-sm border-light-subtle fw-medium text-dark">
                                <option value="cash" {{ ($existingBonus && $existingBonus->payment_method == 'cash') || !$existingBonus ? 'selected' : '' }}>
                                    💵 Pembayaran Tunai (Cash)
                                </option>
                                <option value="transfer" {{ ($existingBonus && $existingBonus->payment_method == 'transfer') ? 'selected' : '' }}>
                                    🏦 Transfer Rekening Bank
                                </option>
                            </select>
                        </div>

                        {{-- Aksi --}}
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-3 pt-4 border-top">
                            <a href="{{ route('branch-salary.show', $user->branch_id ?? 0) }}"
                                class="btn btn-light btn-lg px-5 fw-bold text-dark shadow-sm rounded-pill">
                                <i class="mdi mdi-arrow-left me-2"></i> Batal & Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm rounded-pill">
                                <i class="mdi mdi-content-save-check me-2"></i> Simpan Data
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Format Rupiah Input
            $('.rupiah-input').on('keyup', function (e) {
                let value = $(this).val();
                // Remove non-digit characters
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