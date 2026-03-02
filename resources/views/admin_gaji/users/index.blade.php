@extends('layout.master')

@section('title', 'Daftar User')
@section('heading', 'Daftar User')

@section('content')
    <div class="p-3 p-md-4">
        <h4 class="mb-4 fw-bold text-dark" style="letter-spacing: -0.5px;">Daftar User</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show fw-bold text-dark" role="alert">
                <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Daftar Admin Gaji User</h5>
            <button type="button" class="btn btn-primary shadow-sm fw-bold px-3 py-2 rounded-pill" data-bs-toggle="modal"
                data-bs-target="#addUserModal">
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
                                <th class="py-3 px-4 font-14 fw-bold text-secondary text-uppercase text-center" width="15%">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $index => $user)
                                <tr>
                                    <td class="py-3 px-4 font-14 fw-semibold text-dark">{{ $index + 1 }}</td>
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center gap-3">
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
                                            <button type="button" class="btn btn-sm btn-light text-primary rounded-3 px-2 py-1"
                                                data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                                <i class="mdi mdi-pencil-outline fs-6"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light text-danger rounded-3 px-2 py-1"
                                                onclick="if(confirm('Yakin ingin menghapus data ini?')) document.getElementById('delete-form-{{ $user->id }}').submit();">
                                                <i class="mdi mdi-trash-can-outline fs-6"></i>
                                            </button>
                                            <form id="delete-form-{{ $user->id }}"
                                                action="{{ route('admin-gaji.users.destroy', $user->id) }}" method="POST"
                                                class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>


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
    <!-- Modal Edit User -->
    @foreach ($users as $user)
        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg custom-modal-bg">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">Edit Data User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin-gaji.users.update', $user->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-body p-4 pt-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark font-14">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control rounded-3 py-2 px-3 shadow-sm border-0"
                                    value="{{ $user->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark font-14">Lokasi</label>
                                <input type="text" name="location"
                                    class="form-control rounded-3 py-2 px-3 shadow-sm border-0"
                                    value="{{ $user->location }}">
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Simpan
                                Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

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