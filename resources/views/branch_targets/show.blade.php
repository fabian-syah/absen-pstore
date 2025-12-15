@extends('layout.master')

@section('title', 'Detail Target Cabang')
@section('heading', $branch->name)

@section('content')

    {{-- HEADER & NAVIGATION --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <div>
            <a href="{{ route('branch-targets.index') }}"
                class="btn btn-light bg-white border shadow-sm btn-sm mb-2 rounded-3 fw-bold text-muted hover-scale">
                <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Daftar Cabang
            </a>
            <h3 class="fw-bold text-dark mb-1 d-flex align-items-center">
                <i class="mdi mdi-storefront text-primary me-2"></i> {{ $branch->name }}
            </h3>
            <p class="text-muted mb-0 d-flex align-items-center">
                <i class="mdi mdi-map-marker-radius text-danger me-1"></i>
                {{ $branch->address ?? 'Lokasi belum diatur' }}
            </p>
        </div>

        {{-- TOMBOL CREATE TARGET GLOBAL (Hanya Admin, Audit, Leader) --}}
        @if ($canManage)
            <div>
                <a href="{{ route('job-targets.create', ['branch_id' => $branch->id, 'type_preselect' => 'team', 'redirect_to_branch' => $branch->id]) }}"
                    class="btn btn-dark btn-lg shadow-lg rounded-4 px-4 fw-bold hover-scale w-100 w-md-auto">
                    <i class="mdi mdi-target me-1"></i> Buat Target Global Tim
                </a>
            </div>
        @endif
    </div>

    {{-- SECTION 1: TARGET GLOBAL CABANG --}}
    <div class="card card-rounded shadow-sm border-0 mb-5">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="mdi mdi-office-building text-primary mdi-24px"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">🏢 Target Global Cabang</h5>
                    <small class="text-muted">Target utama yang menjadi tanggung jawab satu tim penuh.</small>
                </div>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            {{-- Menggunakan Partial Tabs yang sudah ada --}}
            @include('job_targets.partials.period_tabs', [
                'idPrefix' => 'branch',
                'dataCollection' => $teamData,
                'allow_edit_detail' => $canManage,
                'allow_update_status' => $canManage,
            ])
        </div>
    </div>

    {{-- SECTION 2: DAFTAR ANGGOTA TIM --}}
    <div class="card card-rounded shadow-sm border-0 mb-5">
        <div class="card-header bg-gradient-info text-white border-bottom py-3">
            <div class="d-flex align-items-center">
                <div class="bg-white bg-opacity-25 p-2 rounded-circle me-3">
                    <i class="mdi mdi-account-group text-white mdi-24px"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-white">👥 Daftar Anggota Tim</h5>
                    <small class="text-white opacity-75">Kelola target personal untuk setiap karyawan.</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Nama Karyawan</th>
                            <th>Posisi</th>
                            <th class="text-center">Target Aktif</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branchMembers as $member)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold"
                                            style="width: 40px; height: 40px;">
                                            {{ substr($member->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">{{ $member->name }}</h6>
                                            <small class="text-muted">{{ $member->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $member->role }}</span></td>
                                <td class="text-center">
                                    @if ($member->active_targets_count > 0)
                                        <span
                                            class="badge bg-warning text-dark rounded-pill px-3">{{ $member->active_targets_count }}
                                            Pending</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        {{-- TOMBOL LIHAT DETAIL (MODAL) --}}
                                        <button type="button"
                                            class="btn btn-info btn-sm text-white rounded-pill px-3 fw-bold shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalTargets-{{ $member->id }}">
                                            <i class="mdi mdi-eye me-1"></i> Lihat
                                        </button>

                                        {{-- TOMBOL BERI TARGET --}}
                                        @if ($canManage)
                                            <a href="{{ route('job-targets.create', ['assign_user_id' => $member->id, 'branch_id' => $branch->id, 'redirect_to_branch' => $branch->id]) }}"
                                                class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">
                                                <i class="mdi mdi-plus-circle-outline me-1"></i> Target
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- MODAL LIST TARGET PER USER --}}
                            <div class="modal fade" id="modalTargets-{{ $member->id }}" tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content rounded-4 border-0 shadow-lg">
                                        <div class="modal-header bg-light border-bottom-0">
                                            <h5 class="modal-title fw-bold">
                                                🎯 Target: {{ $member->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            @if ($member->jobTargets->count() > 0)
                                                <div class="list-group list-group-flush">
                                                    @foreach ($member->jobTargets as $target)
                                                        <div class="list-group-item p-4 border-bottom">
                                                            <div
                                                                class="d-flex justify-content-between align-items-start mb-2">
                                                                <div class="w-100">
                                                                    <h6 class="fw-bold text-dark mb-1">{{ $target->title }}
                                                                    </h6>
                                                                    <p class="text-muted small mb-2">
                                                                        {{ Str::limit($target->description, 100) }}</p>

                                                                    {{-- Badge Status Logic --}}
                                                                    @php
                                                                        $statusConfig = match ($target->status) {
                                                                            'completed' => [
                                                                                'bg' => 'success',
                                                                                'text' => 'white',
                                                                            ], // Hijau - Teks Putih
                                                                            'in_progress' => [
                                                                                'bg' => 'info',
                                                                                'text' => 'white',
                                                                            ], // Biru - Teks Putih
                                                                            'rejected' => [
                                                                                'bg' => 'danger',
                                                                                'text' => 'white',
                                                                            ], // Merah - Teks Putih
                                                                            default => [
                                                                                'bg' => 'warning',
                                                                                'text' => 'dark',
                                                                            ], // Kuning - TEKS HITAM (Penting!)
                                                                        };
                                                                    @endphp

                                                                    <div class="d-flex gap-2 mb-2">
                                                                        {{-- Terapkan config di sini --}}
                                                                        <span
                                                                            class="badge bg-{{ $statusConfig['bg'] }} text-{{ $statusConfig['text'] }} rounded-pill border border-{{ $statusConfig['bg'] }}">
                                                                            {{ ucfirst(str_replace('_', ' ', $target->status)) }}
                                                                        </span>

                                                                        <span class="badge bg-light text-muted border">
                                                                            <i class="mdi mdi-calendar-clock me-1"></i>
                                                                            {{ \Carbon\Carbon::parse($target->deadline)->format('d M Y') }}
                                                                        </span>
                                                                    </div>

                                                                    {{-- Bintang --}}
                                                                    <div class="d-flex text-nowrap ms-2">
                                                                        @for ($i = 0; $i < $target->star_level; $i++)
                                                                            <i class="mdi mdi-star text-warning"></i>
                                                                        @endfor
                                                                    </div>
                                                                </div>

                                                                {{-- FORM UBAH STATUS (ADMIN/LEADER) --}}
                                                                @if ($canManage)
                                                                    <div
                                                                        class="mt-3 pt-3 border-top bg-light rounded-3 p-3">
                                                                        <form
                                                                            action="{{ route('job-targets.admin-status', $target->id) }}"
                                                                            method="POST"
                                                                            class="d-flex align-items-center gap-2">
                                                                            @csrf
                                                                            @method('PUT')
                                                                            <label
                                                                                class="small fw-bold text-muted mb-0 text-nowrap">Ubah
                                                                                Status:</label>

                                                                            {{-- FORM UBAH STATUS (ADMIN/LEADER) --}}
                                                                            @if ($canManage)
                                                                                <div
                                                                                    class="mt-3 pt-3 border-top bg-light rounded-3 p-3">
                                                                                    <form
                                                                                        action="{{ route('job-targets.admin-status', $target->id) }}"
                                                                                        method="POST"
                                                                                        class="d-flex align-items-center gap-2">
                                                                                        @csrf
                                                                                        @method('PUT')
                                                                                        <label
                                                                                            class="small fw-bold text-muted mb-0 text-nowrap">Ubah
                                                                                            Status:</label>

                                                                                        {{-- PERBAIKAN: Tambah style color black dan background white --}}
                                                                                        <select name="status"
                                                                                            class="form-select form-select-sm shadow-sm"
                                                                                            style="width: auto; cursor: pointer; background-color: #ffffff !important; color: #000000 !important;">
                                                                                            <option value="pending"
                                                                                                class="text-dark"
                                                                                                {{ $target->status == 'pending' ? 'selected' : '' }}>
                                                                                                Pending</option>
                                                                                            <option value="in_progress"
                                                                                                class="text-dark"
                                                                                                {{ $target->status == 'in_progress' ? 'selected' : '' }}>
                                                                                                In Progress</option>
                                                                                            <option value="completed"
                                                                                                class="text-dark"
                                                                                                {{ $target->status == 'completed' ? 'selected' : '' }}>
                                                                                                Completed</option>
                                                                                            <option value="rejected"
                                                                                                class="text-dark"
                                                                                                {{ $target->status == 'rejected' ? 'selected' : '' }}>
                                                                                                Rejected</option>
                                                                                        </select>

                                                                                        <button type="submit"
                                                                                            class="btn btn-dark btn-sm rounded-3 px-3">
                                                                                            <i class="mdi mdi-check"></i>
                                                                                            Simpan
                                                                                        </button>
                                                                                    </form>
                                                                                </div>
                                                                            @endif

                                                                            {{-- TAMPILKAN BUKTI JIKA COMPLETED --}}
                                                                            @if ($target->status == 'completed' && $target->outcome)
                                                                                <div
                                                                                    class="mt-2 p-2 bg-success bg-opacity-10 rounded border border-success border-opacity-25">
                                                                                    <small
                                                                                        class="fw-bold text-success d-block">Hasil
                                                                                        Pengerjaan:</small>
                                                                                    <small
                                                                                        class="text-dark">{{ $target->outcome }}</small>
                                                                                </div>
                                                                            @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="text-center py-5">
                                                                <div
                                                                    class="bg-light rounded-circle d-inline-flex p-3 mb-3">
                                                                    <i
                                                                        class="mdi mdi-clipboard-text-off text-muted mdi-24px"></i>
                                                                </div>
                                                                <p class="text-muted">Belum ada target personal untuk user
                                                                    ini.</p>
                                                            </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer bg-light border-top-0">
                                                    <button type="button" class="btn btn-light border"
                                                        data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- END MODAL --}}

                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">Belum ada anggota tim di cabang
                                        ini.</td>
                                </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL UPDATE STATUS (Popup Hasil Kerja untuk user sendiri / Section 1) --}}
    @include('job_targets.partials.modal_update')

    {{-- STYLE --}}
    <style>
        .card-rounded {
            border-radius: 16px;
            overflow: hidden;
        }

        .bg-gradient-info {
            background: linear-gradient(45deg, #198ae3, #4b49ac);
        }

        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        .nav-pills-custom .nav-link {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #e9ecef;
            margin-right: 5px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        .nav-pills-custom .nav-link.active {
            background: #4b49ac;
            color: #fff;
            border-color: #4b49ac;
            box-shadow: 0 4px 6px rgba(75, 73, 172, 0.2);
        }
    </style>

    {{-- SCRIPT FILTER --}}
    <script>
        function applyFilter(containerId, periodType) {
            let filterBox = document.getElementById('filter-container-' + containerId);
            let dataContainer = document.getElementById('data-container-' + containerId);
            if (!filterBox || !dataContainer) return;

            let startVal = '',
                endVal = '';
            if (periodType === 'daily') {
                startVal = filterBox.querySelector('.filter-date-start').value;
                endVal = filterBox.querySelector('.filter-date-end').value;
            } else if (periodType === 'monthly') {
                startVal = filterBox.querySelector('.filter-month-start').value;
                endVal = filterBox.querySelector('.filter-month-end').value;
            } else if (periodType === 'yearly') {
                startVal = filterBox.querySelector('.filter-year-start').value;
                endVal = filterBox.querySelector('.filter-year-end').value;
            }

            let items = dataContainer.querySelectorAll('.filterable-item');
            items.forEach(item => {
                let itemVal = '';
                if (periodType === 'daily') itemVal = item.getAttribute('data-date');
                else if (periodType === 'monthly') itemVal = item.getAttribute('data-month');
                else if (periodType === 'yearly') itemVal = item.getAttribute('data-year');

                let show = true;
                if (startVal && itemVal < startVal) show = false;
                if (endVal && itemVal > endVal) show = false;

                show ? item.classList.remove('d-none') : item.classList.add('d-none');
            });

            let tables = dataContainer.querySelectorAll('tbody');
            tables.forEach(tbody => {
                let visibleRows = tbody.querySelectorAll('.filterable-item:not(.d-none)');
                let msgRow = tbody.querySelector('.no-data-message');
                if (msgRow) visibleRows.length === 0 ? msgRow.classList.remove('d-none') : msgRow.classList.add(
                    'd-none');
            });
        }

        function resetFilter(containerId) {
            let filterBox = document.getElementById('filter-container-' + containerId);
            let dataContainer = document.getElementById('data-container-' + containerId);
            if (!filterBox || !dataContainer) return;

            filterBox.querySelectorAll('input').forEach(input => input.value = '');
            dataContainer.querySelectorAll('.filterable-item').forEach(item => item.classList.remove('d-none'));
            dataContainer.querySelectorAll('.no-data-message').forEach(msg => msg.classList.add('d-none'));
        }
    </script>
@endsection
