@extends('layout.master')

@section('title')
    Profil Saya
@endsection

@section('heading')
    Profil Saya
@endsection

@section('content')

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
        {{-- KOLOM KIRI (Profil, KTP, QR) --}}
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="card-title">Foto Profil</h4>

                    {{-- Tampilkan Foto Profil --}}
                    @if ($user->profile_photo_path)
                        @php
                            $photoUrl = Storage::url($user->profile_photo_path);
                        @endphp
                        <img src="{{ $photoUrl }}" alt="foto profil" class="img-lg rounded-circle mb-3"
                            style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        {{-- Tampilkan Inisial jika tidak ada foto --}}
                        <div class="profile-initial-dropdown mb-3"
                            style="margin: 0 auto; background-color: #007bff; width: 150px; height: 150px; line-height: 150px; font-size: 40px; border-radius: 50%; color: white; font-weight: bold;">
                            {{ getInitials($user->name) }}
                        </div>
                    @endif

                    <p class="card-description">{{ $user->name }}</p>

                    {{-- Form Ganti Foto Profil --}}
                    <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data"
                        class="mb-2">
                        @csrf
                        @method('PUT')
                        <label for="profile_photo" class="btn btn-primary btn-sm">Upload Foto Baru</label>
                        <input type="file" name="profile_photo" id="profile_photo" class="d-none"
                            accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                    </form>

                    {{-- Tombol Hapus Foto --}}
                    @if ($user->profile_photo_path)
                        <form action="{{ route('profile.photo.delete') }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus foto profil?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus Foto</button>
                        </form>
                    @endif
                </div>

                <hr>

                <div class="card-body text-center">
                    <h4 class="card-title">QR Code Absensi</h4>
                    <div id="qrcode-display" class="d-flex justify-content-center mb-3"></div>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#qrModal">
                        Tampilkan Penuh
                    </button>
                </div>

                <hr>

                <div class="card-body text-center">
                    <h4 class="card-title">Data KTP</h4>
                    @if ($user->ktp_photo_path)
                        <p>KTP sudah ter-upload.</p>
                        <a href="{{ asset('storage/' . $user->ktp_photo_path) }}" target="_blank"
                            class="btn btn-secondary btn-sm">
                            Lihat KTP
                        </a>
                        <small class="d-block text-muted mt-2">Hubungi Admin jika ada kesalahan data.</small>
                    @else
                        <p class="text-danger">KTP Anda belum di-upload!</p>
                        <form action="{{ route('profile.ktp.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <label for="ktp_photo" class="btn btn-warning btn-sm">Upload KTP</label>
                            <input type="file" name="ktp_photo" id="ktp_photo" class="d-none"
                                accept="image/jpeg,image/png,image/jpg" onchange="this.form.submit()">
                            <small class="d-block text-muted mt-2">PENTING: KTP tidak bisa diubah atau dihapus setelah
                                di-upload.</small>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (Edit Info) --}}
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
                                    <input type="text" class="form-control" value="{{ $user->role }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cabang</label>
                                    <input type="text" class="form-control"
                                        value="{{ $user->branch->name ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Divisi / Tim</label>
                                    <input type="text" class="form-control"
                                        value="{{ $user->division->name ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Masuk</label>
                                    <input type="text" class="form-control"
                                        value="{{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->format('d M Y') : 'N/A' }}"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title mt-4">Info Kontak & Sosial Media (Opsional)</h4>
                        <div class="form-group">
                            <label for="whatsapp">WhatsApp</label>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                placeholder="62812..." value="{{ old('whatsapp', $user->whatsapp) }}">
                        </div>
                        <div class="form-group">
                            <label for="instagram">Instagram</label>
                            <input type="text" class="form-control" id="instagram" name="instagram"
                                placeholder="username" value="{{ old('instagram', $user->instagram) }}">
                        </div>
                        <div class="form-group">
                            <label for="tiktok">TikTok</label>
                            <input type="text" class="form-control" id="tiktok" name="tiktok"
                                placeholder="username" value="{{ old('tiktok', $user->tiktok) }}">
                        </div>

                        <h4 class="card-title mt-4">Ubah Password</h4>
                        <div class="form-group">
                            <label for="password">Password Baru</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti password.</small>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation">
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                        <a href="/" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION RIWAYAT PEKERJAAN --}}
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

    {{-- SECTION INVENTARIS --}}
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
                                                            <div class="col-md-6">
                                                                @if ($item->item_photo_path)
                                                                    <img src="{{ asset('storage/' . $item->item_photo_path) }}"
                                                                        alt="item" class="img-fluid mb-3">
                                                                @endif
                                                                <p><strong>Nama Barang:</strong> {{ $item->item_name }}</p>
                                                                <p><strong>Kategori:</strong>
                                                                    {{ ucfirst($item->category) }}</p>
                                                                <p><strong>Serial Number:</strong>
                                                                    {{ $item->serial_number ?? '-' }}</p>
                                                                <p><strong>Tanggal Terima:</strong>
                                                                    {{ \Carbon\Carbon::parse($item->received_date)->format('d M Y') }}
                                                                </p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Kondisi:</strong>
                                                                    {{ ucfirst(str_replace('_', ' ', $item->condition)) }}
                                                                </p>
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

    {{-- Modal Tambah Riwayat Pekerjaan --}}
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
                            <i class="mdi mdi-information"></i> <small>Catat perpindahan divisi atau promosi jabatan Anda
                                di perusahaan ini.</small>
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
                                    <small class="text-muted">Kosongkan jika masih di posisi ini</small>
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

    {{-- Modal Tambah Inventaris --}}
    <div class="modal fade" id="addInventoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Inventaris Pribadi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Pastikan action mengarah ke route yang benar --}}
                <form action="{{ route('profile.inventory.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- ================================================= --}}
                    {{-- PENTING: LINE INI YANG MEMBEDAKAN DENGAN ADMIN --}}
                    {{-- ================================================= --}}
                    <input type="hidden" name="is_profile_action" value="1">
                    {{-- ================================================= --}}

                    <div class="modal-body">
                        <div class="alert alert-info py-2">
                            <i class="mdi mdi-information-outline"></i> Barang ini akan otomatis tercatat atas nama Anda.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_name">Nama Barang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="item_name" name="item_name" required
                                        placeholder="Contoh: Laptop Pribadi / Inventaris Kantor">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-control" id="category" name="category" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Elektronik">Elektronik</option>
                                        <option value="Perkantoran">Perkantoran</option>
                                        <option value="Kendaraan">Kendaraan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="serial_number">Serial Number (SN)</label>
                                    <input type="text" class="form-control" id="serial_number" name="serial_number"
                                        placeholder="Opsional">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="received_date">Tanggal Terima <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="received_date" name="received_date"
                                        required value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="condition">Kondisi Saat Ini <span class="text-danger">*</span></label>
                            <select class="form-control" id="condition" name="condition" required>
                                <option value="Baru">Baru</option>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                                <option value="Perbaikan">Dalam Perbaikan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Deskripsi / Keterangan</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                placeholder="Keterangan tambahan barang..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="item_photo">Foto Barang (Max 5MB)</label>
                            <input type="file" class="form-control" id="item_photo" name="item_photo"
                                accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="document">Dokumen Pendukung (PDF, DOCX - Max 10MB)</label>
                            <input type="file" class="form-control" id="document" name="document"
                                accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal QR Code (Sama seperti sebelumnya) --}}
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

            // TEST

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
