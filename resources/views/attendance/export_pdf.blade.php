<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0; color: #555; }
        
        .summary-box { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .summary-box td { border: 1px solid #ddd; padding: 10px; text-align: center; width: 25%; }
        .summary-title { font-weight: bold; display: block; margin-bottom: 5px; color: #555; }
        .summary-value { font-size: 16px; font-weight: bold; }

        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 10px; color: white; display: inline-block; }
        .bg-success { background-color: #28a745; color: white; }
        .bg-warning { background-color: #ffc107; color: black; }
        .bg-danger { background-color: #dc3545; color: white; }
        .bg-info { background-color: #17a2b8; color: white; }
        .bg-secondary { background-color: #6c757d; color: white; }
        
        .text-danger { color: #dc3545; }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN ABSENSI KARYAWAN</h2>
        <p><strong>Nama:</strong> {{ $user->name }} | <strong>Periode:</strong> {{ $monthName }}</p>
        <p>{{ $user->division->name ?? 'Divisi -' }} - {{ $user->branch->name ?? 'Cabang -' }}</p>
    </div>

    {{-- Summary Boxes --}}
    <table class="summary-box">
        <tr>
            <td>
                <span class="summary-title">Total Hari</span>
                <span class="summary-value">{{ $summary['total'] }}</span>
            </td>
            <td>
                <span class="summary-title">Hadir / WFH</span>
                <span class="summary-value" style="color: green;">{{ $summary['hadir'] }}</span>
            </td>
            <td>
                <span class="summary-title">Sakit / Izin</span>
                <span class="summary-value" style="color: orange;">{{ $summary['sakit'] + $summary['izin'] }}</span>
            </td>
            <td>
                <span class="summary-title">Alpha / Bolos</span>
                <span class="summary-value" style="color: red;">{{ $summary['alpha'] }}</span>
            </td>
        </tr>
    </table>

    {{-- Detail Table --}}
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history as $att)
                <tr>
                    <td>
                        {{ $att->check_in_time->format('d/m/Y') }}<br>
                        <small style="color: #777;">{{ $att->check_in_time->format('l') }}</small>
                    </td>
                    <td>
                        {{ $att->check_in_time->format('H:i') }}
                        @if($att->is_late_checkin)
                            <span style="color: red; font-size: 10px;">(Telat)</span>
                        @endif
                    </td>
                    <td>
                        @if($att->check_out_time)
                            {{ $att->check_out_time->format('H:i') }}
                            @if($att->is_early_checkout)
                                <span style="color: orange; font-size: 10px;">(Cepat)</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                             $status = $att->presence_status;
                             $bg = 'bg-secondary';
                             if($status == 'Masuk') $bg = 'bg-success';
                             if(str_contains($status, 'WFH')) $bg = 'bg-info';
                             if($status == 'Sakit') $bg = 'bg-info';
                             if($status == 'Alpha') $bg = 'bg-danger';
                             if($status == 'Telat') $bg = 'bg-warning';
                        @endphp
                        <span class="badge {{ $bg }}">{{ $status }}</span>
                    </td>
                    <td>
                        {{-- Tampilkan Notes / Audit Notes --}}
                        @if($att->audit_note)
                            <small>Audit: {{ $att->audit_note }}</small><br>
                        @endif
                        <small>{{ \Illuminate\Support\Str::limit($att->notes, 30) }}</small>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        <p>Dicetak pada: {{ date('d F Y H:i') }}</p>
    </div>

</body>
</html>