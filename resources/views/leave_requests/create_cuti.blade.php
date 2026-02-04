@extends('layout.master')

@section('title')
    Form Pengajuan Cuti
@endsection

@section('heading')
    <h4 class="mb-0 fw-bold">Form Pengajuan Cuti</h4>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">

            {{-- INFORMASI SALDO CUTI --}}
            <div class="card bg-primary text-white mb-4 border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1 text-uppercase fw-bold">Sisa Saldo Cuti Anda</h6>
                        <h2 class="fw-bold mb-0 display-6">{{ $user->leave_balance ?? 10 }} <small class="fs-6">Hari</small>
                        </h2>
                    </div>
                    <div>
                        <i class="mdi mdi-calendar-check text-white-50" style="font-size: 4rem;"></i>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('leave-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        {{-- Hiden Input Type = Cuti --}}
                        <input type="hidden" name="type" value="cuti">

                        {{-- Tanggal Mulai --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Mulai Tanggal Cuti</label>
                            <input type="date" name="start_date" class="form-control rounded-pill bg-light border-0 px-4"
                                required>
                        </div>

                        {{-- Tanggal Selesai --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Sampai Tanggal (Opsional Jika
                                1 Hari)</label>
                            <input type="date" name="end_date" class="form-control rounded-pill bg-light border-0 px-4">
                            <small class="text-muted ms-3 fst-italic">*Isi jika cuti lebih dari 1 hari.</small>
                        </div>

                        {{-- Keterangan / Alasan --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Alasan Cuti</label>
                            <textarea name="reason" class="form-control bg-light border-0 px-4 py-3" rows="3"
                                placeholder="Contoh: Acara keluarga di kampung halaman..." required
                                style="border-radius: 20px;"></textarea>
                        </div>

                        {{-- Bukti / Lampiran --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Bukti / Lampiran
                                (Opsional)</label>
                            <input type="file" name="file_proof" class="form-control rounded-pill bg-light border-0"
                                accept="image/*,application/pdf">
                            <small class="text-muted ms-3 fst-italic">*Upload surat cuti atau bukti pendukung jika
                                ada.</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill py-3 fw-bold shadow-sm">
                                <i class="mdi mdi-send me-1"></i> Ajukan Cuti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection