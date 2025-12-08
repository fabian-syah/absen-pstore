@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title"><i class="mdi mdi-history me-2"></i>Riwayat Scan Security</h4>
                    
                    {{-- Filter Tanggal Sederhana --}}
                    <form action="{{ route('security.history') }}" method="GET" class="d-flex">
                        <input type="date" name="date" class="form-control form-control-sm me-2" value="{{ request('date') }}">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal & Jam</th>
                                <th>Karyawan</th>
                                <th>Divisi & Cabang</th>
                                <th>Status Scan</th>
                                <th>Bukti Foto</th>
                                @if(auth()->user()->role == 'admin')
                                    <th>Oleh Security</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $log->check_in_time ? \Carbon\Carbon::parse($log->check_in_time)->format('d M Y') : '-' }}</div>
                                    <small class="text-muted">
                                        In: {{ $log->check_in_time ? \Carbon\Carbon::parse($log->check_in_time)->format('H:i') : '-' }} <br>
                                        Out: {{ $log->check_out_time ? \Carbon\Carbon::parse($log->check_out_time)->format('H:i') : '-' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $log->user->profile_photo_path ? asset('storage/' . $log->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($log->user->name) }}" 
                                             class="rounded-circle me-2" 
                                             style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                             onclick="showImage(this.src, 'Foto Profil: {{ $log->user->name }}')"
                                             alt="Profile">
                                        <div>
                                            <div class="fw-bold">{{ $log->user->name }}</div>
                                            <small class="text-muted">{{ $log->user->role }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $log->user->division->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $log->branch->name ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($log->is_late_checkin)
                                        <span class="badge badge-danger">Telat</span>
                                    @else
                                        <span class="badge badge-success">Tepat Waktu</span>
                                    @endif
                                    
                                    @if($log->check_out_time)
                                        @if($log->is_early_checkout)
                                            <span class="badge badge-warning">Pulang Cepat</span>
                                        @else
                                            <span class="badge badge-info">Pulang Normal</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        {{-- Foto Absen Masuk --}}
                                        @if($log->photo_path)
                                            <button class="btn btn-sm btn-outline-success py-1 px-2" 
                                                onclick="showImage('{{ asset('storage/'.$log->photo_path) }}', 'Bukti Masuk - {{ $log->user->name }}')">
                                                <i class="mdi mdi-camera"></i> Masuk
                                            </button>
                                        @endif

                                        {{-- Foto Absen Pulang --}}
                                        @if($log->photo_out_path)
                                            <button class="btn btn-sm btn-outline-warning py-1 px-2" 
                                                onclick="showImage('{{ asset('storage/'.$log->photo_out_path) }}', 'Bukti Pulang - {{ $log->user->name }}')">
                                                <i class="mdi mdi-camera"></i> Pulang
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                
                                {{-- Kolom Khusus Admin: Menampilkan siapa security yang scan --}}
                                @if(auth()->user()->role == 'admin')
                                <td>
                                    <small>
                                        In: {{ $log->scanner->name ?? '-' }} <br>
                                        Out: {{ $log->verifier->name ?? '-' }}
                                    </small>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat scan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL POPUP GAMBAR --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalImageTitle">Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImageSrc" src="" class="img-fluid" style="max-height: 80vh;">
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
@endsection