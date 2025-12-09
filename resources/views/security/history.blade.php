@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title"><i class="mdi mdi-history me-2"></i>Riwayat Scan Security</h4>
                    <form action="{{ route('security.history') }}" method="GET" class="d-flex">
                        <input type="date" name="date" class="form-control form-control-sm me-2" value="{{ request('date') }}">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal & Jam</th>
                                <th>Karyawan</th>
                                <th>Lokasi</th>
                                <th>Status Scan</th>
                                <th>Bukti Foto & Catatan</th> {{-- Updated Header --}}
                                 <th>Petugas Scanner</th> 
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
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
                                        <img src="{{ $log->user->profile_photo_path ? asset('storage/' . $log->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($log->user->name) }}" 
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

                                {{-- 5. BUKTI FOTO & CATATAN (MODIFIED) --}}
                                <td>
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

                                    <div class="d-flex gap-2 align-items-start">
                                        {{-- FOTO MASUK --}}
                                        @if($log->photo_path) 
                                            <div class="d-flex flex-column align-items-center" style="width: 50px;">
                                                <img src="{{ asset('storage/'.$log->photo_path) }}" 
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

                                        {{-- FOTO PULANG --}}
                                        @if($log->photo_out_path) 
                                            <div class="d-flex flex-column align-items-center" style="width: 50px;">
                                                <img src="{{ asset('storage/'.$log->photo_out_path) }}" 
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
                                
                                {{-- 6. PETUGAS (ADMIN ONLY) --}}
                                {{-- @if(auth()->user()->role == 'admin') --}}
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        {{-- MASUK --}}
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success bg-opacity-10 text-success p-1 me-1" style="font-size: 9px;">IN</span>
                                            <small class="fw-bold text-dark" style="font-size: 11px;">
                                                {{ $log->scanner->name ?? ($log->attendance_type == 'self' ? 'Selfie' : '-') }}
                                            </small>
                                        </div>

                                        {{-- PULANG --}}
                                        @if($log->check_out_time)
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-danger bg-opacity-10 text-danger p-1 me-1" style="font-size: 9px;">OUT</span>
                                                <small class="fw-bold text-dark" style="font-size: 11px;">
                                                    @if (str_contains($log->notes, 'Security Scan by'))
                                                        {{ Str::after($log->notes, 'Security Scan by ') }}
                                                    @elseif($log->verifier)
                                                        {{ $log->verifier->name }}
                                                    @else
                                                        -
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                {{-- @endif --}}
                            </tr>
                            @empty
                            <tr><td colspan="{{ auth()->user()->role == 'admin' ? 6 : 5 }}" class="text-center py-4 text-muted">Belum ada riwayat scan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $logs->links() }}</div>
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