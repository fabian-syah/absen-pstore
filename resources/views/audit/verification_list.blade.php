@php
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;
@endphp

@extends('layout.master')

@section('title')
    Verifikasi Absensi
@endsection

@section('content')
    <div class="container-fluid px-0">
        {{-- Header Section --}}
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h4 class="card-title text-primary mb-1">Verifikasi Absensi</h4>
                <p class="text-muted small mb-0">Kelola persetujuan absensi manual karyawan.</p>
            </div>
            <div class="col-auto">
                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill shadow-sm">
                    <i class="mdi mdi-clipboard-alert-outline me-1"></i>
                    {{ $pendingAttendances->total() }} Menunggu
                </span>
            </div>
        </div>

        {{-- Alert Notification --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-check-circle-outline fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-alert-circle-outline fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($pendingAttendances->count() > 0)
            
            {{-- ================================================================================= --}}
            {{-- TAMPILAN DESKTOP (LAPTOP) - TABLE MODE                                            --}}
            {{-- ================================================================================= --}}
            <div class="card shadow-sm border-0 d-none d-md-block rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase font-size-11 fw-bold">Karyawan</th>
                                    <th class="py-3 text-uppercase font-size-11 fw-bold" style="min-width: 180px;">Waktu & Lokasi</th>
                                    <th class="py-3 text-uppercase font-size-11 fw-bold" style="width: 220px;">Catatan</th>
                                    <th class="py-3 text-uppercase font-size-11 fw-bold">Bukti Foto</th>
                                    <th class="text-end pe-4 py-3 text-uppercase font-size-11 fw-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingAttendances as $att)
                                    @php
                                        $userTimezone = $att->user->branch->timezone ?? 'Asia/Jakarta';
                                        $checkInLocal = Carbon::parse($att->check_in_time)->timezone($userTimezone);
                                        $checkOutLocal = $att->check_out_time ? Carbon::parse($att->check_out_time)->timezone($userTimezone) : null;
                                        $tzLabel = str_contains($userTimezone, 'Jakarta') ? 'WIB' : (str_contains($userTimezone, 'Makassar') ? 'WITA' : 'WIT');
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold">
                                                        {{ substr($att->user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $att->user->name }}</h6>
                                                    <div class="small text-muted">
                                                        {{ $att->user->division->name ?? 'Staff' }} 
                                                        <span class="mx-1">•</span> 
                                                        <span class="text-primary fw-medium">{{ $att->user->branch->name ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column small">
                                                <span class="text-success fw-bold">In: {{ $checkInLocal->format('H:i') }}</span>
                                                @if($checkOutLocal)
                                                    <span class="text-danger fw-bold">Out: {{ $checkOutLocal->format('H:i') }}</span>
                                                @endif
                                                <span class="text-muted">{{ $checkInLocal->format('d M Y') }} ({{ $tzLabel }})</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate-multiline small text-muted">
                                                {{ $att->notes ?? '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="image-popup" data-img="{{ Storage::url($att->photo_path) }}">
                                                <img src="{{ Storage::url($att->photo_path) }}" class="rounded border" width="45" height="45" style="object-fit: cover; cursor: pointer;">
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <form action="{{ route('audit.approve', $att->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="btn btn-soft-success"><i class="mdi mdi-check"></i></button>
                                                </form>
                                                <button type="button" class="btn btn-soft-warning" data-bs-toggle="modal" data-bs-target="#editDesk{{ $att->id }}"><i class="mdi mdi-pencil"></i></button>
                                                <form action="{{ route('audit.reject', $att->id) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-soft-danger" onclick="return confirm('Hapus data?')"><i class="mdi mdi-close"></i></button>
                                                </form>
                                            </div>

                                            {{-- Modal Edit Desktop --}}
                                            <div class="modal fade text-start" id="editDesk{{ $att->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-content rounded-4 border-0">
                                                            <div class="modal-header bg-primary text-white border-0">
                                                                <h5 class="modal-title fw-bold">Koreksi Absensi</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Jam Masuk ({{ $tzLabel }})</label>
                                                                    <input type="time" name="check_in_time" class="form-control" value="{{ $checkInLocal->format('H:i') }}" required>
                                                                </div>
                                                                <input type="hidden" name="presence_status" value="Masuk">
                                                                <input type="hidden" name="status" value="verified">
                                                                <div class="mb-0">
                                                                    <label class="form-label fw-bold">Catatan Audit</label>
                                                                    <textarea name="audit_note" class="form-control" rows="2">{{ $att->audit_note }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0">
                                                                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">Update & Verifikasi</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-center">
                    <span class="text-muted small">Mode Laptop: Menampilkan semua data terbaru (Total: {{ $pendingAttendances->count() }})</span>
                </div>
            </div>

            {{-- ================================================================================= --}}
            {{-- TAMPILAN MOBILE (HP / IPHONE) - CARD MODE                                         --}}
            {{-- ================================================================================= --}}
            <div class="d-md-none">
                @foreach ($pendingAttendances as $att)
                    @php
                        $userTimezone = $att->user->branch->timezone ?? 'Asia/Jakarta';
                        $checkInLocal = Carbon::parse($att->check_in_time)->timezone($userTimezone);
                        $tzLabel = str_contains($userTimezone, 'Jakarta') ? 'WIB' : (str_contains($userTimezone, 'Makassar') ? 'WITA' : 'WIT');
                    @endphp
                    <div class="card shadow-sm border-0 mb-3 rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm me-2">
                                    <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold">{{ substr($att->user->name, 0, 1) }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold">{{ Str::limit($att->user->name, 22) }}</h6>
                                    <div class="d-flex align-items-center">
                                        <small class="text-primary fw-bold">{{ $att->user->branch->name ?? '-' }}</small>
                                        <small class="text-muted ms-1">({{ $tzLabel }})</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-0 bg-light rounded-3 p-2 mb-3 text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block xxs">MASUK</small>
                                    <span class="fw-bold text-success">{{ $checkInLocal->format('H:i') }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block xxs">TANGGAL</small>
                                    <span class="fw-bold text-dark">{{ $checkInLocal->format('d/m/y') }}</span>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="image-popup" data-img="{{ Storage::url($att->photo_path) }}">
                                        <img src="{{ Storage::url($att->photo_path) }}" class="img-fluid rounded border" style="height: 65px; width: 100%; object-fit: cover;">
                                    </div>
                                </div>
                                <div class="col-8">
                                    @if($att->latitude && $att->longitude)
                                        <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="btn btn-outline-info w-100 h-100 d-flex align-items-center justify-content-center btn-sm rounded-3">
                                            <i class="mdi mdi-map-marker-radius me-1"></i> Peta Lokasi
                                        </a>
                                    @else
                                        <div class="h-100 border rounded-3 d-flex align-items-center justify-content-center text-muted small bg-light">No Loc</div>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <form action="{{ route('audit.approve', $att->id) }}" method="POST" class="flex-grow-1">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-success btn-sm w-100 fw-bold rounded-pill py-2 shadow-sm">Terima</button>
                                </form>
                                <button type="button" class="btn btn-warning btn-sm flex-grow-1 fw-bold rounded-pill py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#editMob{{ $att->id }}">Edit</button>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Edit Mobile --}}
                    <div class="modal fade" id="editMob{{ $att->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered mx-3">
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" class="w-100">
                                @csrf @method('PUT')
                                <div class="modal-content rounded-4 shadow-lg border-0">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold">Koreksi Data</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="small fw-bold text-muted mb-1">JAM MASUK ({{ $tzLabel }})</label>
                                            <input type="time" name="check_in_time" class="form-control form-control-lg" value="{{ $checkInLocal->format('H:i') }}" required>
                                        </div>
                                        <input type="hidden" name="presence_status" value="Masuk">
                                        <input type="hidden" name="status" value="verified">
                                        <button type="submit" class="btn btn-primary w-100 fw-bold py-3 rounded-pill shadow">Simpan & Setujui</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- PAGINATION KHUSUS MOBILE --}}
                <div class="mt-4 mb-5 d-flex justify-content-center">
                    {{ $pendingAttendances->links('pagination::bootstrap-5') }}
                </div>
            </div>

        @else
            <div class="text-center py-5">
                <div class="mb-3 opacity-25">
                    <i class="mdi mdi-check-all display-1 text-muted"></i>
                </div>
                <h5 class="text-muted fw-bold">Antrean Kosong</h5>
                <p class="text-muted small">Semua absensi manual telah diproses.</p>
            </div>
        @endif
    </div>

    {{-- Global Image Modal --}}
    <div class="modal fade" id="imgGlobalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0 shadow-none text-center">
                <img id="imgGlobalSrc" src="" class="img-fluid rounded shadow-lg" style="max-height: 80vh;">
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tooltip Init
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el) });

            // Image Handler
            const imgModal = new bootstrap.Modal(document.getElementById('imgGlobalModal'));
            const imgSrc = document.getElementById('imgGlobalSrc');

            document.querySelectorAll('.image-popup').forEach(el => {
                el.addEventListener('click', function() {
                    imgSrc.src = this.getAttribute('data-img');
                    imgModal.show();
                });
            });
        });
    </script>
    <style>
        .bg-soft-primary { background: rgba(59, 130, 246, 0.12); }
        .btn-soft-success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: none; }
        .btn-soft-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: none; }
        .btn-soft-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; }
        .xxs { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; }
        .text-truncate-multiline {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden; text-overflow: ellipsis; white-space: normal;
        }
        .image-popup { cursor: zoom-in; }
    </style>
@endpush