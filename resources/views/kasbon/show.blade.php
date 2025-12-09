@extends('layout.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body" id="printableArea">
                <div class="text-center mb-4">
                    <h3>BUKTI KASBON / INVOICE</h3>
                    <h5 class="text-muted">ID Transaksi: #KB-{{ str_pad($kasbon->id, 5, '0', STR_PAD_LEFT) }}</h5>
                    
                    @if($kasbon->status == 'paid')
                        <h2 class="text-success mt-2" style="border: 2px solid green; display:inline-block; padding: 5px 20px; border-radius: 8px;">LUNAS</h2>
                    @elseif($kasbon->status == 'approved')
                         <h4 class="text-primary mt-2">BELUM LUNAS</h4>
                    @elseif($kasbon->status == 'rejected')
                         <h4 class="text-danger mt-2">DITOLAK</h4>
                    @else
                         <h4 class="text-warning mt-2">MENUNGGU KONFIRMASI</h4>
                    @endif
                </div>

                <hr>

                <div class="row">
                    <div class="col-sm-6">
                        <p><strong>Peminjam:</strong> {{ $kasbon->user->name }}</p>
                        <p><strong>Divisi/Cabang:</strong> {{ $kasbon->user->division->name ?? '-' }} / {{ $kasbon->user->branch->name ?? '-' }}</p>
                        <p><strong>Tanggal Pengajuan:</strong> {{ $kasbon->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="col-sm-6 text-end text-md-end text-sm-start">
                        <p><strong>Total Nominal:</strong></p>
                        <h3 class="text-primary">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</h3>
                    </div>
                </div>

                <div class="mt-4">
                    <h5>Rincian & Keterangan</h5>
                    <table class="table table-bordered">
                        <tr>
                            <td width="30%">Keterangan 1</td>
                            <td>{{ $kasbon->description_1 }}</td>
                        </tr>
                        <tr>
                            <td>Keterangan 2</td>
                            <td>{{ $kasbon->description_2 ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="mt-4">
                    <h5>Dokumen Pendukung</h5>
                    <div class="d-flex gap-2">
                        @if($kasbon->photo_1)
                            <a href="{{ asset('storage/'.$kasbon->photo_1) }}" target="_blank">
                                <img src="{{ asset('storage/'.$kasbon->photo_1) }}" class="img-thumbnail" style="height: 100px;">
                            </a>
                        @endif
                        @if($kasbon->photo_2)
                            <a href="{{ asset('storage/'.$kasbon->photo_2) }}" target="_blank">
                                <img src="{{ asset('storage/'.$kasbon->photo_2) }}" class="img-thumbnail" style="height: 100px;">
                            </a>
                        @endif
                    </div>
                </div>

                @if($kasbon->status == 'paid' && $kasbon->repayment_proof)
                    <div class="mt-4 p-3 bg-light border rounded">
                        <h5>Bukti Pelunasan</h5>
                        <p>Tanggal Bayar: {{ \Carbon\Carbon::parse($kasbon->repayment_date)->format('d M Y') }}</p>
                        <a href="{{ asset('storage/'.$kasbon->repayment_proof) }}" target="_blank">
                            <img src="{{ asset('storage/'.$kasbon->repayment_proof) }}" class="img-fluid rounded" style="max-height: 200px;">
                        </a>
                    </div>
                @endif
            </div>
            
            <div class="card-footer text-end">
                <a href="{{ route('kasbon.index') }}" class="btn btn-secondary">Kembali</a>
                <button onclick="window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Cetak</button>
            </div>
        </div>
    </div>
</div>
@endsection