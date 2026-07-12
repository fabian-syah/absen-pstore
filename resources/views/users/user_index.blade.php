@extends('layout.master')

@section('title')
    Data User
@endsection

@section('heading')
    Manajemen User
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Daftar Semua User</h4>

                    {{-- CONTAINER: TOMBOL TAMBAH & SEARCH FORM --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Tambah User Baru
                        </a>

                        <form action="{{ route('users.index') }}" method="GET" class="d-flex">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Cari Nama / ID / Email..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                                @if (request('search'))
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary" title="Reset Pencarian">
                                        <i class="mdi mdi-refresh"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    @php
                        // Jika role admin_gaji, default ke tab inactive (EX Karyawan)
                        $defaultTab = auth()->user()->role == 'admin_gaji' ? 'inactive' : 'active';
                        $activeTab = request('tab') ?: $defaultTab;
                    @endphp
                    {{-- NAV TABS UNTUK MEMISAHKAN AKTIF & NON-AKTIF --}}
                    <ul class="nav nav-tabs tab-basic mb-3" role="tablist">
                        @if(auth()->user()->role == 'admin_gaji')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin-gaji.users.index') }}">
                                <i class="mdi mdi-arrow-left me-1"></i> Non Karyawan
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->role != 'admin_gaji')
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'active' ? 'active' : '' }}" id="active-tab" data-bs-toggle="tab" href="#active-users" role="tab" aria-controls="active-users" aria-selected="{{ $activeTab == 'active' ? 'true' : 'false' }}">
                                User Aktif <span class="badge bg-success ms-1 text-white">{{ $users->total() }}</span>
                            </a>
                        </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'inactive' ? 'active' : '' }}" id="inactive-tab" data-bs-toggle="tab" href="#inactive-users" role="tab" aria-controls="inactive-users" aria-selected="{{ $activeTab == 'inactive' ? 'true' : 'false' }}">
                                EX Karyawan <span class="badge bg-danger ms-1 text-white">{{ $inactiveUsers->total() }}</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content tab-content-basic">
                        {{-- TAB 1: USER AKTIF --}}
                        <div class="tab-pane fade {{ $activeTab == 'active' ? 'show active' : '' }}" id="active-users" role="tabpanel" aria-labelledby="active-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th> # </th>
                                            <th> Profil Pengguna </th>
                                            <th> Kontak </th>
                                            <th> Status Login </th> {{-- <--- KOLOM BARU --}}
                                            <th> Role </th>
                                            <th> Penempatan & Divisi </th>
                                            <th> Dibuat Oleh </th>
                                            <th> Tanggal Join </th>
                                            <th> QR Code </th>
                                            <th> Aksi </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $key => $user)
                                            <tr>
                                                <td> {{ $users->firstItem() + $key }} </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3 position-relative">
                                                            @if ($user->profile_photo_path)
                                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="profile" class="img-sm rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: {{ $user->is_verified ? '2px solid #0d6efd' : 'none' }};">
                                                            @else
                                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" alt="profile" class="img-sm rounded-circle">
                                                            @endif
                                                            @if ($user->is_verified)
                                                                <span class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center" style="bottom: -2px; right: -2px; width: 16px; height: 16px;">
                                                                    <i class="mdi mdi-check-decagram text-primary" style="font-size: 14px;"></i>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold d-flex align-items-center gap-1">{{ $user->name }}</div>
                                                            <small class="text-muted">ID: {{ $user->login_id ?? '-' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div><i class="mdi mdi-email-outline me-1"></i> {{ $user->email }}</div>
                                                    @if ($user->whatsapp)
                                                        <div class="text-success mt-1"><i class="mdi mdi-whatsapp me-1"></i> {{ $user->whatsapp }}</div>
                                                    @endif
                                                </td>
                                                {{-- LOGIKA LAST LOGIN BERDASARKAN TIMEZONE CABANG --}}
                                                <td>
                                                    @php
                                                        $branchTz = $user->branch->timezone ?? 'Asia/Jakarta';
                                                        $lastLogin = $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->setTimezone($branchTz) : null;
                                                        $isOnline = Cache::has('user-is-online-' . $user->id);
                                                    @endphp

                                                    @if($isOnline)
                                                        <span class="badge badge-success d-flex align-items-center" style="width: fit-content;">
                                                            <i class="mdi mdi-circle me-1" style="font-size: 10px;"></i> Online
                                                        </span>
                                                    @elseif($lastLogin)
                                                        <div class="lh-1">
                                                            <div class="fw-bold mb-1" style="font-size: 0.8rem;">{{ $lastLogin->translatedFormat('d M Y') }}</div>
                                                            <small class="text-muted" style="font-size: 0.7rem;">
                                                                <i class="mdi mdi-clock-outline me-1"></i>{{ $lastLogin->format('H:i') }} 
                                                                {{ $branchTz == 'Asia/Jakarta' ? 'WIB' : ($branchTz == 'Asia/Makassar' ? 'WITA' : 'WIT') }}
                                                            </small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted small">Belum Login</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-outline-secondary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                                                    @if($user->only_security_scan)
                                                        <div class="mt-1"><span class="badge bg-danger text-white" style="font-size: 10px;"><i class="mdi mdi-qrcode-scan"></i> Scan Only</span></div>
                                                    @endif
                                                    @if($user->use_face_recognition)
                                                        <div class="mt-1"><span class="badge bg-success text-white" style="font-size: 10px;"><i class="mdi mdi-face-recognition"></i> AI ON</span></div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="fw-bold mb-1" style="font-size: 0.9rem;">
                                                        @if ($user->role == 'audit' || $user->role == 'leader')
                                                            {{ $user->branches->pluck('name')->join(', ') ?: 'Semua Cabang' }}
                                                        @else
                                                            {{ $user->branch->name ?? 'Semua Cabang' }}
                                                        @endif
                                                    </div>
                                                    <div class="text-muted">
                                                        @if ($user->divisions->isNotEmpty())
                                                            <i class="mdi mdi-label-outline text-primary me-1" style="font-size: 10px;"></i>
                                                            <span style="font-size: 0.8rem;">{{ $user->divisions->pluck('name')->join(', ') }}</span>
                                                        @else
                                                            <span class="text-muted fst-italic" style="font-size: 0.8rem;">-</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($user->creator)
                                                        <div class="fw-bold small">{{ $user->creator->name }}</div>
                                                        <small class="text-muted" style="font-size: 0.7rem;">{{ $user->creator->role }}</small>
                                                    @else
                                                        <span class="text-muted small">System</span>
                                                    @endif
                                                </td>
                                                <td>{{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->format('d M Y') : ($user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '-') }}</td>
                                                <td>
                                                    @if ($user->qr_code_value)
                                                        <button type="button" class="btn btn-inverse-dark btn-icon btn-sm me-1" data-bs-toggle="modal" data-bs-target="#qrModal" data-name="{{ $user->name }}" data-qr="{{ $user->qr_code_value }}" title="Lihat QR">
                                                            <i class="mdi mdi-eye"></i>
                                                        </button>
                                                        <a href="{{ route('users.download-qr-pdf', $user->id) }}" class="btn btn-inverse-primary btn-icon btn-sm" title="Download PDF" target="_blank">
                                                            <i class="mdi mdi-file-pdf-box"></i>
                                                        </a>
                                                    @else
                                                        <span class="text-muted text-small">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-inverse-info btn-icon btn-sm" title="Lihat Detail"><i class="mdi mdi-eye"></i></a>
                                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-inverse-warning btn-icon btn-sm" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                                    @if ($user->id != auth()->id() && auth()->user()->role != 'audit' && auth()->user()->role != 'leader')
                                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-inverse-danger btn-icon btn-sm" title="Hapus"><i class="mdi mdi-delete"></i></button>
                                                        </form>
                                                    @endif
                                                    @if ($user->id != auth()->id() && in_array(auth()->user()->role, ['admin', 'audit']))
                                                        @php
                                                            $isSameTeamAudit = auth()->user()->role == 'audit' && $user->branch_id == auth()->user()->branch_id;
                                                        @endphp
                                                        @if (!$isSameTeamAudit || auth()->user()->role == 'admin')
                                                            <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-icon btn-sm btn-inverse-danger" title="Nonaktifkan">
                                                                    <i class="mdi mdi-power-off"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="9" class="text-center py-4"><div class="text-muted">Tidak ada data user aktif ditemukan.</div></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 d-flex justify-content-end">{{ $users->links('pagination::bootstrap-5') }}</div>
                        </div>

                        {{-- TAB 2: EX KARYAWAN (NON-AKTIF) --}}
                        <div class="tab-pane fade {{ $activeTab == 'inactive' ? 'show active' : '' }}" id="inactive-users" role="tabpanel" aria-labelledby="inactive-tab">
                            <div class="table-responsive">
                                <table class="table table-hover border-danger">
                                    <thead>
                                        <tr class="bg-light">
                                            <th> # </th>
                                            <th> Profil Pengguna </th>
                                            <th> Email </th>
                                            <th> Penempatan Terakhir </th>
                                            <th> Dibuat Oleh </th>
                                            <th> Tanggal Join </th>
                                            <th> Aksi </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($inactiveUsers as $key => $user)
                                            <tr class="opacity-75">
                                                <td> {{ $inactiveUsers->firstItem() + $key }} </td>
                                                <td>
                                                    <a href="{{ route('users.show', $user->id) }}" class="text-decoration-none" style="color: inherit;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3 position-relative">
                                                                @if ($user->profile_photo_path)
                                                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="profile" class="img-sm rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                                @else
                                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f8d7da&color=721c24" alt="profile" class="img-sm rounded-circle">
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-danger">{{ $user->name }}</div>
                                                                <small class="text-muted">ID: {{ $user->login_id ?? '-' }}</small>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <span class="badge bg-dark text-white">
                                                        <i class="mdi mdi-store me-1"></i> {{ $user->branch->name ?? 'EX Karyawan' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($user->creator)
                                                        <div class="fw-bold small">{{ $user->creator->name }}</div>
                                                        <small class="text-muted" style="font-size: 0.7rem;">{{ $user->creator->role }}</small>
                                                    @else
                                                        <span class="text-muted small">System</span>
                                                    @endif
                                                </td>
                                                <td>{{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->format('d M Y') : ($user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '-') }}</td>
                                                <td>
                                                    @if (in_array(auth()->user()->role, ['admin', 'audit']))
                                                        <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                        <button type="submit" class="btn btn-icon btn-sm btn-inverse-success" title="Aktifkan Kembali">
                                                            <i class="mdi mdi-power"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                    @if (auth()->user()->role == 'admin')
                                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen data ini?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-inverse-danger btn-icon btn-sm" title="Hapus Permanen"><i class="mdi mdi-delete-forever"></i></button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center py-4"><div class="text-muted">Tidak ada data EX Karyawan.</div></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 d-flex justify-content-end">{{ $inactiveUsers->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL QR Code --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">QR Code User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrcode-container" class="d-flex justify-content-center my-3"></div>
                    <p class="text-muted small mt-2">Scan QR ini untuk absensi</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        var qrModal = document.getElementById('qrModal');
        qrModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var name = button.getAttribute('data-name');
            var qrValue = button.getAttribute('data-qr');
            var modalTitle = qrModal.querySelector('.modal-title');
            modalTitle.textContent = 'QR Code: ' + name;
            var qrContainer = document.getElementById('qrcode-container');
            qrContainer.innerHTML = '';
            if (qrValue) {
                new QRCode(qrContainer, { text: qrValue, width: 200, height: 200, colorDark: "#000000", colorLight: "#ffffff", correctLevel: QRCode.CorrectLevel.H });
            } else {
                qrContainer.innerHTML = '<span class="text-danger">Value QR Code tidak ditemukan</span>';
            }
        });
    </script>
@endpush