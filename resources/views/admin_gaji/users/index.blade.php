@extends('layout.master')

@section('content')
    <div class="row min-vh-100 flex-column flex-md-row">
        <!-- Include Sidebar -->
        @include('layout.sidebar')

        <!-- Main Content -->
        <div class="col px-0 ms-md-auto d-flex flex-column vh-100" style="background-color: #f6f0ff;">
            {{-- Navbar Mobile --}}
            <div class="d-md-none bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between position-sticky top-0 z-3"
                style="box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-white border-0 p-2 shadow-sm rounded-3" type="button" data-bs-toggle="collapse"
                        data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <i class="mdi mdi-menu text-dark fs-5"></i>
                    </button>
                    <h5 class="mb-0 fw-bold text-dark" style="letter-spacing: 0.5px;">Data User</h5>
                </div>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                        id="dropdownUserMobile" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm"
                            style="width: 38px; height: 38px; font-size: 14px;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2"
                        aria-labelledby="dropdownUserMobile">
                        <li>
                            <h6 class="dropdown-header fw-bold text-dark">{{ Auth::user()->name }}</h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="dropdown-item text-danger fw-medium d-flex align-items-center gap-2">
                                    <i class="mdi mdi-logout fs-5"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Desktop Topbar --}}
            <div class="d-none d-md-flex bg-white border-bottom py-3 px-4 align-items-center justify-content-between position-sticky top-0 z-3"
                style="box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <div>
                    <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.5px;">Data User (Khusus Admin Gaji)</h4>
                    <p class="text-muted small mb-0 mt-1"><i class="mdi mdi-account-group me-1"></i>Kelola data staff khusus
                        operasional gaji</p>
                </div>
                <div class="d-flex align-items-center gap-4">
                    <a href="#" class="btn btn-light position-relative p-2 rounded-circle shadow-sm">
                        <i class="mdi mdi-bell-outline text-dark fs-5"></i>
                    </a>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle gap-3"
                            id="dropdownUserDesktop" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="text-end d-none d-md-block">
                                <h6 class="mb-0 fw-bold text-dark">{{ Auth::user()->name }}</h6>
                                <small
                                    class="text-muted fw-medium">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</small>
                            </div>
                            <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                style="width: 42px; height: 42px; font-size: 16px; border: 2px solid white; outline: 2px solid #e0e0e0;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-3"
                            aria-labelledby="dropdownUserDesktop" style="min-width: 250px;">
                            <li class="px-4 py-3 bg-light rounded-top-4 border-bottom">
                                <h6 class="mb-0 fw-bold text-dark">{{ Auth::user()->name }}</h6>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                            </li>
                            <li class="py-2">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="dropdown-item text-danger fw-bold d-flex align-items-center gap-3 px-4 py-2 hover-bg-danger-light transition-all">
                                        <div
                                            class="bg-danger bg-opacity-10 p-2 rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="mdi mdi-logout fs-5"></i>
                                        </div>
                                        Keluar Sistem
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Layout Body -->
            <main class="flex-grow-1 p-3 p-md-4 overflow-auto">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show fw-bold text-dark" role="alert">
                        <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Daftar Admin Gaji User</h5>
                    <button type="button" class="btn btn-primary shadow-sm fw-bold px-3 py-2 rounded-pill"
                        data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="mdi mdi-plus me-1"></i> Tambah User Baru
                    </button>
                </div>

                <!-- Table Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 px-4 font-14 fw-bold text-secondary text-uppercase" width="5%">No
                                        </th>
                                        <th class="py-3 px-4 font-14 fw-bold text-secondary text-uppercase">Nama Lengkap
                                        </th>
                                        <th class="py-3 px-4 font-14 fw-bold text-secondary text-uppercase">Lokasi</th>
                                        <th class="py-3 px-4 font-14 fw-bold text-secondary text-uppercase">Tgl Dibuat</th>
                                        <th class="py-3 px-4 font-14 fw-bold text-secondary text-uppercase text-center"
                                            width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $index => $user)
                                        <tr>
                                            <td class="py-3 px-4 font-14 fw-semibold text-dark">{{ $index + 1 }}</td>
                                            <td class="py-3 px-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                                        style="width: 40px; height: 40px;">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">{{ $user->name }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 font-14 text-dark">{{ $user->location ?? '-' }}</td>
                                            <td class="py-3 px-4 font-14 text-secondary">
                                                {{ $user->created_at->format('d M Y') }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button"
                                                        class="btn btn-sm btn-light text-primary rounded-3 px-2 py-1"
                                                        data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                                        <i class="mdi mdi-pencil-outline fs-6"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-sm btn-light text-danger rounded-3 px-2 py-1"
                                                        onclick="if(confirm('Yakin ingin menghapus data ini?')) document.getElementById('delete-form-{{ $user->id }}').submit();">
                                                        <i class="mdi mdi-trash-can-outline fs-6"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $user->id }}"
                                                        action="{{ route('admin-gaji.users.destroy', $user->id) }}"
                                                        method="POST" class="d-none">
                                                        @csrf @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit User -->
                                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 rounded-4 shadow-lg custom-modal-bg">
                                                    <div class="modal-header border-bottom-0 pb-0">
                                                        <h5 class="modal-title fw-bold">Edit Data User</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin-gaji.users.update', $user->id) }}"
                                                        method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body p-4 pt-3">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold text-dark font-14">Nama
                                                                    Lengkap</label>
                                                                <input type="text" name="name"
                                                                    class="form-control rounded-3 py-2 px-3 shadow-sm border-0"
                                                                    value="{{ $user->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label fw-bold text-dark font-14">Lokasi</label>
                                                                <input type="text" name="location"
                                                                    class="form-control rounded-3 py-2 px-3 shadow-sm border-0"
                                                                    value="{{ $user->location }}">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                                                            <button type="button"
                                                                class="btn btn-light rounded-pill px-4 fw-bold"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit"
                                                                class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Simpan
                                                                Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-5 text-center text-muted">
                                                <i class="mdi mdi-inbox-off-outline" style="font-size: 48px;"></i>
                                                <p class="mt-2 mb-0 fw-medium">Belum ada data user tersimpan.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <footer class="mt-auto py-3 text-center border-top">
                    <span class="text-secondary small fw-medium">Dibuat dengan ❤️ oleh <b>Bian</b></span>
                    <br>
                    <span class="text-secondary" style="font-size: 11px;">© 2026 Hak Cipta Dilindungi Undang-Undang</span>
                </footer>
            </main>
        </div>
    </div>

    <!-- Modal Tambah User -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg custom-modal-bg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Data User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin-gaji.users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 pt-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark font-14">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control rounded-3 py-2 px-3 shadow-sm border-0"
                                placeholder="Masukkan nama..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark font-14">Lokasi</label>
                            <input type="text" name="location" class="form-control rounded-3 py-2 px-3 shadow-sm border-0"
                                placeholder="Masukkan lokasi penempatan...">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Simpan
                            Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .custom-modal-bg {
            background-color: #fcfbfe;
        }

        .hover-bg-danger-light:hover {
            background-color: #ffeef0 !important;
        }

        .font-14 {
            font-size: 14px;
        }

        input.form-control:focus {
            outline: none;
            box-shadow: 0 0 0 0.25rem rgba(103, 58, 183, 0.25);
            border-color: #673ab7;
        }
    </style>
@endsection