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

        {{-- Alert Success --}}
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
            
            {{-- TAMPILAN DESKTOP (Table) - Hidden di Mobile --}}
            <div class="card shadow-sm border-0 d-none d-md-block">
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
                                        
                                        $tzLabel = '';
                                        if (str_contains($userTimezone, 'Jakarta') || str_contains($userTimezone, 'Pontianak')) $tzLabel = 'WIB';
                                        elseif (str_contains($userTimezone, 'Makassar') || str_contains($userTimezone, 'Bali')) $tzLabel = 'WITA';
                                        elseif (str_contains($userTimezone, 'Jayapura')) $tzLabel = 'WIT';
                                        else $tzLabel = $userTimezone;
                                    @endphp

                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    @if($att->user)
                                                        <a href="{{ route('users.show', $att->user->id) }}" class="text-decoration-none">
                                                            <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold hover-scale">
                                                                {{ substr($att->user->name, 0, 1) }}
                                                            </span>
                                                        </a>
                                                    @else
                                                        <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold">U</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($att->user)
                                                        <a href="{{ route('users.show', $att->user->id) }}" class="text-dark text-decoration-none">
                                                            <h6 class="mb-0 fw-bold hover-text-primary">
                                                                {{ $att->user->name }}
                                                                @if($att->user_id == Auth::id())
                                                                    <span class="badge bg-info ms-1" style="font-size: 0.6rem;">ANDA</span>
                                                                @endif
                                                            </h6>
                                                        </a>
                                                    @else
                                                        <h6 class="mb-0 text-dark fw-bold">User Dihapus</h6>
                                                    @endif
                                                    <div class="small text-muted mt-1">
                                                        <span class="badge badge-soft-secondary me-1">{{ $att->user->division->name ?? 'N/A' }}</span>
                                                        <span class="text-xs" data-bs-toggle="tooltip" title="Timezone: {{ $userTimezone }}">
                                                            <i class="mdi mdi-store-marker-outline text-warning"></i> 
                                                            {{ $att->user->branch->name ?? 'Non-Cabang' }}
                                                            <span class="badge bg-dark ms-1" style="font-size: 0.6rem;">{{ $tzLabel }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-2 py-2">
                                                <div>
                                                    <span class="badge bg-soft-success text-success mb-1">
                                                        <i class="mdi mdi-login me-1"></i>Masuk: {{ $checkInLocal->format('H:i') }} <small>{{ $tzLabel }}</small>
                                                    </span>
                                                    <div class="d-flex align-items-center">
                                                        @if($att->latitude && $att->longitude)
                                                            <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="text-muted small text-decoration-none hover-text-primary">
                                                                <i class="mdi mdi-map-marker me-1"></i>Peta Masuk
                                                            </a>
                                                        @else
                                                            <span class="text-muted small"><i class="mdi mdi-map-marker-off me-1"></i>No Loc</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($att->check_out_time)
                                                    <div class="border-top pt-2 mt-1 dashed-border">
                                                        <span class="badge bg-soft-danger text-danger mb-1">
                                                            <i class="mdi mdi-logout me-1"></i>Pulang: {{ $checkOutLocal->format('H:i') }} <small>{{ $tzLabel }}</small>
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            @if($att->latitude_out && $att->longitude_out)
                                                                <a href="https://maps.google.com/?q={{ $att->latitude_out }},{{ $att->longitude_out }}" target="_blank" class="text-muted small text-decoration-none hover-text-primary">
                                                                    <i class="mdi mdi-map-marker me-1"></i>Peta Pulang
                                                                </a>
                                                            @else
                                                                <span class="text-muted small"><i class="mdi mdi-map-marker-off me-1"></i>No Loc</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                <span class="small text-muted mt-1 border-top pt-1">
                                                    <i class="mdi mdi-calendar-blank me-1"></i>{{ $checkInLocal->format('d M Y') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($att->notes)
                                                <div class="p-2 bg-light rounded border border-light position-relative">
                                                    <div class="d-flex align-items-start">
                                                        <i class="mdi mdi-note-text-outline text-primary me-2 mt-1 flex-shrink-0"></i>
                                                        <div class="text-muted small fst-italic text-truncate-multiline" data-bs-toggle="tooltip" title="{{ $att->notes }}">
                                                            "{{ $att->notes }}"
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small ms-2">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ Storage::url($att->photo_path) }}" class="image-popup d-inline-block position-relative">
                                                <img src="{{ Storage::url($att->photo_path) }}" alt="foto" class="rounded shadow-sm object-fit-cover" width="60" height="60">
                                                <div class="overlay-icon">
                                                    <i class="mdi mdi-magnify text-white"></i>
                                                </div>
                                            </a>
                                            @if($att->check_out_time && $att->photo_out_path)
                                                 <div class="mt-1">
                                                    <span class="badge bg-light text-muted border" style="font-size: 0.6rem;">OUT</span>
                                                 </div>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('audit.approve', $att->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-soft-success btn-rounded" data-bs-toggle="tooltip" title="Setujui">
                                                        <i class="mdi mdi-check"></i> Terima
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-soft-warning btn-rounded" data-bs-toggle="modal" data-bs-target="#editAttendanceModal{{ $att->id }}" title="Koreksi Jam">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <form action="{{ route('audit.reject', $att->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger btn-rounded" data-bs-toggle="tooltip" title="Tolak">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            {{-- MODAL EDIT DESKTOP --}}
                                            <div class="modal fade text-start" id="editAttendanceModal{{ $att->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                                        @csrf @method('PUT')
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Koreksi Absensi ({{ $att->user->name }})</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="alert alert-info py-2 small">
                                                                    <i class="mdi mdi-information me-1"></i> Waktu input: <strong>{{ $tzLabel }}</strong>.
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Jam Masuk</label>
                                                                    <input type="time" name="check_in_time" class="form-control" value="{{ $checkInLocal->format('H:i') }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Jam Pulang <small class="text-muted">(Opsional)</small></label>
                                                                    <input type="time" name="check_out_time" class="form-control" value="{{ $checkOutLocal ? $checkOutLocal->format('H:i') : '' }}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status Kehadiran</label>
                                                                    <select name="presence_status" class="form-select">
                                                                        <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                                                                        <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>Izin</option>
                                                                        <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                                                        <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                                                                        <option value="WFH" {{ $att->presence_status == 'WFH' ? 'selected' : '' }}>WFH</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status Verifikasi</label>
                                                                    <select name="status" class="form-select">
                                                                        <option value="verified" selected>Verified</option>
                                                                        <option value="pending_verification">Pending</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Catatan Audit</label>
                                                                    <textarea name="audit_note" class="form-control" rows="2">{{ $att->audit_note }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan</button>
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
                {{-- PAGINATION DESKTOP - DISABLED (Hanya info jumlah) --}}
                <div class="card-footer bg-white border-top py-3 d-none d-md-block">
                    <div class="text-muted small text-center">Menampilkan {{ $pendingAttendances->count() }} data menunggu verifikasi di halaman ini.</div>
                </div>
            </div>

            {{-- TAMPILAN MOBILE (Cards) --}}
            <div class="d-md-none">
                @foreach ($pendingAttendances as $att)
                    @php
                        $userTimezone = $att->user->branch->timezone ?? 'Asia/Jakarta';
                        $checkInLocal = Carbon::parse($att->check_in_time)->timezone($userTimezone);
                        $checkOutLocal = $att->check_out_time ? Carbon::parse($att->check_out_time)->timezone($userTimezone) : null;
                        $tzLabel = str_contains($userTimezone, 'Jakarta') ? 'WIB' : (str_contains($userTimezone, 'Makassar') ? 'WITA' : 'WIT');
                    @endphp

                    <div class="card shadow-sm mb-3 border-0 attendance-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                @if($att->user)
                                    <a href="{{ route('users.show', $att->user->id) }}" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1">
                                        <div class="avatar-sm me-3">
                                            <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold">{{ substr($att->user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-primary">{{ $att->user->name }}</h6>
                                            <div class="small text-muted">{{ $att->user->division->name ?? 'N/A' }}</div>
                                        </div>
                                    </a>
                                @endif
                            </div>

                            <div class="bg-light rounded p-2 mb-3 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Cabang:</span>
                                    <span class="fw-semibold text-dark">{{ $att->user->branch->name ?? '-' }} ({{ $tzLabel }})</span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                    <div class="text-center w-50 border-end">
                                        <span class="d-block text-muted xxs">MASUK</span>
                                        <span class="fw-bold text-success">{{ $checkInLocal->format('H:i') }}</span>
                                    </div>
                                    <div class="text-center w-50">
                                        <span class="d-block text-muted xxs">PULANG</span>
                                        <span class="fw-bold text-danger">{{ $checkOutLocal ? $checkOutLocal->format('H:i') : '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <a href="{{ Storage::url($att->photo_path) }}" class="image-popup">
                                        <img src="{{ Storage::url($att->photo_path) }}" class="img-fluid rounded border w-100" style="height: 60px; object-fit: cover;">
                                    </a>
                                </div>
                                <div class="col-8">
                                    @if($att->latitude && $att->longitude)
                                        <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="btn btn-outline-info w-100 h-100 d-flex align-items-center justify-content-center btn-sm">
                                            <i class="mdi mdi-map-marker-radius me-2"></i> Peta Masuk
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <form action="{{ route('audit.approve', $att->id) }}" method="POST" class="w-50">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-success w-100 btn-sm"><i class="mdi mdi-check"></i> Terima</button>
                                </form>
                                <button type="button" class="btn btn-warning w-50 btn-sm" data-bs-toggle="modal" data-bs-target="#editAttendanceModalMobile{{ $att->id }}">
                                    <i class="mdi mdi-pencil"></i> Koreksi
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL EDIT MOBILE --}}
                    <div class="modal fade" id="editAttendanceModalMobile{{ $att->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" class="w-100">
                                @csrf @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Koreksi ({{ $att->user->name }})</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Masuk ({{ $tzLabel }})</label>
                                            <input type="time" name="check_in_time" class="form-control" value="{{ $checkInLocal->format('H:i') }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Pulang ({{ $tzLabel }})</label>
                                            <input type="time" name="check_out_time" class="form-control" value="{{ $checkOutLocal ? $checkOutLocal->format('H:i') : '' }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Status</label>
                                            <select name="presence_status" class="form-select">
                                                <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                                                <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>Izin</option>
                                                <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="status" value="verified">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary w-100">Simpan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
                
                {{-- PAGINATION MOBILE - ENABLED --}}
                <div class="mt-3 mb-5 d-md-none">
                    {{ $pendingAttendances->links('pagination::bootstrap-5') }}
                </div>
            </div>

        @else
            {{-- Empty State --}}
            <div class="card shadow-none bg-transparent">
                <div class="card-body text-center py-5">
                    <div class="avatar-lg mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-4">
                        <i class="mdi mdi-check-all display-4 text-primary opacity-25"></i>
                    </div>
                    <h5 class="text-dark">Semua Beres!</h5>
                    <p class="text-muted">Tidak ada data absensi yang perlu diverifikasi saat ini.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Image Global --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg bg-transparent">
                <div class="modal-body p-0 position-relative text-center">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-index-10" data-bs-dismiss="modal" aria-label="Close"></button>
                    <img id="modalImage" src="" class="img-fluid rounded shadow" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            const modalImage = document.getElementById('modalImage');

            document.querySelectorAll('.image-popup').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    let src = this.getAttribute('href') || this.querySelector('img').src;
                    modalImage.src = src;
                    imageModal.show();
                });
            });
        });
    </script>
    
    <style>
        .object-fit-cover { object-fit: cover; }
        .bg-soft-primary { background-color: rgba(59, 130, 246, 0.1); }
        .badge-soft-secondary { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }
        .btn-rounded { border-radius: 50px; padding-left: 15px; padding-right: 15px; }
        .btn-soft-success { background-color: rgba(16, 185, 129, 0.1); color: #10b981; border: none; }
        .btn-soft-success:hover { background-color: #10b981; color: white; }
        .btn-soft-warning { background-color: rgba(245, 158, 11, 0.1); color: #f59e0b; border: none; }
        .btn-soft-warning:hover { background-color: #f59e0b; color: white; }
        .image-popup { display: block; overflow: hidden; border-radius: 6px; cursor: pointer; }
        .overlay-icon {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.2s;
        }
        .image-popup:hover .overlay-icon { opacity: 1; }
        .font-size-11 { font-size: 11px; letter-spacing: 0.5px; }
        .xxs { font-size: 0.65rem; letter-spacing: 0.5px; text-transform: uppercase; }
        .dashed-border { border-top: 1px dashed #dee2e6 !important; }
        .text-truncate-multiline {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden; text-overflow: ellipsis; white-space: normal;
        }
    </style>
@endpush