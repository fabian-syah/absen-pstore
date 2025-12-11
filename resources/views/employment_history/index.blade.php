@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Tambah Riwayat</h4>
                <form action="{{ route('employment-history.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <label>Kategori Kejadian</label>
                        <select name="type" id="typeSelect" class="form-control" required onchange="handleTypeChange()">
                            <option value="" disabled selected>Pilih Kategori</option>
                            <option value="join">Awal Masuk Pstore</option>
                            <option value="transfer_branch">Pindah Cabang</option>
                            <option value="transfer_division">Pindah Divisi / Jabatan</option>
                            <option value="resign">Resign / Dirumahkan</option>
                            <option value="rejoin">Masuk Pstore Lagi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Kejadian</label>
                        <input type="date" name="event_date" class="form-control" required>
                        <small class="text-muted">Pastikan tanggal sesuai kejadian asli agar urutan benar.</small>
                    </div>

                    {{-- Container untuk Cabang --}}
                    <div class="form-group" id="branchContainer">
                        <label>Cabang Tujuan</label>
                        <select name="branch_id" id="branchInput" class="form-control">
                            <option value="">Pilih Cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Container untuk Divisi --}}
                    <div class="form-group" id="divisionContainer">
                        <label>Divisi / Jabatan Tujuan</label>
                        <select name="division_id" class="form-control">
                            <option value="">Pilih Divisi</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Contoh: Promosi jabatan, Mutasi, dll"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Lampiran / Foto (Opsional)</label>
                        <input type="file" name="attachment" class="form-control-file">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Simpan Riwayat</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Timeline Riwayat Divisi / Cabang</h4>
                
                @if($histories->isEmpty())
                    <p class="text-center text-muted mt-4">Belum ada riwayat karir.</p>
                @else
                    <ul class="bullet-line-list">
                        @foreach($histories as $history)
                            <li>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="text-{{ $history->type_color }} font-weight-bold">{{ $history->type_label }}</span>
                                        <p class="text-muted mb-2 small">{{ \Carbon\Carbon::parse($history->event_date)->translatedFormat('d F Y') }}</p>
                                    </div>
                                    @if($history->attachment)
                                        <div>
                                            <a href="{{ asset('storage/'.$history->attachment) }}" target="_blank" class="badge badge-outline-primary">
                                                <i class="mdi mdi-attachment"></i> Lihat Foto
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="p-3 bg-light rounded mt-2">
                                    @if($history->type != 'resign')
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <small class="text-muted d-block">Cabang:</small>
                                                <strong>{{ $history->branch->name ?? '-' }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Divisi:</small>
                                                <strong>{{ $history->division->name ?? '-' }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($history->description)
                                        <p class="mb-0 text-small">"{{ $history->description }}"</p>
                                    @endif
                                </div>

                                <div class="mt-2 text-right">
                                    <form action="{{ route('employment-history.destroy', $history->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-inverse-danger btn-sm p-1"><i class="mdi mdi-trash-can"></i></button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function handleTypeChange() {
        const type = document.getElementById('typeSelect').value;
        const branchContainer = document.getElementById('branchContainer');
        const divisionContainer = document.getElementById('divisionContainer');
        const branchInput = document.getElementById('branchInput');
        
        // Reset state
        branchContainer.style.display = 'block';
        divisionContainer.style.display = 'block';
        branchInput.disabled = false;

        // Logika sesuai permintaan
        if (type === 'transfer_division') {
            // Jika Pindah Divisi: Cabang Disabled (Tetap di cabang sekarang)
            // Secara visual kita disable, di backend kita ambil auth()->user()->branch_id
            branchInput.value = "{{ auth()->user()->branch_id }}"; 
            branchInput.disabled = true;
        } 
        else if (type === 'resign') {
            // Jika Resign: Sembunyikan input cabang & divisi
            branchContainer.style.display = 'none';
            divisionContainer.style.display = 'none';
        }
        // Untuk 'join', 'rejoin', 'transfer_branch', semua input aktif
    }
</script>
@endsection