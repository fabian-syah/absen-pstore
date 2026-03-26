@extends('layout.master')

@section('content')
    <style>
        /* UI Customization */
        .card {
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            transition: transform 0.2s;
        }

        .avatar-square {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            background-color: #eef2ff;
            color: #4b49ac;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        /* Timeline Modern */
        .timeline-wrapper {
            position: relative;
            padding-left: 20px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 2.5rem;
            border-left: 2px solid #e9ecef;
            padding-left: 25px;
        }

        .timeline-item:last-child {
            border-left: transparent;
            padding-bottom: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -9px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid #e9ecef;
            box-shadow: 0 0 0 3px rgba(233, 236, 239, 0.3);
        }

        /* Timeline Colors */
        .timeline-item.approved::before {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .timeline-item.pending::before {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .timeline-item.rejected::before {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        /* Input Nominal Modern */
        .input-nominal-group {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 5px 15px;
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .input-nominal-group:focus-within {
            background: #fff;
            border-color: #4b49ac;
            box-shadow: 0 0 0 4px rgba(75, 73, 172, 0.1);
        }

        .input-nominal-field {
            border: none;
            background: transparent;
            font-weight: 800;
            color: #333;
            font-size: 1.5rem;
            padding: 10px 0;
            outline: none;
            width: 100%;
        }

        /* Thumbnail */
        .thumb-box {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            height: 100px;
            cursor: pointer;
        }

        .thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .thumb-box:hover img {
            transform: scale(1.1);
        }

        /* Tombol Back Bulat Sempurna */
        .btn-back {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
            color: #333;
        }

        .btn-back:hover {
            background: #f8f9fa;
            transform: translateX(-2px);
            color: #4b49ac;
        }

        /* Badge Status Solid */
        .badge-status {
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .badge-active {
            background-color: #cff4fc;
            color: #055160;
            border: 1px solid #b6effb;
        }

        .badge-paid {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .badge-rejected {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
    </style>

    @php
        $divRaw = json_decode($kasbon->division);
        $divisi = json_last_error() === JSON_ERROR_NONE && isset($divRaw->name) ? $divRaw->name : $kasbon->division;
        $branchRaw = json_decode($kasbon->branch);
        $cabang = json_last_error() === JSON_ERROR_NONE && isset($branchRaw->name) ? $branchRaw->name : $kasbon->branch;
        $percent = $kasbon->amount > 0 ? ($kasbon->total_paid / $kasbon->amount) * 100 : 0;
    @endphp

    <div class="container-fluid">

        {{-- ALERTS --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
                <i class="mdi mdi-alert-circle fs-4 me-3"></i>
                <div>
                    <strong class="d-block">Gagal Memproses Data</strong>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
                <i class="mdi mdi-check-circle fs-4 me-3"></i>
                <span class="fw-bold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <a href="{{ route('kasbon.index') }}" class="btn-back me-3"><i class="mdi mdi-arrow-left fs-5"></i></a>
                <div>
                    <h4 class="fw-bold text-dark mb-0">Detail Transaksi #{{ str_pad($kasbon->id, 5, '0', STR_PAD_LEFT) }}
                    </h4>
                    <small class="text-muted">Dibuat pada {{ $kasbon->created_at->format('d F Y, H:i') }}</small>
                </div>
            </div>
            <div>
                @if ($kasbon->status == 'pending')
                    <span class="badge badge-status badge-pending">PENDING APPROVAL</span>
                @elseif($kasbon->status == 'approved')
                    <span class="badge badge-status badge-active">AKTIF BERJALAN</span>
                @elseif($kasbon->status == 'paid')
                    <span class="badge badge-status badge-paid">LUNAS</span>
                @else
                    <span class="badge badge-status badge-rejected">DITOLAK</span>
                @endif
            </div>
        </div>

        <div class="row g-4">
            {{-- KOLOM KIRI --}}
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar-square me-3">{{ substr($kasbon->user_name, 0, 1) }}</div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">{{ $kasbon->user_name }}</h5>
                                <small class="text-muted">{{ $divisi }} <span class="mx-1">•</span>
                                    {{ $cabang }}</small>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded-3 border border-dashed mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small fw-bold">TOTAL PINJAMAN</span>
                                <span class="fw-bold text-dark">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small fw-bold">SUDAH DIBAYAR</span>
                                <span class="fw-bold text-success">- Rp
                                    {{ number_format($kasbon->total_paid, 0, ',', '.') }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-dark fw-bold">SISA HUTANG</span>
                                <span
                                    class="fw-bolder {{ $kasbon->remaining_amount > 0 ? 'text-danger' : 'text-success' }} fs-5">
                                    Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted fw-bold">Progress</span>
                                <span class="fw-bold text-primary">{{ number_format($percent, 0) }}%</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>

                        {{-- RENCANA CICILAN --}}
                        @if($kasbon->monthly_deduction > 0)
                            <div class="mt-4 p-3 rounded-3 border" style="background: #f0fdf4; border-color: #86efac !important;">
                                <h6 class="fw-bold text-success mb-3 small text-uppercase" style="letter-spacing: 0.5px;">
                                    <i class="mdi mdi-calendar-clock me-1"></i> Rencana Cicilan
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-white rounded-3">
                                            <small class="text-muted d-block fw-bold">Per Bulan</small>
                                            <span class="fw-bold text-success">Rp {{ number_format($kasbon->monthly_deduction, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-white rounded-3">
                                            <small class="text-muted d-block fw-bold">Durasi</small>
                                            <span class="fw-bold text-primary">{{ $kasbon->installment_months ?? '-' }} Bulan</span>
                                        </div>
                                    </div>
                                </div>
                                @if($kasbon->remaining_amount > 0 && $kasbon->estimated_payoff_date)
                                    <div class="text-center mt-2 p-2 bg-white rounded-3">
                                        <small class="text-muted d-block fw-bold">Estimasi Lunas</small>
                                        <span class="fw-bold text-dark">
                                            <i class="mdi mdi-flag-checkered me-1"></i>
                                            {{ $kasbon->estimated_payoff_date->format('F Y') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3">Dokumen & Keterangan</h6>
                        <p class="text-muted small bg-light p-3 rounded-3 mb-3">{{ $kasbon->description }}</p>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="thumb-box" onclick="openModal('{{ asset('storage/' . $kasbon->photo_1) }}')"><img
                                        src="{{ asset('storage/' . $kasbon->photo_1) }}"></div>
                            </div>
                            <div class="col-6">
                                <div class="thumb-box" onclick="openModal('{{ asset('storage/' . $kasbon->photo_2) }}')"><img
                                        src="{{ asset('storage/' . $kasbon->photo_2) }}"></div>
                            </div>
                        </div>
                        @if ($kasbon->payment_method == 'transfer')
                            <div class="mt-3">
                                <div class="d-flex align-items-center p-3 rounded-3 shadow-sm border"
                                    style="background-color: #e3f2fd; border-color: #90caf9;">
                                    <div class="bg-white p-2 rounded-circle me-3 text-primary d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="mdi mdi-bank fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-primary fw-bold text-uppercase d-block"
                                            style="letter-spacing: 0.5px;">Rekening Tujuan</small>
                                        <span class="fw-bold text-dark fs-6">{{ $kasbon->account_details }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if (in_array(auth()->user()->role, ['admin', 'admin_gaji']) && $kasbon->status == 'pending')
                    <div class="card mt-4 border-2 border-warning border-start-0 border-end-0 border-bottom-0">
                        <div class="card-body">
                            @if(isset($totalOutstanding) && $totalOutstanding > 0)
                                <div class="bg-warning bg-opacity-10 border border-warning border-opacity-50 p-3 rounded-3 mb-3 d-flex align-items-center">
                                    <i class="mdi mdi-alert-circle text-warning fs-2 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Perhatian!</h6>
                                        <small class="text-dark d-block" style="line-height: 1.4">Karyawan ini masih memiliki tanggungan kasbon aktif sebesar <strong class="text-danger fs-6">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</strong> yang belum dilunasi.</small>
                                    </div>
                                </div>
                            @endif
                            <h6 class="fw-bold text-dark mb-3">Konfirmasi Pengajuan</h6>
                            <form action="{{ route('kasbon.status', $kasbon->id) }}" method="POST">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-6"><button name="status" value="approved"
                                            class="btn btn-dark w-100 fw-bold py-2">SETUJUI</button></div>
                                    <div class="col-6"><button name="status" value="rejected"
                                            class="btn btn-outline-danger w-100 fw-bold py-2">TOLAK</button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            {{-- KOLOM KANAN --}}
            <div class="col-lg-8">

                {{-- FORM BAYAR (USER INPUT) --}}
                @if ($kasbon->status == 'approved' && $kasbon->remaining_amount > 0)
                    <div class="card mb-4 bg-primary text-white overflow-hidden position-relative">
                        <div
                            style="position: absolute; top: -20px; right: -20px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;">
                        </div>

                        <div class="card-body p-4 position-relative">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3"><i
                                        class="mdi mdi-wallet-plus fs-4 text-white"></i></div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-white">Input Pembayaran Cicilan</h5><small
                                        class="text-white text-opacity-75">Masukkan nominal pembayaran yang
                                        diterima.</small>
                                </div>
                            </div>

                            <form action="{{ route('kasbon.pay', $kasbon->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="text-white text-opacity-75 small fw-bold mb-2">Nominal Pembayaran
                                            (Rp)</label>
                                        <div class="input-nominal-group bg-white">
                                            <span class="text-dark fw-bold me-2">Rp</span>
                                            <input type="text" name="amount_paid" id="rupiah"
                                                class="input-nominal-field" placeholder="0" autocomplete="off" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-white text-opacity-75 small fw-bold mb-2">Bukti Transfer</label>
                                        <input type="file" name="payment_proof"
                                            class="form-control form-control-lg border-0" style="padding: 13px;" required>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" name="note"
                                            class="form-control border-0 bg-white bg-opacity-10 text-white placeholder-white py-3"
                                            placeholder="Catatan opsional (Contoh: Potong Gaji Bulan November)...">
                                    </div>
                                    <div class="col-12 text-end mt-3">
                                        <button
                                            class="btn btn-light text-primary fw-bold px-5 py-3 shadow-lg rounded-pill transform-hover">
                                            <i class="mdi mdi-send me-2"></i> KIRIM KONFIRMASI
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- TIMELINE HISTORY (WITH ACTION) --}}
                <div class="card">
                    <div class="card-header bg-white py-4 border-bottom">
                        <h5 class="fw-bold text-dark mb-0">Riwayat Transaksi</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline-wrapper">
                            @forelse($kasbon->installments as $ins)
                                <div class="timeline-item {{ $ins->status }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="d-flex align-items-center mb-1">
                                                <h6 class="fw-bold text-dark mb-0 me-2">Pembayaran Cicilan</h6>
                                                @if ($ins->status == 'pending')
                                                    <span class="badge bg-warning text-dark small">Menunggu
                                                        Verifikasi</span>
                                                @elseif($ins->status == 'approved')
                                                    <span class="badge bg-success small">Diterima</span>
                                                @elseif($ins->status == 'rejected')
                                                    <span class="badge bg-danger small">Ditolak</span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center text-muted small mb-2">
                                                <i class="mdi mdi-calendar-blank me-1"></i>
                                                {{ $ins->created_at->format('d M Y, H:i') }}
                                                <span class="mx-2">•</span>
                                                <i class="mdi mdi-account-circle-outline me-1"></i> {{ $ins->user->name }}
                                            </div>
                                            @if ($ins->note)
                                                <div
                                                    class="d-inline-block bg-light px-3 py-1 rounded-pill small border text-muted">
                                                    {{ $ins->note }}</div>
                                            @endif

                                            {{-- ADMIN ACTION BUTTONS --}}
                                            @if ($ins->status == 'pending' && in_array(auth()->user()->role, ['admin', 'admin_gaji']))
                                                <div class="mt-2 d-flex gap-2">
                                                    <form action="{{ route('kasbon.installment.approve', $ins->id) }}"
                                                        method="POST">
                                                        @csrf <button
                                                            class="btn btn-sm btn-success fw-bold px-3 rounded-pill"><i
                                                                class="mdi mdi-check me-1"></i> Terima</button>
                                                    </form>

                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger fw-bold px-3 rounded-pill"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#rejectModal{{ $ins->id }}">
                                                        <i class="mdi mdi-close me-1"></i> Tolak
                                                    </button>

                                                    <div class="modal fade" id="rejectModal{{ $ins->id }}"
                                                        tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <form
                                                                action="{{ route('kasbon.installment.reject', $ins->id) }}"
                                                                method="POST" class="modal-content">
                                                                @csrf
                                                                <div class="modal-body p-4 text-center">
                                                                    <h6 class="fw-bold mb-3">Tolak Pembayaran Ini?</h6>
                                                                    <input type="text" name="reason"
                                                                        class="form-control mb-3"
                                                                        placeholder="Alasan penolakan..." required>
                                                                    <div class="d-flex justify-content-center gap-2">
                                                                        <button type="button" class="btn btn-light"
                                                                            data-bs-dismiss="modal">Batal</button>
                                                                        <button type="submit"
                                                                            class="btn btn-danger">Tolak Permanen</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <h5 class="fw-bold text-dark mb-1">+ Rp
                                                {{ number_format($ins->amount_paid, 0, ',', '.') }}</h5>
                                            <button class="btn btn-light btn-sm text-muted border rounded-pill px-3 mt-1"
                                                onclick="openModal('{{ asset('storage/' . $ins->payment_proof) }}')">
                                                <i class="mdi mdi-image-outline me-1"></i> Bukti
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80"
                                        class="mb-3 opacity-50" alt="Empty">
                                    <p class="text-muted fw-bold">Belum ada riwayat pembayaran.</p>
                                </div>
                            @endforelse

                            {{-- START POINT --}}
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">Pengajuan Dibuat</h6>
                                        <small class="text-muted">{{ $kasbon->created_at->format('d F Y, H:i') }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark border">Rp
                                            {{ number_format($kasbon->amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL LIGHTBOX --}}
    <div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-body text-center p-0 position-relative">
                    <button type="button"
                        class="btn-close btn-close-white position-absolute top-0 end-0 m-3 bg-dark rounded-circle p-2 shadow"
                        data-bs-dismiss="modal"></button>
                    <img id="modalImage" src="" class="img-fluid rounded-3 shadow-lg" style="max-height: 85vh;">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Format Rupiah
        const rupiah = document.getElementById('rupiah');
        if (rupiah) {
            rupiah.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        }

        function formatRupiah(angka) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        }

        // Modal Image
        function openModal(src) {
            document.getElementById('modalImage').src = src;
            var myModal = new bootstrap.Modal(document.getElementById('imgModal'));
            myModal.show();
        }
    </script>
@endsection
