@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
                        <h4 class="card-title mb-0"><i class="mdi mdi-history me-2"></i>Riwayat Scan Security</h4>
                        <form action="{{ route('security.history') }}" method="GET" class="d-flex">
                            <input type="date" name="date" class="form-control form-control-sm me-2" value="{{ request('date') }}">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </form>
                    </div>

                    {{-- ========================================== --}}
                    {{-- DESKTOP TABLE VIEW (hidden on mobile) --}}
                    {{-- ========================================== --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal & Jam</th>
                                    <th>Karyawan</th>
                                    <th>Lokasi</th>
                                    <th>Status Scan</th>
                                    <th>Bukti Foto & Catatan</th>
                                    <th>Petugas Scanner</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    @php
                                        // PARSING CATATAN MASUK
                                        $noteMasuk = '';
                                        $parts = explode(' | ', $log->notes ?? '');
                                        if (isset($parts[0])) {
                                            $firstPart = trim($parts[0]);
                                            if (!empty($firstPart) && !str_contains($firstPart, 'Catatan:') && !str_contains($firstPart, 'Security Scan') && $firstPart != '-') {
                                                $noteMasuk = $firstPart;
                                            }
                                        }
                                        // PARSING CATATAN PULANG
                                        $notePulang = '';
                                        foreach ($parts as $part) {
                                            if (str_contains($part, 'Catatan:')) {
                                                $notePulang = trim(str_replace('Catatan:', '', $part));
                                                break;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        {{-- 1. TANGGAL & JAM --}}
                                        <td>
                                            <div class="fw-bold">{{ $log->check_in_time ? \Carbon\Carbon::parse($log->check_in_time)->format('d M Y') : '-' }}</div>
                                            <small class="text-muted d-block" style="font-size: 11px;">
                                                <i class="mdi mdi-login text-success"></i> In: {{ $log->check_in_time ? \Carbon\Carbon::parse($log->check_in_time)->format('H:i') : '-' }}
                                            </small>
                                            @if($log->check_out_time)
                                                <small class="text-muted d-block" style="font-size: 11px;">
                                                    <i class="mdi mdi-logout text-danger"></i> Out: {{ \Carbon\Carbon::parse($log->check_out_time)->format('H:i') }}
                                                </small>
                                            @endif
                                        </td>

                                        {{-- 2. KARYAWAN --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $log->user->profile_photo_path ? asset('storage/' . $log->user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($log->user->name) }}" 
                                                   class="rounded-circle me-2 img-clickable" style="width: 35px; height: 35px; object-fit: cover;"
                                                   onclick="showImage(this.src, 'Foto Profil: {{ $log->user->name }}')">
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $log->user->name }}</div>
                                                    <small class="text-muted" style="font-size: 10px;">{{ $log->user->role }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 3. LOKASI --}}
                                        <td>
                                            <div style="font-size: 12px;">{{ $log->user->division->name ?? '-' }}</div>
                                            <small class="text-muted" style="font-size: 10px;">{{ $log->branch->name ?? '-' }}</small>
                                        </td>

                                        {{-- 4. STATUS --}}
                                        <td>
                                            @if($log->is_late_checkin) <span class="badge bg-danger" style="font-size: 10px;">Telat</span>
                                            @else <span class="badge bg-success" style="font-size: 10px;">Tepat Waktu</span> @endif
                                            @if($log->check_out_time && $log->is_early_checkout) <span class="badge bg-warning text-dark mt-1" style="font-size: 10px;">Pulang Cepat</span> @endif
                                        </td>

                                        {{-- 5. BUKTI FOTO & CATATAN --}}
                                        <td>
                                            <div class="d-flex gap-2 align-items-start">
                                                @if($log->photo_path) 
                                                    <div class="d-flex flex-column align-items-center" style="width: 50px;">
                                                        <img src="{{ asset('storage/' . $log->photo_path) }}" 
                                                             class="rounded border img-clickable mb-1" 
                                                             style="width: 40px; height: 40px; object-fit: cover;" 
                                                             onclick="showImage(this.src, 'Bukti Masuk')">
                                                        @if($noteMasuk)
                                                            <div class="bg-light border rounded px-1 text-center" style="width: 100%; line-height: 1;">
                                                                <small class="text-muted fst-italic" style="font-size: 8px;">{{ \Illuminate\Support\Str::limit($noteMasuk, 15) }}</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if($log->photo_out_path) 
                                                    <div class="d-flex flex-column align-items-center" style="width: 50px;">
                                                        <img src="{{ asset('storage/' . $log->photo_out_path) }}" 
                                                             class="rounded border img-clickable mb-1" 
                                                             style="width: 40px; height: 40px; object-fit: cover;" 
                                                             onclick="showImage(this.src, 'Bukti Pulang')">
                                                        @if($notePulang)
                                                            <div class="bg-light border rounded px-1 text-center" style="width: 100%; line-height: 1;">
                                                                <small class="text-muted fst-italic" style="font-size: 8px;">{{ \Illuminate\Support\Str::limit($notePulang, 15) }}</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- 6. PETUGAS --}}
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge p-1 me-1" style="background-color: #e8f5e9; color: #198754; font-size: 9px;">IN</span>
                                                    <small class="fw-bold text-dark" style="font-size: 11px;">
                                                        @if($log->scanner)
                                                            {{ $log->scanner->name }}
                                                        @else
                                                            <span class="text-muted fst-italic">Mandiri/Selfie</span>
                                                        @endif
                                                    </small>
                                                </div>
                                                @if($log->check_out_time)
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge p-1 me-1" style="background-color: #ffebee; color: #dc3545; font-size: 9px;">OUT</span>
                                                        <small class="fw-bold text-dark" style="font-size: 11px;">
                                                            @if (str_contains($log->notes, 'Security Scan by'))
                                                                {{ Str::after($log->notes, 'Security Scan by ') }}
                                                            @elseif($log->verified_by_user_id && !$log->scanned_by_user_id)
                                                                {{ $log->verifier->name ?? 'System' }}
                                                            @else
                                                                <span class="text-muted fst-italic">Mandiri/System</span>
                                                            @endif
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat scan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ========================================== --}}
                    {{-- MOBILE CARD VIEW (visible only on mobile) --}}
                    {{-- ========================================== --}}
                    <div class="d-block d-md-none">
                        @forelse($logs as $log)
                            @php
                                // PARSING CATATAN MASUK
                                $noteMasuk = '';
                                $parts = explode(' | ', $log->notes ?? '');
                                if (isset($parts[0])) {
                                    $firstPart = trim($parts[0]);
                                    if (!empty($firstPart) && !str_contains($firstPart, 'Catatan:') && !str_contains($firstPart, 'Security Scan') && $firstPart != '-') {
                                        $noteMasuk = $firstPart;
                                    }
                                }
                                // PARSING CATATAN PULANG
                                $notePulang = '';
                                foreach ($parts as $part) {
                                    if (str_contains($part, 'Catatan:')) {
                                        $notePulang = trim(str_replace('Catatan:', '', $part));
                                        break;
                                    }
                                }
                            @endphp
                            <div class="card mb-3 shadow-sm border">
                                <div class="card-body p-3">
                                    {{-- HEADER: Karyawan + Tanggal --}}
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $log->user->profile_photo_path ? asset('storage/' . $log->user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($log->user->name) }}" 
                                                 class="rounded-circle me-2 img-clickable" style="width: 45px; height: 45px; object-fit: cover;"
                                                 onclick="showImage(this.src, 'Foto Profil: {{ $log->user->name }}')">
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 14px;">{{ $log->user->name }}</div>
                                                <small class="text-muted" style="font-size: 11px;">{{ $log->user->role }} • {{ $log->user->division->name ?? '-' }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if($log->is_late_checkin) 
                                                <span class="badge bg-danger" style="font-size: 10px;">Telat</span>
                                            @else 
                                                <span class="badge bg-success" style="font-size: 10px;">Tepat Waktu</span> 
                                            @endif
                                            @if($log->check_out_time && $log->is_early_checkout) 
                                                <span class="badge bg-warning text-dark d-block mt-1" style="font-size: 10px;">Pulang Cepat</span> 
                                            @endif
                                        </div>
                                    </div>

                                    {{-- INFO ROWS --}}
                                    <div class="row g-2 mb-2">
                                        {{-- Tanggal & Jam --}}
                                        <div class="col-6">
                                            <div class="bg-light rounded p-2">
                                                <small class="text-muted d-block" style="font-size: 10px;">Tanggal</small>
                                                <div class="fw-bold" style="font-size: 12px;">{{ $log->check_in_time ? \Carbon\Carbon::parse($log->check_in_time)->format('d M Y') : '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-light rounded p-2">
                                                <small class="text-muted d-block" style="font-size: 10px;">Cabang</small>
                                                <div class="fw-bold" style="font-size: 12px;">{{ $log->branch->name ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- JAM IN/OUT --}}
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center rounded p-2" style="background-color: #e8f5e9;">
                                                <i class="mdi mdi-login text-success me-2" style="font-size: 18px;"></i>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 9px;">Masuk</small>
                                                    <div class="fw-bold text-success" style="font-size: 13px;">{{ $log->check_in_time ? \Carbon\Carbon::parse($log->check_in_time)->format('H:i') : '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center rounded p-2" style="background-color: #ffebee;">
                                                <i class="mdi mdi-logout text-danger me-2" style="font-size: 18px;"></i>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 9px;">Pulang</small>
                                                    <div class="fw-bold text-danger" style="font-size: 13px;">{{ $log->check_out_time ? \Carbon\Carbon::parse($log->check_out_time)->format('H:i') : '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- BUKTI FOTO --}}
                                    @if($log->photo_path || $log->photo_out_path)
                                        <div class="d-flex gap-2 mb-2">
                                            @if($log->photo_path) 
                                                <div class="text-center">
                                                    <img src="{{ asset('storage/' . $log->photo_path) }}" 
                                                         class="rounded border img-clickable" 
                                                         style="width: 50px; height: 50px; object-fit: cover;" 
                                                         onclick="showImage(this.src, 'Bukti Masuk')">
                                                    <small class="d-block text-muted" style="font-size: 9px;">Foto In</small>
                                                </div>
                                            @endif
                                            @if($log->photo_out_path) 
                                                <div class="text-center">
                                                    <img src="{{ asset('storage/' . $log->photo_out_path) }}" 
                                                         class="rounded border img-clickable" 
                                                         style="width: 50px; height: 50px; object-fit: cover;" 
                                                         onclick="showImage(this.src, 'Bukti Pulang')">
                                                    <small class="d-block text-muted" style="font-size: 9px;">Foto Out</small>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- PETUGAS SCANNER --}}
                                    <div class="border-top pt-2 mt-2">
                                        <small class="text-muted d-block mb-1" style="font-size: 10px;">Petugas Scanner:</small>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge px-2 py-1" style="background-color: #e8f5e9; color: #198754; font-size: 10px;">
                                                <i class="mdi mdi-login me-1"></i>
                                                @if($log->scanner){{ $log->scanner->name }}@else Mandiri @endif
                                            </span>
                                            @if($log->check_out_time)
                                                <span class="badge px-2 py-1" style="background-color: #ffebee; color: #dc3545; font-size: 10px;">
                                                    <i class="mdi mdi-logout me-1"></i>
                                                    @if (str_contains($log->notes, 'Security Scan by'))
                                                        {{ Str::after($log->notes, 'Security Scan by ') }}
                                                    @elseif($log->verified_by_user_id && !$log->scanned_by_user_id) 
                                                        {{ $log->verifier->name ?? 'System' }}
                                                    @else
                                                        Mandiri
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="mdi mdi-history display-4 mb-2"></i>
                                <p>Belum ada riwayat scan.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- PAGINATION --}}
                    <div class="mt-4 d-flex justify-content-center justify-content-md-end">
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 p-0 mb-2">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="modalImageSrc" src="" class="img-fluid rounded shadow-lg" style="max-height: 85vh;">
                    <div class="mt-2 text-white fw-bold" id="modalImageTitle"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showImage(src, title) {
            document.getElementById('modalImageSrc').src = src;
            document.getElementById('modalImageTitle').innerText = title;
            var myModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            myModal.show();
        }
    </script>
    <style>
        .img-clickable { cursor: pointer; transition: transform 0.1s; } 
        .img-clickable:hover { transform: scale(1.05); opacity: 0.9; }
    </style>
@endsection