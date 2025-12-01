@extends('layout.master')

@section('title', 'Job Desk / Target')
@section('heading', 'Job Desk & Target')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-body">
            
            {{-- NAVIGATION TABS --}}
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pribadi-tab" data-bs-toggle="tab" data-bs-target="#pribadi" type="button" role="tab">
                        <i class="mdi mdi-account me-1"></i>Pribadi (Harian)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="team-harian-tab" data-bs-toggle="tab" data-bs-target="#team-harian" type="button" role="tab">
                        <i class="mdi mdi-account-group me-1"></i>Tim (Harian)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="team-bulanan-tab" data-bs-toggle="tab" data-bs-target="#team-bulanan" type="button" role="tab">
                        <i class="mdi mdi-calendar-month me-1"></i>Tim (Bulanan)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="team-tahunan-tab" data-bs-toggle="tab" data-bs-target="#team-tahunan" type="button" role="tab">
                        <i class="mdi mdi-calendar-clock me-1"></i>Tim (Tahunan)
                    </button>
                </li>
            </ul>

            <div class="tab-content pt-4" id="myTabContent">
                
                {{-- TAB 1: PRIBADI HARIAN --}}
                <div class="tab-pane fade show active" id="pribadi" role="tabpanel">
                    <h5 class="card-title mb-3">Target Harian Saya ({{ \Carbon\Carbon::now()->format('d M Y') }})</h5>
                    
                    {{-- Form Tambah Cepat --}}
                    <form action="{{ route('job-targets.store') }}" method="POST" class="d-flex gap-2 mb-4">
                        @csrf
                        <input type="hidden" name="type" value="individual">
                        <input type="text" name="title" class="form-control" placeholder="Tulis target/pekerjaan hari ini... (Lalu tekan Enter/Simpan)" required>
                        <button type="submit" class="btn btn-primary"><i class="mdi mdi-plus"></i></button>
                    </form>

                    {{-- List Checklist --}}
                    <ul class="list-group list-group-flush">
                        @forelse($myDailyTargets as $target)
                            <li class="list-group-item d-flex align-items-center justify-content-between px-0">
                                <div class="d-flex align-items-center">
                                    {{-- Form Checklist (Langsung Submit saat diklik) --}}
                                    <form action="{{ route('job-targets.toggle', $target->id) }}" method="POST" class="me-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" onchange="this.form.submit()" 
                                                    {{ $target->status == 'completed' ? 'checked' : '' }} 
                                                    style="width: 20px; height: 20px;">
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </form>
                                    
                                    {{-- Teks (Dicoret jika selesai) --}}
                                    <span class="{{ $target->status == 'completed' ? 'text-decoration-line-through text-muted' : 'fw-medium' }}" style="font-size: 16px;">
                                        {{ $target->title }}
                                    </span>
                                </div>

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('job-targets.destroy', $target->id) }}" method="POST" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-inverse-danger btn-sm p-2"><i class="mdi mdi-close"></i></button>
                                </form>
                            </li>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="mdi mdi-clipboard-text-outline mdi-48px"></i>
                                <p>Belum ada target hari ini.</p>
                            </div>
                        @endforelse
                    </ul>
                </div>

                {{-- TAB 2: TIM HARIAN --}}
                <div class="tab-pane fade" id="team-harian" role="tabpanel">
                    @include('job_targets.partials.team_table', ['targets' => $teamDaily, 'period' => 'daily', 'title' => 'Harian'])
                </div>

                {{-- TAB 3: TIM BULANAN --}}
                <div class="tab-pane fade" id="team-bulanan" role="tabpanel">
                    @include('job_targets.partials.team_table', ['targets' => $teamMonthly, 'period' => 'monthly', 'title' => 'Bulanan'])
                </div>

                {{-- TAB 4: TIM TAHUNAN --}}
                <div class="tab-pane fade" id="team-tahunan" role="tabpanel">
                    @include('job_targets.partials.team_table', ['targets' => $teamYearly, 'period' => 'yearly', 'title' => 'Tahunan'])
                </div>

            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH TARGET TIM (Hanya muncul jika punya hak akses) --}}
@if(in_array(Auth::user()->role, ['admin', 'leader', 'audit']))
    @include('job_targets.partials.create_modal')
@endif

@endsection