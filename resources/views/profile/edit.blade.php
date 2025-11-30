@extends('layout.master')

@section('title')
    Profil Saya
@endsection

@section('heading')
    Profil Saya
@endsection

@section('content')

    {{-- ALERT MESSAGES --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- ================================================= --}}
        {{-- KOLOM KIRI (FOTO, QR, KTP) --}}
        {{-- ================================================= --}}
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    
                    {{-- 1. BAGIAN FOTO PROFIL --}}
                    <h4 class="card-title d-flex justify-content-center align-items-center gap-2">
                        Foto Profil
                        @if ($user->is_verified)
                            <i class="mdi mdi-check-decagram text-primary" title="Akun Terverifikasi" style="font-size: 1.2rem;"></i>
                        @endif
                    </h4>

                    <div class="position-relative d-inline-block mb-3">
                        @if ($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="foto profil"
                                class="img-lg rounded-circle"
                                style="width: 150px; height: 150px; object-fit: cover; border: {{ $user->is_verified ? '4px solid #0d6efd' : 'none' }}">
                        @else
                            <div class="profile-initial-dropdown mx-auto"
                                style="background-color: #007bff; width: 150px; height: 150px; line-height: 150px; font-size: 40px; border-radius: 50%; color: white; font-weight: bold;">
                                {{ getInitials($user->name) }}
                            </div>
                        @endif

                        {{-- Overlay Centang Biru di Foto --}}
                        @if ($user->is_verified)
                            <div class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="bottom: 5px; right: 5px; width: 35px; height: 35px; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                <i class="mdi mdi-check-decagram text-primary" style="font-size: 20px;"></i>
                            </div>
                        @endif
                    </div>

                    <p class="card-description fw-bold">
                        {{ $user->name }}
                        @if ($user->is_verified)
                            <br><span class="badge badge-primary badge-pill mt-1">
                                <i class="mdi mdi-check-decagram"></i> Akun Terverifikasi
                            </span>
                        @else
                            <br><span class="text-muted small">User Biasa</span>
                        @endif
                    </p>

                    {{-- LOGIKA TOMBOL GANTI FOTO --}}
                    
                    {{-- KONDISI A: User Verified & Request Disetujui (Buka Kunci) --}}
                    @if ($user->is_verified && $user->photo_request_status == 'approved')
                        <div class="alert alert-success py-2 mt-3 text-small">
                            <i class="mdi mdi-lock-open-check"></i> Akses dibuka sementara. Silakan upload.
                        </div>
                        <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="mb-2">
                            @csrf
                            @method('PUT')
                            <label for="profile_photo" class="btn btn-success btn-sm w-100">Upload Foto Baru</label>
                            <input type="file" name="profile_photo" id="profile_photo" class="d-none"
                                accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                        </form>

                    {{-- KONDISI B: User Verified & Request Pending --}}
                    @elseif ($user->is_verified && $user->photo_request_status == 'pending')
                        <div class="alert alert-warning py-2 mt-3 text-small">
                            <i class="mdi mdi-clock"></i> Menunggu persetujuan Admin.
                        </div>
                        <button class="btn btn-secondary btn-sm w-100" disabled>Request Terkirim</button>

                    {{-- KONDISI C: User Verified (Terkunci) --}}
                    @elseif ($user->is_verified)
                        <div class="alert alert-light border py-2 text-muted text-small mt-3">
                            <i class="mdi mdi-lock"></i> Foto terkunci (Verified).
                        </div>
                        <form action="{{ route('profile.photo.request') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning btn-sm w-100" 
                                onclick="return confirm('Ajukan izin ke Admin untuk mengganti foto profil?')">
                                <i class="mdi mdi-key-variant"></i> Ajukan Ganti Foto
                            </button>
                        </form>

                    {{-- KONDISI D: User Belum Verified (Bebas) --}}
                    @else
                        <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="mb-2">
                            @csrf
                            @method('PUT')
                            <label for="profile_photo" class="btn btn-primary btn-sm w-100 mb-2">Ganti Foto</label>
                            <input type="file" name="profile_photo" id="profile_photo" class="d-none"
                                accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                        </form>

                        @if ($user->profile_photo_path)
                            <form action="{{ route('profile.photo.delete') }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus foto profil?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">Hapus Foto</button>
                            </form>
                        @endif
                    @endif

                    <hr>

                    {{-- 2. BAGIAN QR CODE --}}
                    <div class="text-center">
                        <h4 class="card-title">QR Code Absensi</h4>
                        <div id="qrcode-display" class="d-flex justify-content-center mb-3"></div>
                        <button type="button" class="btn btn-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#qrModal">
                            <i class="mdi mdi-qrcode-scan"></i> Tampilkan Penuh
                        </button>
                    </div>

                    <hr>

                    {{-- 3. BAGIAN DATA KTP --}}
                    <div class="text-center">
                        <h4 class="card-title">Data KTP</h4>
                        @if ($user->ktp_photo_path)
                            <p class="text-success small"><i class="mdi mdi-check-circle"></i> KTP Ter-upload</p>
                            <a href="{{ asset('storage/' . $user->ktp_photo_path) }}" target="_blank"
                                class="btn btn-secondary btn-sm w-100">
                                <i class="mdi mdi-card-account-details"></i> Lihat KTP
                            </a>
                            <small class="d-block text-muted mt-2 text-small">Hubungi Admin jika ada kesalahan.</small>
                        @else
                            <div class="alert alert-danger py-2 text-small">KTP Belum di-upload!</div>
                            <form action="{{ route('profile.ktp.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <label for="ktp_photo" class="btn btn-warning btn-sm w-100">Upload KTP</label>
                                <input type="file" name="ktp_photo" id="ktp_photo" class="d-none"
                                    accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                                <small class="d-block text-muted mt-2 text-small">PENTING: KTP tidak bisa diubah setelah di-upload.</small>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================= --}}
        {{-- KOLOM KANAN (FORM EDIT INFO) --}}
        {{-- ================================================= --}}
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form class="forms-sample" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h4 class="card-title">Informasi Akun</h4>
                        <div class="form-group">
                            <label for="name">Nama Lengkap ( Sesuai KTP )</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $user->email) }}" required>
                        </div>

                        <h4 class="card-title mt-4">Informasi Karyawan (Read-Only)</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Role</label>
                                    <input type="text" class="form-control" value="{{ strtoupper(str_replace('_', ' ', $user->role)) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cabang</label>
                                    <input type="text" class="form-control"
                                        value="{{ $user->branch->name ?? 'Pusat / Semua' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Divisi / Tim</label>
                                    <input type="text" class="form-control"
                                        value="{{ $user->division->name ?? '-' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Masuk</label>
                                    <input type="text" class="form-control"
                                        value="{{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->format('d M Y') : '-' }}"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title mt-4">Info Kontak & Sosial Media</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="whatsapp">WhatsApp</label>
                                    <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                        placeholder="62812..." value="{{ old('whatsapp', $user->whatsapp) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="instagram">Instagram</label>
                                    <input type="text" class="form-control" id="instagram" name="instagram"
                                        placeholder="username" value="{{ old('instagram', $user->instagram) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tiktok">TikTok</label>
                                    <input type="text" class="form-control" id="tiktok" name="tiktok"
                                        placeholder="username" value="{{ old('tiktok', $user->tiktok) }}">
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="facebook">Facebook</label>
                                    <input type="text" class="form-control" id="facebook" name="facebook"
                                        placeholder="username" value="{{ old('facebook', $user->facebook) }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="linkedin">LinkedIn</label>
                                    <input type="text" class="form-control" id="linkedin" name="linkedin"
                                        placeholder="username" value="{{ old('linkedin', $user->linkedin) }}">
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title mt-4">Keamanan</h4>
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password Baru</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengganti.</small>
                                </div>
                             </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation">
                                </div>
                             </div>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- SECTION RIWAYAT PEKERJAAN --}}
    {{-- ================================================= --}}
    <div class="row mt-4">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Riwayat Perpindahan Divisi/Posisi</h4>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addWorkHistoryModal">
                            <i class="mdi mdi-plus"></i> Tambah Riwayat
                        </button>
                    </div>

                    @if ($workHistories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Jabatan</th>
                                        <th>Divisi/Departemen</th>
                                        <th>Periode</th>
                                        <th>Durasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workHistories as $history)
                                        <tr>
                                            <td><strong>{{ $history->position }}</strong></td>
                                            <td>{{ $history->department }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($history->start_date)->format('d M Y') }} -
                                                {{ $history->end_date ? \Carbon\Carbon::parse($history->end_date)->format('d M Y') : 'Sekarang' }}
                                            </td>
                                            <td>
                                                @php
                                                    $start = \Carbon\Carbon::parse($history->start_date);
                                                    $end = $history->end_date
                                                        ? \Carbon\Carbon::parse($history->end_date)
                                                        : \Carbon\Carbon::now();
                                                    $diff = $start->diff($end);
                                                    $years = $diff->y;
                                                    $months = $diff->m;
                                                @endphp
                                                @if ($years > 0)
                                                    {{ $years }} tahun
                                                @endif
                                                @if ($months > 0)
                                                    {{ $months }} bulan
                                                @endif
                                                @if ($years == 0 && $months == 0)
                                                    < 1 bulan @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('profile.work-history.destroy', $history->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus riwayat ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i> Belum ada riwayat perpindahan divisi/posisi yang dicatat.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- SECTION INVENTARIS --}}
    {{-- ================================================= --}}
    <div class="row mt-4">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Inventaris Pribadi</h4>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addInventoryModal">
                            <i class="mdi mdi-plus"></i> Tambah Inventaris
                        </button>
                    </div>

                    @if ($inventories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Foto</th>
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Serial Number</th>
                                        <th>Tanggal Terima</th>
                                        <th>Kondisi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($inventories as $item)
                                        <tr>
                                            <td>
                                                @if ($item->item_photo_path)
                                                    <img src="{{ asset('storage/' . $item->item_photo_path) }}"
                                                        alt="item"
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <span class="badge badge-secondary">No Photo</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->item_name }}</td>
                                            <td><span class="badge badge-info">{{ ucfirst($item->category) }}</span></td>
                                            <td>{{ $item->serial_number ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->received_date)->format('d M Y') }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = match ($item->condition) {
                                                        'baik' => 'success',
                                                        'rusak_ringan' => 'warning',
                                                        'rusak_berat' => 'danger',
                                                        'perbaikan' => 'info',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span
                                                    class="badge badge-{{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $item->condition)) }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewInventoryModal{{ $item->id }}"
                                                    title="Lihat Detail">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                                <form action="{{ route('profile.inventory.destroy', $item->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus inventaris ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        {{-- Modal Detail Inventaris --}}
                                        <div class="modal fade" id="viewInventoryModal{{ $item->id }}"
                                            tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Inventaris: {{ $item->item_name }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6 text-center">
                                                                @if ($item->item_photo_path)
                                                                    <img src="{{ asset('storage/' . $item->item_photo_path) }}"
                                                                        alt="item" class="img-fluid mb-3 rounded" style="max-height: 300px;">
                                                                @else
                                                                    <div class="alert alert-secondary">Tidak ada foto</div>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Nama Barang:</strong> {{ $item->item_name }}</p>
                                                                <p><strong>Kategori:</strong> {{ ucfirst($item->category) }}</p>
                                                                <p><strong>Serial Number:</strong> {{ $item->serial_number ?? '-' }}</p>
                                                                <p><strong>Tanggal Terima:</strong> {{ \Carbon\Carbon::parse($item->received_date)->format('d M Y') }}</p>
                                                                <p><strong>Kondisi:</strong> {{ ucfirst(str_replace('_', ' ', $item->condition)) }}</p>
                                                                <p><strong>Deskripsi:</strong></p>
                                                                <p>{{ $item->description ?? 'Tidak ada deskripsi' }}</p>
                                                                
                                                                @if ($item->document_path)
                                                                    <a href="{{ asset('storage/' . $item->document_path) }}"
                                                                        target="_blank"
                                                                        class="btn btn-secondary btn-sm mt-2">
                                                                        <i class="mdi mdi-file-document"></i> Lihat Dokumen
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Belum ada inventaris yang ditambahkan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- MODAL TAMBAHAN --}}
    {{-- ================================================= --}}

    {{-- 1. Modal Tambah Riwayat Pekerjaan --}}
    <div class="modal fade" id="addWorkHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Riwayat Perpindahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('profile.work-history.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info alert-sm">
                            <i class="mdi mdi-information"></i> <small>Catat perpindahan divisi atau promosi jabatan Anda.</small>
                        </div>
                        <div class="form-group">
                            <label for="position">Jabatan *</label>
                            <input type="text" class="form-control" id="position" name="position"
                                placeholder="Contoh: Staff Marketing" required>
                        </div>
                        <div class="form-group">
                            <label for="department">Divisi/Departemen *</label>
                            <input type="text" class="form-control" id="department" name="department"
                                placeholder="Contoh: Marketing & Sales" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai *</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">Tanggal Selesai</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                    <small class="text-muted">Kosongkan jika saat ini.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Modal Tambah Inventaris --}}
    <div class="modal fade" id="addInventoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Inventaris Pribadi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('profile.inventory.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- IMPORTANT: Flag is_profile_action --}}
                    <input type="hidden" name="is_profile_action" value="1">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Barang *</label>
                                    <input type="text" class="form-control" name="item_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kategori *</label>
                                    <select class="form-control" name="category" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="elektronik">Elektronik</option>
                                        <option value="perkantoran">Perkantoran</option>
                                        <option value="kendaraan">Kendaraan</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Serial Number</label>
                                    <input type="text" class="form-control" name="serial_number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Terima *</label>
                                    <input type="date" class="form-control" name="received_date" required value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Kondisi *</label>
                            <select class="form-control" name="condition" required>
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                                <option value="perbaikan">Dalam Perbaikan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Foto Barang</label>
                            <input type="file" class="form-control" name="item_photo" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Dokumen</label>
                            <input type="file" class="form-control" name="document" accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. Modal QR Code --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">QR Code Absensi: {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrcode-container-modal" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Import library QR Code --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        @if ($user->qr_code_value)
            const qrValue = "{{ $user->qr_code_value }}";

            // Gambar QR Code kecil di halaman
            new QRCode(document.getElementById("qrcode-display"), {
                text: qrValue,
                width: 128,
                height: 128,
            });

            // Gambar QR Code besar saat modal dibuka
            var qrModal = document.getElementById('qrModal');
            qrModal.addEventListener('show.bs.modal', function(event) {
                var qrContainer = document.getElementById('qrcode-container-modal');
                qrContainer.innerHTML = '';
                new QRCode(qrContainer, {
                    text: qrValue,
                    width: 400,
                    height: 400,
                });
            });
        @endif
    </script>
@endpush