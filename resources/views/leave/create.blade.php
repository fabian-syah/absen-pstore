@extends('layout.master')

@section('title')
    Buat Pengajuan Izin/Cuti
@endsection

@section('heading')
    Form Pengajuan Izin, Cuti, Sakit, & Libur
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card card-action">
                <div class="card-body">
                    <h4 class="card-title mb-4">Formulir Pengajuan</h4>
                    <p class="text-muted mb-4">
                        Silakan isi formulir di bawah ini dengan lengkap. <br>
                        - **Izin Telat & Sakit** wajib menyertakan foto bukti. <br>
                        - **Libur Mingguan** hanya bisa diajukan untuk 1 hari. <br>
                        - **Cuti** bisa diajukan untuk rentang tanggal.
                    </p>

                    {{-- Tampilkan error validasi --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('leave-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            {{-- Jenis Pengajuan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Jenis Pengajuan <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-lg" id="type" name="type" required>
                                        <option value="">-- Pilih Jenis Pengajuan --</option>
                                        <option value="sakit" {{ old('type') == 'sakit' ? 'selected' : '' }}>Izin Sakit
                                        </option>
                                        <option value="telat" {{ old('type') == 'telat' ? 'selected' : '' }}>Izin Telat
                                        </option>
                                        <option value="cuti" {{ old('type') == 'cuti' ? 'selected' : '' }}>Cuti Tahunan
                                        </option>
                                        <option value="libur" {{ old('type') == 'libur' ? 'selected' : '' }}>
                                            Libur (Off Day)
                                        </option>
                                    </select>
                                </div>
                            </div>

                            {{-- Tanggal Mulai --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai <span class="text-danger">*</span></label>
                                    {{-- Kita akan menggunakan tipe 'text' agar bisa dikontrol oleh datepicker --}}
                                    <input type="text" class="form-control form-control-lg" id="start_date"
                                        name="start_date" placeholder="Pilih tanggal..." value="{{ old('start_date') }}"
                                        required readonly>
                                </div>
                            </div>

                            {{-- Tanggal Selesai (Hanya untuk Cuti) --}}
                            <div class="col-md-6" id="end_date_wrapper" style="display: none;">
                                <div class="form-group">
                                    <label for="end_date">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" id="end_date" name="end_date"
                                        placeholder="Pilih tanggal..." value="{{ old('end_date') }}" readonly>
                                </div>
                            </div>

                            {{-- Alasan --}}
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="reason">Alasan / Keterangan <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-lg" id="reason" name="reason" rows="4"
                                        placeholder="Jelaskan alasan pengajuan Anda..."
                                        required>{{ old('reason') }}</textarea>
                                </div>
                            </div>

                            {{-- Bukti Foto (Untuk Sakit & Telat) --}}
                            <div class="col-12" id="file_proof_wrapper" style="display: none;">
                                <div class="form-group">
                                    <label for="file_proof">Bukti Foto (Surat Dokter, Kendala, dll) <span
                                            class="text-danger">*</span></label>
                                    <input type="file" class="form-control form-control-lg" id="file_proof"
                                        name="file_proof" accept="image/*">
                                    <small class="text-muted">Wajib diisi untuk Izin Sakit dan Izin Telat.</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-dark btn-lg">
                                <i class="mdi mdi-send me-2"></i>Kirim Pengajuan
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark btn-lg">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- Style untuk Bootstrap Datepicker (sesuaikan jika Anda pakai library lain) --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <style>
        /* Form Control */
        .form-control-lg {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 14px 18px;
            transition: all 0.3s ease;
        }

        .form-control-lg:focus {
            border-color: #000;
            box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
        }

        /* Style untuk readonly input tanggal agar terlihat interaktif */
        input[readonly].form-control-lg {
            background-color: #fff;
            cursor: pointer;
        }

        /* Card Action - Card Biasa dengan Style Modern */
        .card-action {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            /* Menyamakan tinggi */
        }

        /* Buttons */
        .btn-dark {
            background: #000;
            border: 2px solid #000;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 28px;
            transition: all 0.3s ease;
        }

        .btn-dark:hover {
            background: #1f2937;
            border-color: #1f2937;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-outline-dark {
            border: 2px solid #000;
            color: #000;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 28px;
            transition: all 0.3s ease;
        }

        .btn-outline-dark:hover {
            background: #000;
            color: white;
            transform: translateY(-2px);
        }
    </style>
@endpush

@push('scripts')
    {{-- Script untuk Bootstrap Datepicker (sesuaikan jika Anda pakai library lain) --}}
    {{-- Pastikan jQuery sudah dimuat di master.blade.php SEBELUM @stack('scripts') --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script>
        $(document).ready(function () {
            // --- Logika untuk Form Dinamis ---
            const typeSelect = $('#type');
            const endDateWrapper = $('#end_date_wrapper');
            const fileProofWrapper = $('#file_proof_wrapper');
            const fileProofInput = $('#file_proof');
            const startDateInput = $('#start_date');
            const endDateInput = $('#end_date');

            function toggleFields() {
                const selectedType = typeSelect.val();

                // Reset semua field
                endDateWrapper.hide();
                endDateInput.prop('required', false);
                fileProofWrapper.hide();
                fileProofInput.prop('required', false);

                // Atur berdasarkan tipe
                if (selectedType === 'sakit') {
                    // Sakit: Perlu foto, tanggal hanya 1 (atau bisa rentang, tergantung kebijakan)
                    // Asumsi kita, sakit bisa rentang
                    endDateWrapper.show();
                    endDateInput.prop('required', true);

                    fileProofWrapper.show();
                    fileProofInput.prop('required', true);

                } else if (selectedType === 'telat') {
                    // Telat: Perlu foto, tanggal hanya 1 (start_date)
                    fileProofWrapper.show();
                    fileProofInput.prop('required', true);

                } else if (selectedType === 'cuti') {
                    // Cuti: Perlu rentang tanggal, tidak perlu foto
                    endDateWrapper.show();
                    endDateInput.prop('required', true);

                } else if (selectedType === 'libur') {
                    // Libur: Hanya 1 tanggal, tidak perlu foto
                    // Tidak ada field tambahan
                }
            }

            // Panggil saat halaman dimuat (jika ada old input)
            toggleFields();

            // Panggil saat dropdown berubah
            typeSelect.on('change', toggleFields);

            // --- Logika untuk Datepicker ---
            // Inisialisasi datepicker
            startDateInput.datepicker({
                format: "yyyy-mm-dd",
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom"
            }).on('changeDate', function (e) {
                // Set tanggal minimal untuk end_date
                endDateInput.datepicker('setStartDate', e.date);
                // Jika jenisnya bukan cuti/sakit, otomatis set end_date = start_date
                const selectedType = typeSelect.val();
                if (selectedType === 'telat' || selectedType === 'libur') {
                    endDateInput.datepicker('setDate', e.date);
                }
            });

            endDateInput.datepicker({
                format: "yyyy-mm-dd",
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom"
            });

            // Logika untuk Libur Mingguan (Maksimal 1 per minggu)
            // Ini HANYA bisa divalidasi di backend (controller)
            // Tapi kita bisa bantu di frontend dengan membatasi pilihan
            if (typeSelect.val() === 'libur') {
                // Libur: Tidak ada batasan hari
            }

            typeSelect.on('change', function () {
                if ($(this).val() === 'libur') {
                    // Libur: Tidak ada batasan hari khusus
                    // Untuk "hanya boleh 1 hari/minggu" validasi terbaik ada di server.
                    alert("Validasi untuk libur mingguan (maks 1 per minggu) akan dilakukan oleh server saat submit.");
                } else {
                    // Hapus batasan hari
                    startDateInput.datepicker('setDaysOfWeekDisabled', []);
                }
            });

        });
    </script>
@endpush