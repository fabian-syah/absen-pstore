@extends('layout.master')

@section('title', 'Job Desk / Target')
@section('heading', 'Job Desk & Target')

@section('content')

{{-- ======================================================================= --}}
{{-- BAGIAN 1: TARGET PRIBADI (HARIAN) --}}
{{-- ======================================================================= --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-white"><i class="mdi mdi-account me-2"></i>Target Pribadi ({{ \Carbon\Carbon::now()->format('d M Y') }})</h5>
            </div>
            <div class="card-body">
                
                {{-- Form Tambah Cepat --}}
                <form action="{{ route('job-targets.store') }}" method="POST" class="d-flex gap-2 mb-4">
                    @csrf
                    <input type="hidden" name="type" value="individual">
                    <input type="text" name="title" class="form-control" placeholder="Tulis target/pekerjaan pribadi hari ini... (Tekan Enter)" required>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-plus"></i> Tambah</button>
                </form>

                {{-- List Checklist Pribadi --}}
                <ul class="list-group list-group-flush">
                    @forelse($myDailyTargets as $target)
                        <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 border-bottom">
                            <div class="d-flex align-items-center">
                                {{-- Form Checklist --}}
                                <form action="{{ route('job-targets.toggle', $target->id) }}" method="POST" class="me-3">
                                    @csrf
                                    @method('PATCH')
                                    <div class="form-check m-0">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" onchange="this.form.submit()" 
                                                {{ $target->status == 'completed' ? 'checked' : '' }} 
                                                style="width: 20px; height: 20px; cursor: pointer;">
                                            <i class="input-helper"></i>
                                        </label>
                                    </div>
                                </form>
                                
                                {{-- Teks Target --}}
                                <span class="{{ $target->status == 'completed' ? 'text-decoration-line-through text-muted' : 'fw-bold text-dark' }}" style="font-size: 16px;">
                                    {{ $target->title }}
                                </span>
                            </div>

                            {{-- Tombol Hapus --}}
                            <form action="{{ route('job-targets.destroy', $target->id) }}" method="POST" onsubmit="return confirm('Hapus target ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-inverse-danger btn-icon btn-sm"><i class="mdi mdi-close"></i></button>
                            </form>
                        </li>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="mdi mdi-clipboard-text-outline mdi-48px"></i>
                            <p class="mt-2">Belum ada target harian pribadi.</p>
                        </div>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================================= --}}
{{-- BAGIAN 2: TARGET TIM / CABANG --}}
{{-- ======================================================================= --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="mdi mdi-account-group me-2"></i>Target Tim / Cabang</h5>
                
                {{-- Tombol Tambah Target Tim (Muncul jika punya akses) --}}
                @if(in_array(Auth::user()->role, ['admin', 'leader', 'audit']))
                     <button type="button" class="btn btn-light btn-sm text-success fw-bold" data-bs-toggle="modal" data-bs-target="#createTeamTargetModal">
                        <i class="mdi mdi-plus"></i> Buat Target Tim
                    </button>
                @endif
            </div>
            
            <div class="card-body">
                {{-- NAVIGATION TABS (Hanya untuk memilah Harian/Bulanan/Tahunan Tim) --}}
                <ul class="nav nav-tabs" id="teamTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="team-harian-tab" data-bs-toggle="tab" data-bs-target="#team-harian" type="button" role="tab">
                            <i class="mdi mdi-calendar-today me-1"></i>Harian
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="team-bulanan-tab" data-bs-toggle="tab" data-bs-target="#team-bulanan" type="button" role="tab">
                            <i class="mdi mdi-calendar-month me-1"></i>Bulanan
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="team-tahunan-tab" data-bs-toggle="tab" data-bs-target="#team-tahunan" type="button" role="tab">
                            <i class="mdi mdi-calendar-clock me-1"></i>Tahunan
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-4" id="teamTabContent">
                    {{-- TAB 1: TIM HARIAN --}}
                    <div class="tab-pane fade show active" id="team-harian" role="tabpanel">
                        {{-- Menggunakan Include yang sudah ada atau Table Manual --}}
                        @if(count($teamDaily) > 0)
                            @include('job_targets.partials.team_table', ['targets' => $teamDaily, 'period' => 'daily', 'title' => 'Harian'])
                        @else
                            <div class="alert alert-light text-center">Tidak ada target tim harian yang aktif.</div>
                        @endif
                    </div>

                    {{-- TAB 2: TIM BULANAN --}}
                    <div class="tab-pane fade" id="team-bulanan" role="tabpanel">
                        @if(count($teamMonthly) > 0)
                            @include('job_targets.partials.team_table', ['targets' => $teamMonthly, 'period' => 'monthly', 'title' => 'Bulanan'])
                        @else
                            <div class="alert alert-light text-center">Tidak ada target tim bulanan periode ini.</div>
                        @endif
                    </div>

                    {{-- TAB 3: TIM TAHUNAN --}}
                    <div class="tab-pane fade" id="team-tahunan" role="tabpanel">
                        @if(count($teamYearly) > 0)
                            @include('job_targets.partials.team_table', ['targets' => $teamYearly, 'period' => 'yearly', 'title' => 'Tahunan'])
                        @else
                            <div class="alert alert-light text-center">Tidak ada target tim tahunan periode ini.</div>
                        @endif
                    </div>
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