@extends('layout.master')

@section('title')
    Ajukan Izin/Cuti/WFH
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0 text-white"><i class="mdi mdi-file-document-edit me-2"></i>Form Pengajuan
                    </h4>
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('leave-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- 1. Pilih Tipe --}}
                        <div class="mb-4">
                            <label class="fw-bold mb-2">Jenis Pengajuan</label>
                            <select name="type" id="type" class="form-control form-select" required
                                onchange="toggleInputs()">
                                <option value="" disabled selected>-- Pilih Jenis --</option>
                                <option value="izin" {{ old('type') == 'izin' ? 'selected' : '' }}>Izin / Keperluan Pribadi
                                </option>
                                <option value="sakit" {{ old('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="wfh" {{ old('type') == 'wfh' ? 'selected' : '' }}>WFH (Work From Home)</option>
                                <option value="dinas" {{ old('type') == 'dinas' ? 'selected' : '' }}>Dinas Luar</option>
                                <option value="telat" {{ old('type') == 'telat' ? 'selected' : '' }}>Izin Terlambat</option>
                                <option value="libur" {{ old('type') == 'libur' ? 'selected' : '' }}>Izin Libur</option>
                            </select>
                        </div>

                        {{-- INFO SALDO CUTI (Hanya Tampil Jika Pilih Cuti) --}}
                        <div id="leave_balance_info" class="alert alert-info d-none mb-4">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-information-outline fs-4 me-2"></i>
                                <div>
                                    <strong>Info Saldo Cuti</strong><br>
                                    <span>
                                        Sisa Cuti: <strong>{{ auth()->user()->leave_balance ?? 0 }}</strong> Hari
                                        (Total: {{ auth()->user()->yearly_leave_limit ?? 12 }} Hari/Tahun)
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Area Input Waktu (Dinamis) --}}
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label id="label_start_date" class="fw-bold mb-2">Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ old('start_date', date('Y-m-d')) }}" required>
                            </div>

                            {{-- Input Tanggal Selesai (Untuk WFH, Izin, Sakit, Cuti) --}}
                            <div class="col-md-6 mb-4" id="end_date_box">
                                <label class="fw-bold mb-2">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                <small class="text-muted">Pilih tanggal yang sama jika hanya 1 hari.</small>
                            </div>

                            {{-- Input Jam (Khusus Telat) --}}
                            <div class="col-md-6 mb-4 d-none" id="time_box">
                                <label class="fw-bold mb-2">Perkiraan Jam Sampai</label>
                                <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
                            </div>
                        </div>

                        {{-- 3. Alasan --}}
                        <div class="mb-4">
                            <label class="fw-bold mb-2">Alasan / Keterangan</label>
                            <textarea name="reason" class="form-control" rows="3" required
                                placeholder="Jelaskan alasan pengajuan...">{{ old('reason') }}</textarea>
                        </div>

                        {{-- 4. Bukti Foto --}}
                        <div class="mb-4">
                            <label class="fw-bold mb-2">Bukti Foto / Dokumen (Wajib)</label>
                            <input type="file" name="file_proof" class="form-control" accept="image/*,.pdf" required>
                            <small class="text-muted">Upload surat dokter, foto kegiatan dinas, atau bukti kendala di
                                jalan.</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('leave-requests.index') }}" class="btn btn-light me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleInputs() {
            let type = document.getElementById('type').value;
            let endDateBox = document.getElementById('end_date_box');
            let timeBox = document.getElementById('time_box');
            let labelDate = document.getElementById('label_start_date');

            // Jika tipe adalah TELAT, tampilkan input JAM, sembunyikan Tanggal Selesai
            if (type === 'telat') {
                endDateBox.classList.add('d-none');
                timeBox.classList.remove('d-none');
                labelDate.innerText = "Tanggal Hari Ini";
            }
            // Untuk WFH, IZIN, SAKIT, CUTI -> Tampilkan Tanggal Selesai, Sembunyikan Jam
            else {
                endDateBox.classList.remove('d-none');
                timeBox.classList.add('d-none');
                labelDate.innerText = "Tanggal Mulai";
            }

            // Tampilkan Info Saldo Cuti hanya jika pilih "cuti"
            let leaveInfo = document.getElementById('leave_balance_info');
            if (type === 'cuti') {
                leaveInfo.classList.remove('d-none');
            } else {
                leaveInfo.classList.add('d-none');
            }
        }

        // Jalankan saat load agar form sesuai dengan old input jika ada error validasi
        document.addEventListener('DOMContentLoaded', function () { toggleInputs(); });
    </script>
@endsection