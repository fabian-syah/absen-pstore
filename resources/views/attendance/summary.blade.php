    @extends('layout.master')

    @section('title', 'Ringkasan Tahunan')

    @section('styles')
        {{-- Tambahkan CSS Select2 untuk searchable dropdown --}}
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container .select2-selection--single {
                height: 38px !important;
                border: 1px solid #ced4da !important;
                border-radius: 0.375rem !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px !important;
                padding-left: 12px;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px !important;
            }
        </style>
    @endsection

    @section('content')
    <div class="row">
        <div class="col-12">
            
            {{-- HEADER & FILTER --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body py-3">
                    <div class="row align-items-center g-3">
                        <div class="col-md-4">
                            <h4 class="fw-bold mb-1">Ringkasan Absensi</h4>
                            <p class="text-muted mb-0 small">Menampilkan data: <strong class="text-primary">{{ $user->name }}</strong></p>
                        </div>
                        
                        <div class="col-md-8">
                            <form action="{{ route('attendance.summary') }}" method="GET" class="d-flex justify-content-md-end gap-2 flex-wrap">
                                
                                {{-- FILTER KARYAWAN (KHUSUS ADMIN/AUDIT) --}}
                                @if(isset($isAccessGranted) && $isAccessGranted)
                                    <div style="min-width: 250px;">
                                        <select name="user_id" class="form-select select2-user" onchange="this.form.submit()">
                                            <option value="" disabled>-- Cari Karyawan --</option>
                                            {{-- Opsi Diri Sendiri --}}
                                            <option value="{{ Auth::id() }}" {{ $user->id == Auth::id() ? 'selected' : '' }}>
                                                {{ Auth::user()->name }} (Saya)
                                            </option>
                                            
                                            {{-- Loop Employees dari Controller --}}
                                            @foreach($employees as $emp)
                                                @if($emp->id != Auth::id())
                                                    <option value="{{ $emp->id }}" {{ $user->id == $emp->id ? 'selected' : '' }}>
                                                        {{ $emp->name }} - {{ $emp->branch->name ?? 'Cabang Tidak Diketahui' }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- FILTER TAHUN --}}
                                <div class="d-flex align-items-center bg-light rounded px-3 py-1 border" style="height: 38px;">
                                    <i class="mdi mdi-calendar text-muted me-2"></i>
                                    <select name="year" class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark p-0 shadow-none" style="width: auto; cursor: pointer;" onchange="this.form.submit()">
                                        @for ($y = 2024; $y <= date('Y') + 1; $y++)
                                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GRAND TOTAL CARDS --}}
            <div class="row g-3 mb-4">
                {{-- HADIR --}}
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 5px solid #198754 !important;">
                        <div class="card-body p-3">
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Hadir</h6>
                            <h2 class="mb-0 fw-bold text-success">{{ $grandTotal['masuk'] }}</h2>
                            <small class="text-muted" style="font-size: 0.7rem;">(Termasuk {{ $grandTotal['wfh'] }} WFH/Dinas)</small>
                        </div>
                    </div>
                </div>
                
                {{-- SAKIT & IZIN --}}
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 5px solid #0dcaf0 !important;">
                        <div class="card-body p-3">
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Sakit & Izin</h6>
                            <h2 class="mb-0 fw-bold text-info">{{ $grandTotal['sakit'] + $grandTotal['izin'] }}</h2>
                            <small class="text-muted" style="font-size: 0.7rem;">{{ $grandTotal['sakit'] }} Sakit, {{ $grandTotal['izin'] }} Izin</small>
                        </div>
                    </div>
                </div>

                {{-- CUTI --}}
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 5px solid #6c757d !important;">
                        <div class="card-body p-3">
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Cuti Diambil</h6>
                            <h2 class="mb-0 fw-bold text-secondary">{{ $grandTotal['cuti'] }}</h2>
                            <small class="text-muted" style="font-size: 0.7rem;">Hari</small>
                        </div>
                    </div>
                </div>

                {{-- ALPHA & TELAT --}}
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 5px solid #dc3545 !important;">
                        <div class="card-body p-3">
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Alpha / Telat</h6>
                            <div class="d-flex align-items-baseline">
                                <h2 class="mb-0 fw-bold text-danger me-2">{{ $grandTotal['alpha'] }}</h2>
                                <span class="text-muted fw-bold">/ {{ $grandTotal['telat'] }}x</span>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Tanpa Keterangan</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABEL RINCIAN BULANAN --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold">Rincian Per Bulan ({{ $selectedYear }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr style="font-size: 0.85rem;">
                                    <th class="ps-4 py-3 text-uppercase">Bulan</th>
                                    <th class="text-center text-uppercase">Total Hari</th>
                                    <th class="text-center text-uppercase text-success">Masuk</th>
                                    <th class="text-center text-uppercase text-info">WFH/DL</th>
                                    <th class="text-center text-uppercase text-primary">Sakit</th>
                                    <th class="text-center text-uppercase text-secondary">Izin</th>
                                    <th class="text-center text-uppercase text-dark">Cuti</th>
                                    <th class="text-center text-uppercase text-danger">Alpha</th>
                                    <th class="text-center text-uppercase text-warning pe-4">Telat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthsData as $data)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $data['name'] }}</td>
                                        
                                        @if ($data['total_hari'] == 0)
                                            <td colspan="8" class="text-center text-muted small fst-italic pe-4">- Tidak ada data absensi -</td>
                                        @else
                                            <td class="text-center fw-bold">{{ $data['total_hari'] }}</td>
                                            <td class="text-center fw-bold text-success">{{ $data['masuk'] }}</td>
                                            <td class="text-center text-info">{{ $data['wfh'] > 0 ? $data['wfh'] : '-' }}</td>
                                            <td class="text-center text-primary">{{ $data['sakit'] > 0 ? $data['sakit'] : '-' }}</td>
                                            <td class="text-center text-secondary">{{ $data['izin'] > 0 ? $data['izin'] : '-' }}</td>
                                            <td class="text-center text-dark">{{ $data['cuti'] > 0 ? $data['cuti'] : '-' }}</td>
                                            <td class="text-center text-danger fw-bold">{{ $data['alpha'] > 0 ? $data['alpha'] : '-' }}</td>
                                            <td class="text-center text-warning fw-bold pe-4">{{ $data['telat'] > 0 ? $data['telat'].'x' : '-' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light border-top">
                                <tr class="fw-bold">
                                    <td class="ps-4 py-3">TOTAL {{ $selectedYear }}</td>
                                    <td class="text-center">{{ $grandTotal['total_hari'] }}</td>
                                    <td class="text-center text-success">{{ $grandTotal['masuk'] }}</td>
                                    <td class="text-center text-info">{{ $grandTotal['wfh'] }}</td>
                                    <td class="text-center text-primary">{{ $grandTotal['sakit'] }}</td>
                                    <td class="text-center text-secondary">{{ $grandTotal['izin'] }}</td>
                                    <td class="text-center text-dark">{{ $grandTotal['cuti'] }}</td>
                                    <td class="text-center text-danger">{{ $grandTotal['alpha'] }}</td>
                                    <td class="text-center text-warning pe-4">{{ $grandTotal['telat'] }}x</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endsection

    @section('scripts')
        {{-- Script Select2 untuk Searchable Dropdown --}}
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                // Inisialisasi Select2 pada class .select2-user
                $('.select2-user').select2({
                    placeholder: "Cari Karyawan...",
                    allowClear: false,
                    width: '100%' // Menyesuaikan lebar parent
                });
            });
        </script>
    @endsection