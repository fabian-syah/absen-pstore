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
            
            {{-- ====================================================== --}}
            {{-- TAMPILAN DESKTOP (LAPTOP) --}}
            {{-- ====================================================== --}}
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
                                                    <div class="small text-muted">{{ $att->user->division->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column small">
                                                <span class="text-success fw-bold">Masuk: {{ $checkInLocal->format('H:i') }}</span>
                                                @if($checkOutLocal)
                                                    <span class="text-danger fw-bold">Pulang: {{ $checkOutLocal->format('H:i') }}</span>
                                                @endif
                                                <span class="text-muted">{{ $checkInLocal->format('d M Y') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate-multiline small text-muted">
                                                {{ $att->notes ?? '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ Storage::url($att->photo_path) }}" class="image-popup">
                                                <img src="{{ Storage::url($att->photo_path) }}" class="rounded shadow-sm" width="50" height="50" style="object-fit: cover;">
                                            </a>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <form action="{{ route('audit.approve', $att->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="btn btn-soft-success"><i class="mdi mdi-check"></i></button>
                                                </form>
                                                <button type="button" class="btn btn-soft-warning" data-bs-toggle="modal" data-bs-target="#editDesktop{{ $att->id }}"><i class="mdi mdi-pencil"></i></button>
                                                <form action="{{ route('audit.reject', $att->id) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-soft-danger"><i class="mdi mdi-close"></i></button>
                                                </form>
                                            </div>

                                            {{-- Modal Edit Desktop --}}
                                            <div class="modal fade text-start" id="editDesktop{{ $att->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Koreksi {{ $att->user->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold text-uppercase">Jam Masuk ({{ $tzLabel }})</label>
                                                                    <input type="time" name="check_in_time" class="form-control" value="{{ $checkInLocal->format('H:i') }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold text-uppercase">Status</label>
                                                                    <select name="presence_status" class="form-select">
                                                                        <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                                                                        <option value="Telat" {{ $att->presence_status == 'Telat' ? 'selected' : '' }}>Telat</option>
                                                                    </select>
                                                                </div>
                                                                <input type="hidden" name="status" value="verified">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-primary w-100 fw-bold">Update & Verifikasi</button>
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
                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                    <small class="text-muted">Laptop Mode: Menampilkan semua data tanpa pagination.</small>
                    <span class="small fw-bold">Total: {{ $pendingAttendances->count() }} Item</span>
                </div>
            </div>

            {{-- ====================================================== --}}
            {{-- TAMPILAN MOBILE (HP / IPHONE) --}}
            {{-- ====================================================== --}}
            <div class="d-md-none">
                @foreach ($pendingAttendances as $att)
                    @php
                        $userTimezone = $att->user->branch->timezone ?? 'Asia/Jakarta';
                        $checkInLocal = Carbon::parse($att->check_in_time)->timezone($userTimezone);
                        $tzLabel = str_contains($userTimezone, 'Jakarta') ? 'WIB' : (str_contains($userTimezone, 'Makassar') ? 'WITA' : 'WIT');
                    @endphp
                    <div class="card shadow-sm border-0 mb-3" style="border-radius: 15px;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm me-2">
                                    <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold">{{ substr($att->user->name, 0, 1) }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold">{{ Str::limit($att->user->name, 20) }}</h6>
                                    <small class="text-muted">{{ $att->user->division->name ?? '-' }}</small>
                                </div>
                            </div>
                            <div class="row g-0 bg-light rounded p-2 mb-3">
                                <div class="col-6 text-center border-end">
                                    <small class="text-muted d-block xxs">MASUK</small>
                                    <span class="fw-bold text-success">{{ $checkInLocal->format('H:i') }}</span>
                                </div>
                                <div class="col-6 text-center">
                                    <small class="text-muted d-block xxs">TANGGAL</small>
                                    <span class="fw-bold">{{ $checkInLocal->format('d/m/y') }}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="{{ route('audit.approve', $att->id) }}" method="POST" class="flex-grow-1">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-success btn-sm w-100 fw-bold rounded-pill">Setujui</button>
                                </form>
                                <button type="button" class="btn btn-warning btn-sm flex-grow-1 fw-bold rounded-pill" data-bs-toggle="modal" data-bs-target="#editMob{{ $att->id }}">Edit</button>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Edit Mobile --}}
                    <div class="modal fade" id="editMob{{ $att->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" class="w-100">
                                @csrf @method('PUT')
                                <div class="modal-content" style="border-radius: 20px;">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold">Koreksi Data</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="small fw-bold">JAM MASUK ({{ $tzLabel }})</label>
                                        <input type="time" name="check_in_time" class="form-control mb-3" value="{{ $checkInLocal->format('H:i') }}" required>
                                        <input type="hidden" name="presence_status" value="Masuk">
                                        <input type="hidden" name="status" value="verified">
                                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-pill">Simpan & Verifikasi</button>
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
                <i class="mdi mdi-check-all display-1 text-muted opacity-25"></i>
                <h5 class="mt-3 text-muted">Tidak ada absensi yang perlu diverifikasi.</h5>
            </div>
        @endif
    </div>

    {{-- Modal Image Global --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0 shadow-none text-center">
                <img id="modalImage" src="" class="img-fluid rounded shadow" style="max-height: 80vh;">
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            const modalImage = document.getElementById('modalImage');

            document.querySelectorAll('.image-popup').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    modalImage.src = this.getAttribute('href') || this.querySelector('img').src;
                    imageModal.show();
                });
            });
        });
    </script>
    <style>
        .bg-soft-primary { background: rgba(59, 130, 246, 0.1); }
        .btn-soft-success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: none; }
        .btn-soft-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; }
        .btn-soft-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: none; }
        .xxs { font-size: 0.6rem; letter-spacing: 0.5px; }
        .text-truncate-multiline {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden; text-overflow: ellipsis; white-space: normal;
        }
    </style>
@endpush