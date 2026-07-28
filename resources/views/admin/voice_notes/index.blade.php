@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Bukti VN Suara</h2>
            <p class="text-muted">Daftar rekaman suara saat absen (Hukuman Jutek & Ngerokok)</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Karyawan</th>
                            <th>Tanggal Absen</th>
                            <th>Jam Masuk</th>
                            <th>Bukti Suara (VN)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $attendance->user->photo_url ?? asset('images/default_avatar.jpg') }}" class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold">{{ $attendance->user->name }}</div>
                                        <small class="text-muted">{{ $attendance->user->division }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($attendance->check_in_time)->translatedFormat('l, d F Y') }}</td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                    {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}
                                </span>
                            </td>
                            <td>
                                @if($attendance->voice_note_path)
                                    <audio controls class="w-100" style="max-width: 300px; height: 40px;">
                                        <source src="{{ asset('storage/' . $attendance->voice_note_path) }}" type="audio/webm">
                                        <source src="{{ asset('storage/' . $attendance->voice_note_path) }}" type="audio/mpeg">
                                        Browser Anda tidak mendukung elemen audio.
                                    </audio>
                                @else
                                    <span class="text-muted fst-italic">Tidak ada rekaman</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-microphone-slash fa-3x mb-3 text-light"></i>
                                <h5>Belum ada data Voice Note</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        {{ $attendances->links() }}
    </div>
</div>
@endsection
