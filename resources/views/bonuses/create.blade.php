@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto grid-margin stretch-card">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="mdi mdi-cash-multiple text-primary fs-3 mb-0"></i>
                        </div>
                        <div>
                            <h4 class="card-title fw-bold text-dark mb-1">Bonus & THR Karyawan</h4>
                            <p class="text-muted mb-0">Input data bonus atau THR untuk profil berikut.</p>
                        </div>
                    </div>

                    <form action="{{ route('bonuses.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">

                        {{-- Profil Info --}}
                        <div class="bg-light p-4 rounded-3 mb-4 border border-light-subtle">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Nama Karyawan</label>
                                    <div class="fs-5 fw-bold text-dark">{{ $user->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Cabang &
                                        Divisi</label>
                                    <div class="fs-5 fw-medium text-dark">
                                        {{ $user->branch->name ?? 'Pusat' }} - <span
                                            class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $user->division->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Input Nominal --}}
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark w-100 d-flex justify-content-between">
                                    Nominal Bonus
                                    @if($existingBonus && $existingBonus->bonus_amount > 0)
                                        <span class="badge bg-success small">Telah diisi: Rp
                                            {{ number_format($existingBonus->bonus_amount, 0, ',', '.') }}</span>
                                    @endif
                                </label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted fw-bold">Rp</span>
                                    <input type="text" class="form-control border-start-0 ps-0 fw-bold rupiah-input"
                                        name="bonus_amount" id="bonus_amount" placeholder="0"
                                        value="{{ $existingBonus ? number_format($existingBonus->bonus_amount, 0, '', '.') : '' }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark w-100 d-flex justify-content-between">
                                    Nominal THR
                                    @if($existingBonus && $existingBonus->thr_amount > 0)
                                        <span class="badge bg-success small">Telah diisi: Rp
                                            {{ number_format($existingBonus->thr_amount, 0, ',', '.') }}</span>
                                    @endif
                                </label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted fw-bold">Rp</span>
                                    <input type="text" class="form-control border-start-0 ps-0 fw-bold rupiah-input"
                                        name="thr_amount" id="thr_amount" placeholder="0"
                                        value="{{ $existingBonus ? number_format($existingBonus->thr_amount, 0, '', '.') : '' }}">
                                </div>
                            </div>
                        </div>

                        {{-- Metode Pembayaran --}}
                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark mb-3">Metode Pembayaran</label>
                            <div class="d-flex gap-4">
                                <div class="form-check form-check-inline custom-radio">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_cash"
                                        value="cash" {{ ($existingBonus && $existingBonus->payment_method == 'cash') || !$existingBonus ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium ms-2" for="pay_cash">
                                        <i class="mdi mdi-cash text-success me-1"></i> Tunai (Cash)
                                    </label>
                                </div>
                                <div class="form-check form-check-inline custom-radio">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_transfer"
                                        value="transfer" {{ ($existingBonus && $existingBonus->payment_method == 'transfer') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium ms-2" for="pay_transfer">
                                        <i class="mdi mdi-bank text-primary me-1"></i> Transfer Bank
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Aksi --}}
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('branch-salary.show', $user->branch_id ?? 0) }}"
                                class="btn btn-light px-4 fw-bold">
                                <i class="mdi mdi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                <i class="mdi mdi-content-save me-1"></i> Simpan Data
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