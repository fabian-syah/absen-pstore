@php
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;
@endphp

@extends('layout.master')

@section('title')
    History Absensi Ditolak
@endsection

@section('content')
    <div class="container-fluid px-0">
        {{-- Header Section --}}
        <div class="rejected-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="mdi mdi-history me-2 text-danger"></i>History Absensi Ditolak
                    </h4>
                    <p class="text-muted small mb-0">Daftar absensi mandiri yang tidak disetujui oleh Audit</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('audit.verify.list') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Verifikasi
                    </a>
                </div>
            </div>
        </div>

        @if($rejectedAttendances->count() > 0)
            {{-- Desktop View --}}
            <div class="card card-modern d-none d-md-block">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-modern">
                            <thead>
                                <tr>
                                    <th class="ps-4">Karyawan</th>
                                    <th>Waktu Absen</th>
                                    <th>Lokasi</th>
                                    <th>Bukti Foto</th>
                                    <th>Ditolak Oleh</th>
                                    <th class="pe-4">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rejectedAttendances as $att)
                                    @php
                                        $userTimezone = $att->user?->branch?->timezone ?? 'Asia/Jakarta';
                                        $checkInLocal = Carbon::parse($att->check_in_time)->timezone($userTimezone);
                                        $tzLabel = str_contains($userTimezone, 'Jakarta') ? 'WIB' : (str_contains($userTimezone, 'Makassar') ? 'WITA' : 'WIT');
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <h6 class="mb-0 fw-semibold">{{ $att->user?->name ?? 'User Terhapus' }}</h6>
                                            <small class="text-muted">{{ $att->user?->branch?->name ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $checkInLocal->format('H:i') }} <small
                                                    class="text-muted">{{ $tzLabel }}</small></div>
                                            <small class="text-muted">{{ $checkInLocal->format('d M Y') }}</small>
                                        </td>
                                        <td>
                                            @if($att->latitude && $att->longitude)
                                                <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}"
                                                    target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3">
                                                    Maps
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="photo-thumb image-popup" data-img="{{ Storage::url($att->photo_path) }}">
                                                <img src="{{ Storage::url($att->photo_path) }}" alt="Foto">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="badge bg-light text-dark border">{{ $att->verifier?->name ?? ($att->verified_by_user_id ? 'User Terhapus' : 'System') }}
                                            </div>
                                        </td>
                                        <td class="pe-4">
                                            <small class="text-muted">{{ $att->audit_note ?? '-' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent py-3">
                    {{ $rejectedAttendances->links('pagination::bootstrap-5') }}
                </div>
            </div>

            {{-- Mobile View --}}
            <div class="d-md-none">
                @foreach ($rejectedAttendances as $att)
                    @php
                        $userTimezone = $att->user?->branch?->timezone ?? 'Asia/Jakarta';
                        $checkInLocal = Carbon::parse($att->check_in_time)->timezone($userTimezone);
                    @endphp
                    <div class="card card-mobile-modern mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold mb-0">{{ $att->user?->name ?? 'User Terhapus' }}</h6>
                                <span class="badge bg-danger rounded-pill">Ditolak</span>
                            </div>
                            <div class="mb-2 small">
                                <i class="mdi mdi-clock-outline me-1"></i> {{ $checkInLocal->format('d/m/Y H:i') }}
                            </div>
                            <div class="mb-3 small text-muted">
                                <i class="mdi mdi-account-cancel me-1"></i> Oleh: {{ $att->verifier?->name ?? ($att->verified_by_user_id ? 'User Terhapus' : 'System') }}
                            </div>
                            <div class="p-2 bg-light rounded small border-start border-danger border-4">
                                {{ $att->audit_note ?? 'Tanpa keterangan' }}
                            </div>
                        </div>
                    </div>
                @endforeach
                {{ $rejectedAttendances->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon bg-light text-muted">
                    <i class="mdi mdi-history"></i>
                </div>
                <h5 class="fw-bold">Belum Ada History</h5>
                <p class="text-muted small">Daftar absensi yang ditolak akan muncul di sini.</p>
            </div>
        @endif
    </div>

    {{-- Global Image Modal --}}
    <div class="modal fade" id="imgGlobalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0 shadow-none text-center">
                <img id="imgGlobalSrc" src="" class="img-fluid rounded-4 shadow-lg" style="max-height: 80vh;">
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imgModal = new bootstrap.Modal(document.getElementById('imgGlobalModal'));
            const imgSrc = document.getElementById('imgGlobalSrc');

            document.querySelectorAll('.image-popup').forEach(el => {
                el.addEventListener('click', function () {
                    imgSrc.src = this.getAttribute('data-img');
                    imgModal.show();
                });
            });
        });
    </script>
    <style>
        .rejected-header {
            padding: 1.5rem;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .card-modern {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .table-modern thead th {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            padding: 1rem 0.75rem;
            background: #f8fafc;
            border: none;
        }

        .photo-thumb {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
        }

        .photo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-mobile-modern {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: #fff;
            border-radius: 16px;
        }

        .empty-state-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
        }
    </style>
@endpush