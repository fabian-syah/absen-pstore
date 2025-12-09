@extends('layout.master')

@section('content')
<style>
    /* Styling khusus untuk cetak */
    @media print {
        body * {
            visibility: hidden;
        }
        #printableArea, #printableArea * {
            visibility: visible;
        }
        #printableArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="row justify-content-center">
    
    {{-- BAGIAN KIRI: DETAIL KASBON (INVOICE) --}}
    <div class="col-md-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-body" id="printableArea">
                <div class="text-center mb-4 border-bottom pb-3">
                    <h3 class="text-uppercase font-weight-bold text-primary">INVOICE KASBON</h3>
                    <h6 class="text-muted">ID: #KB-{{ str_pad($kasbon->id, 5, '0', STR_PAD_LEFT) }}</h6>
                    
                    {{-- STATUS BADGE BESAR --}}
                    <div class="mt-2">
                        @if($kasbon->status == 'paid')
                            <h2 class="text-success" style="border: 2px solid green; display:inline-block; padding: 5px 20px; border-radius: 8px; transform: rotate(-5deg);">LUNAS</h2>
                        @elseif($kasbon->status == 'approved')
                            <h4 class="badge badge-outline-primary p-2">BELUM LUNAS / DICICIL</h4>
                        @elseif($kasbon->status == 'rejected')
                            <h4 class="badge badge-outline-danger p-2">DITOLAK</h4>
                        @else
                            <h4 class="badge badge-outline-warning p-2">MENUNGGU APPROVAL</h4>
                        @endif
                    </div>
                </div>

                {{-- INFO PEMINJAM & TUJUAN --}}
                <div class="mb-4">
                    <h5 class="text-muted mb-2">Informasi Peminjam</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Nama</span> <strong class="text-end">{{ $kasbon->user->name }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Divisi / Cabang</span> <strong class="text-end">{{ $kasbon->user->division->name ?? '-' }} / {{ $kasbon->user->branch->name ?? '-' }}</strong>
                        </li>
                        <li class="list-group-item px-0">
                            <span>Judul Keperluan</span><br>
                            <strong>{{ $kasbon->title }}</strong>
                        </li>
                    </ul>
                </div>

                {{-- INFO KEUANGAN --}}
                <div class="mb-4 p-3 bg-light rounded">
                    <h5 class="text-muted mb-3">Rincian Keuangan</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Pinjaman</span>
                        <strong class="text-dark">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sudah Dibayar</span>
                        <strong class="text-success">Rp {{ number_format($kasbon->total_paid, 0, ',', '.') }}</strong>
                    </div>
                    <div class="border-top my-2"></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold">Sisa Kewajiban</span>
                        <h4 class="text-danger font-weight-bold mb-0">Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}</h4>
                    </div>
                </div>

                {{-- INFO JATUH TEMPO & METODE --}}
                <div class="mb-4">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <td class="bg-light" width="40%">Jatuh Tempo</td>
                            <td>
                                {{ \Carbon\Carbon::parse($kasbon->due_date)->format('d F Y') }}
                                @if($kasbon->is_overdue)
                                    <br><span class="badge badge-danger mt-1">LEWAT JATUH TEMPO</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="bg-light">Penerimaan</td>
                            <td>
                                @if($kasbon->payment_method == 'transfer')
                                    Transfer Bank<br>
                                    <small class="text-muted">{{ $kasbon->payment_details }}</small>
                                @else
                                    Tunai (Cash)
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="bg-light">Ket. Lengkap</td>
                            <td>{{ $kasbon->description_1 }}</td>
                        </tr>
                    </table>
                </div>

                {{-- BUKTI FOTO --}}
                <div class="mb-3">
                    <p class="mb-1 font-weight-bold">Dokumen Pengajuan:</p>
                    <div class="d-flex gap-2">
                        @if($kasbon->photo_1)
                            <a href="{{ asset('storage/'.$kasbon->photo_1) }}" target="_blank">
                                <img src="{{ asset('storage/'.$kasbon->photo_1) }}" class="img-thumbnail" style="height: 80px; width: 80px; object-fit: cover;">
                            </a>
                        @endif
                        @if($kasbon->photo_2)
                            <a href="{{ asset('storage/'.$kasbon->photo_2) }}" target="_blank">
                                <img src="{{ asset('storage/'.$kasbon->photo_2) }}" class="img-thumbnail" style="height: 80px; width: 80px; object-fit: cover;">
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- FOOTER TOMBOL PRINT --}}
            <div class="card-footer d-flex justify-content-between no-print">
                <a href="{{ route('kasbon.index') }}" class="btn btn-light">Kembali</a>
                <button onclick="window.print()" class="btn btn-info text-white"><i class="mdi mdi-printer"></i> Cetak Invoice</button>
            </div>
        </div>
    </div>

    {{-- BAGIAN KANAN: RIWAYAT CICILAN & APPROVAL --}}
    <div class="col-md-7 grid-margin stretch-card no-print">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Riwayat Pembayaran / Cicilan</h4>
                    @if($kasbon->remaining_amount > 0 && auth()->id() == $kasbon->user_id)
                         <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#payModalGlobal">
                            <i class="mdi mdi-cash"></i> Bayar Sekarang
                        </button>
                    @endif
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Bukti</th>
                                <th>Status</th>
                                @if(auth()->user()->role == 'admin') 
                                    <th class="text-center">Aksi Admin</th> 
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kasbon->installments as $ins)
                                <tr>
                                    <td>{{ $ins->created_at->format('d/m/y H:i') }}</td>
                                    <td class="font-weight-bold text-primary">Rp {{ number_format($ins->amount_paid, 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ asset('storage/'.$ins->payment_proof) }}" target="_blank" class="btn btn-inverse-dark btn-rounded btn-icon btn-sm d-flex align-items-center justify-content-center">
                                            <i class="mdi mdi-image"></i>
                                        </a>
                                    </td>
                                    <td>
                                        @if($ins->status == 'pending') 
                                            <span class="badge badge-warning">Menunggu</span>
                                        @elseif($ins->status == 'approved') 
                                            <span class="badge badge-success">Diterima</span>
                                        @else 
                                            <span class="badge badge-danger">Ditolak</span> 
                                        @endif
                                        
                                        @if($ins->note)
                                            <br><small class="text-danger" style="font-size: 0.7rem;">Note: {{ $ins->note }}</small>
                                        @endif
                                    </td>
                                    
                                    {{-- KOLOM AKSI KHUSUS ADMIN --}}
                                    @if(auth()->user()->role == 'admin')
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                {{-- TOMBOL APPROVE/REJECT --}}
                                                @if($ins->status == 'pending')
                                                    <form action="{{ route('kasbon.installment.approve', $ins->id) }}" method="POST" class="d-inline">
                                                        @csrf 
                                                        <button class="btn btn-success btn-sm p-2" title="Terima Pembayaran" onclick="return confirm('Verifikasi pembayaran ini valid?')">
                                                            <i class="mdi mdi-check"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" class="btn btn-danger btn-sm p-2" title="Tolak Pembayaran" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $ins->id }}">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                @endif

                                                {{-- TOMBOL EDIT & HAPUS (SELALU MUNCUL UNTUK ADMIN) --}}
                                                <a href="{{ route('kasbon.installment.edit', $ins->id) }}" class="btn btn-warning btn-sm p-2" title="Edit Data">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>

                                                <form action="{{ route('kasbon.installment.destroy', $ins->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-dark btn-sm p-2" title="Hapus Permanen" onclick="return confirm('Yakin hapus data cicilan ini? Total hutang akan dihitung ulang.')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            {{-- MODAL REJECT --}}
                                            <div class="modal fade text-start" id="rejectModal{{ $ins->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('kasbon.installment.reject', $ins->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Tolak Pembayaran</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Anda yakin ingin menolak pembayaran sebesar <strong>Rp {{ number_format($ins->amount_paid) }}</strong>?</p>
                                                                <div class="form-group">
                                                                    <label>Alasan Penolakan (Wajib)</label>
                                                                    <textarea name="note" class="form-control" required placeholder="Contoh: Bukti transfer buram / Nominal tidak sesuai"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-danger">Tolak Pembayaran</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted p-4">Belum ada riwayat pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL BAYAR GLOBAL (Jika user ingin bayar dari halaman detail) --}}
@if($kasbon->remaining_amount > 0 && auth()->id() == $kasbon->user_id)
<div class="modal fade" id="payModalGlobal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bayar Cicilan Kasbon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('kasbon.installment.store', $kasbon->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        Sisa Kewajiban: <strong>Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}</strong>
                    </div>
                    
                    <div class="form-group">
                        <label>Nominal Pembayaran (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="amount_paid" id="rupiah_cicil" class="form-control" required>
                        </div>
                        <small class="text-muted">Masukkan nominal yang Anda bayar hari ini.</small>
                    </div>

                    <div class="form-group">
                        <label>Bukti Pembayaran (Transfer/Struk)</label>
                        <input type="file" name="payment_proof" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Format Rupiah di Modal Detail
    const rupiahCicil = document.getElementById('rupiah_cicil');
    if(rupiahCicil){
        rupiahCicil.addEventListener('keyup', function(e){
            this.value = formatRupiahGlobal(this.value);
        });
    }

    function formatRupiahGlobal(angka){
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split   = number_string.split(','),
        sisa    = split[0].length % 3,
        rupiah  = split[0].substr(0, sisa),
        ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }
</script>
@endif

@endsection