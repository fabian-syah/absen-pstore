@extends('layout.master')

@section('title', 'Form Pengajuan Kasbon')

@push('styles')
    {{-- Select2 CSS for Searchable Dropdown --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* RAMADAN THEME OVERRIDES */
        .ramadan-card {
            border: none;
            border-radius: 20px;
            background: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 105, 62, 0.08);
        }

        .ramadan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #00693E, #D4AF37, #00693E);
        }

        /* Ketupat Pattern Background (Subtle) */
        .bg-ramadan-pattern {
            background-color: #ffffff;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2300693e' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .header-ramadan {
            background: linear-gradient(135deg, #004d2e 0%, #00693E 100%);
            padding: 30px;
            color: white;
            border-radius: 20px 20px 0 0;
            position: relative;
        }

        .header-ramadan::after {
            content: '☪';
            font-size: 80px;
            color: rgba(255,255,255,0.1);
            position: absolute;
            right: 20px;
            top: -10px;
            transform: rotate(15deg);
        }

        /* Input & Select Styling */
        .form-control, .form-select, .select2-container .select2-selection--single {
            border: 2px solid #e9ecef !important;
            border-radius: 12px !important;
            padding: 12px 15px;
            height: auto;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #00693E !important;
            box-shadow: 0 0 0 4px rgba(0, 105, 62, 0.1) !important;
        }

        /* Select2 Customization */
        .select2-container .select2-selection--single {
            height: 52px !important; /* Match text input height */
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 50px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057;
            font-weight: 500;
            padding-left: 0;
        }

        /* Payment Option Cards */
        .payment-option-input { display: none; }
        .payment-option-card {
            cursor: pointer;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            height: 100%;
            background-color: #fff;
        }
        .payment-option-card:hover {
            border-color: #D4AF37;
            background-color: #fffae6;
            transform: translateY(-3px);
        }
        .payment-option-input:checked + .payment-option-card {
            border-color: #00693E;
            background-color: #e6f0eb;
            color: #00693E;
            box-shadow: 0 4px 15px rgba(0, 105, 62, 0.15);
        }
        .payment-option-input:checked + .payment-option-card i {
            color: #00693E;
            transform: scale(1.1);
        }

        .icon-lg {
            font-size: 2.5rem;
            margin-bottom: 12px;
            color: #adb5bd;
            transition: all 0.3s;
        }

        /* Upload Box */
        .upload-box {
            border: 2px dashed #00693E;
            background-color: rgba(0, 105, 62, 0.03);
            border-radius: 15px;
            transition: all 0.3s;
        }
        .upload-box:hover {
            background-color: rgba(0, 105, 62, 0.06);
        }

        .btn-ramadan {
            background: linear-gradient(135deg, #D4AF37 0%, #b89628 100%);
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            transition: all 0.3s;
        }
        .btn-ramadan:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
            color: white;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card ramadan-card shadow-lg">
                    {{-- Header --}}
                    <div class="header-ramadan">
                        <h3 class="mb-1 fw-bold">🌙 Form Pengajuan Kasbon</h3>
                        <p class="mb-0 opacity-75">Berkah Ramadan, Permudah Urusan.</p>
                    </div>

                    <div class="card-body p-4 p-md-5 bg-ramadan-pattern">

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-alert-circle fs-4 me-2"></i>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('kasbon.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- 1. INFORMASI PEMINJAM --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="mdi mdi-account-circle text-success me-1"></i> Informasi Peminjam
                                </label>

                                @if (in_array(auth()->user()->role, ['admin', 'admin_gaji']))
                                    {{-- SEARCHABLE SELECT FOR ADMIN --}}
                                    <div class="form-group mb-0">
                                        <select name="user_id" id="userSelect" class="form-select w-100" required>
                                            <option value="" selected disabled>Cari Nama Karyawan...</option>
                                            @foreach ($users as $u)
                                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }} 
                                                    data-division="{{ $u->division->name ?? ($u->division ?? '-') }}"
                                                    data-avatar="{{ $u->profile_photo_url ?? asset('assets/images/faces/face1.jpg') }}">
                                                    {{ $u->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text text-success mt-2">
                                            <i class="mdi mdi-information-outline"></i> Admin Mode: Silahkan cari nama karyawan yang mengajukan.
                                        </div>
                                    </div>
                                @else
                                    {{-- READONLY FOR USER --}}
                                    <div class="p-3 rounded-3 bg-light border d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-weight: bold; font-size: 1.2rem;">
                                                {{ substr(auth()->user()->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ auth()->user()->name }}</h6>
                                            <small class="text-muted">{{ auth()->user()->division->name ?? 'Staff Umum' }}</small>
                                        </div>
                                    </div>
                                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                                @endif
                            </div>

                            {{-- 2. NOMINAL --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="mdi mdi-cash-multiple text-warning me-1"></i> Nominal Pengajuan
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-success text-white fw-bold border-0 px-4" style="border-radius: 12px 0 0 12px;">Rp</span>
                                    <input type="text" name="amount" id="rupiah" class="form-control fw-bold fs-4 text-success" 
                                        placeholder="0" required autocomplete="off" value="{{ old('amount') }}"
                                        style="border-radius: 0 12px 12px 0 !important; z-index: 0;">
                                </div>
                                <small class="text-muted ps-1">Masukkan nominal tanpa tanda titik.</small>
                            </div>

                            {{-- 3. METODE PENCAIRAN --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-3">
                                    <i class="mdi mdi-bank-transfer text-primary me-1"></i> Metode Pencairan
                                </label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="radio" name="payment_method" id="methodCash" value="cash" class="payment-option-input" 
                                            {{ old('payment_method', 'cash') == 'cash' ? 'checked' : '' }} onchange="toggleBank(false)">
                                        <label for="methodCash" class="payment-option-card d-flex flex-column align-items-center justify-content-center h-100">
                                            <i class="mdi mdi-wallet icon-lg"></i>
                                            <span class="fw-bold fs-5">Tunai / Cash</span>
                                            <small class="text-muted mt-1">Ambil langsung di kasir keuangan</small>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="radio" name="payment_method" id="methodTransfer" value="transfer" class="payment-option-input" 
                                            {{ old('payment_method') == 'transfer' ? 'checked' : '' }} onchange="toggleBank(true)">
                                        <label for="methodTransfer" class="payment-option-card d-flex flex-column align-items-center justify-content-center h-100">
                                            <i class="mdi mdi-bank icon-lg"></i>
                                            <span class="fw-bold fs-5">Transfer Bank</span>
                                            <small class="text-muted mt-1">Dikirim ke rekening pribadi</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. INFORMASI BANK (Conditional) --}}
                            <div id="bankDetails" class="mb-4 p-4 rounded-3" style="display: none; background-color: #f8fcf9; border: 1px dashed #00693E;">
                                <h6 class="fw-bold mb-3 text-success">
                                    <i class="mdi mdi-card-account-details-outline me-1"></i> Detail Rekening
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="small text-muted fw-bold">Nama Bank</label>
                                        <input type="text" name="bank_name" class="form-control" placeholder="Contoh: BCA" value="{{ old('bank_name') }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="small text-muted fw-bold">Nomor Rekening</label>
                                        <input type="number" name="account_number" class="form-control fw-bold" placeholder="Contoh: 1234xxxx" value="{{ old('account_number') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small text-muted fw-bold">Atas Nama</label>
                                        <input type="text" name="account_name" class="form-control" placeholder="Nama Pemilik" value="{{ old('account_name') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- 5. KEPERLUAN --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="mdi mdi-pencil-box-outline text-info me-1"></i> Keperluan Kasbon
                                </label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan alasan pengajuan kasbon..." required>{{ old('description') }}</textarea>
                            </div>

                            {{-- 6. DOKUMEN BUKTI --}}
                            <div class="mb-5">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="mdi mdi-paperclip text-secondary me-1"></i> Lampiran Dokumen
                                </label>
                                <div class="upload-box p-4 text-center">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="p-3 bg-white rounded border text-center">
                                                <i class="mdi mdi-camera text-muted fs-3 mb-2 d-block"></i>
                                                <label class="small fw-bold mb-2 d-block">Bukti 1 (Wajib)</label>
                                                <input type="file" name="photo_1" class="form-control form-control-sm" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 bg-white rounded border text-center">
                                                <i class="mdi mdi-file-document text-muted fs-3 mb-2 d-block"></i>
                                                <label class="small fw-bold mb-2 d-block">Bukti 2 (Wajib)</label>
                                                <input type="file" name="photo_2" class="form-control form-control-sm" required>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted mt-3 d-block">Format: JPG/PNG/PDF. Maksimal 10MB.</small>
                                </div>
                            </div>

                            {{-- BUTTONS --}}
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end align-items-center">
                                <a href="{{ route('kasbon.index') }}" class="btn btn-light btn-lg px-4 me-md-2 rounded-pill fw-bold text-muted border">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-ramadan btn-lg px-5 rounded-pill">
                                    <i class="mdi mdi-send me-2"></i> Ajukan Sekarang
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Init Select2 with custom formatting
            $('#userSelect').select2({
                theme: 'default',
                placeholder: "Cari Nama Karyawan...",
                allowClear: true,
                width: '100%',
                templateResult: formatUserOption,
                templateSelection: formatUserSelection
            });

            // Toggle Bank on Load
            if($('#methodTransfer').is(':checked')) {
                toggleBank(true);
            }

            // Format Rupiah Input
            const rupiah = document.getElementById('rupiah');
            if(rupiah){
                rupiah.addEventListener('keyup', function(e) { 
                    rupiah.value = formatRupiah(this.value); 
                });
            }
        });

        // Custom Select2 Option View
        function formatUserOption (user) {
            if (!user.id) { return user.text; }
            var $user = $(
                '<div class="d-flex align-items-center py-1">' +
                '<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width:35px; height:35px; font-weight:bold;">' + user.text.charAt(0) + '</div>' +
                '<div>' +
                '<div class="fw-bold text-dark">' + user.text + '</div>' +
                '<div class="small text-muted">' + $(user.element).data('division') + '</div>' +
                '</div>' +
                '</div>'
            );
            return $user;
        }

        function formatUserSelection (user) {
            if (!user.id) { return user.text; }
            return user.text + ' - ' + $(user.element).data('division');
        }

        // Toggle Bank Details Logic
        function toggleBank(show) {
            const el = document.getElementById('bankDetails');
            const inputs = el.querySelectorAll('input');
            if (show) {
                $(el).slideDown(300);
                inputs.forEach(i => i.required = true);
            } else {
                $(el).slideUp(300);
                inputs.forEach(i => i.required = false);
            }
        }

        // Rupiah Formatter
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
    </script>
@endpush