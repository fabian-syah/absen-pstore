<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data {{ $catLabel }}</title>
    <style>
        @page {
            margin: 1.2cm 1cm 1.2cm 1cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .logo-wrap {
            margin-bottom: 6px;
        }
        .logo {
            height: 38px;
            width: auto;
        }
        .company-name {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .title {
            font-size: 13px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .subtitle {
            font-size: 10px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9.5px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            vertical-align: middle;
        }
        th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .badge-info {
            background-color: #e0f2fe;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }
        .footer {
            margin-top: 25px;
            width: 100%;
        }
        .meta-info {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 8px;
        }
        .signature-table {
            width: 100%;
            border: none;
            margin-top: 20px;
        }
        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            text-align: center;
        }
        .signature-space {
            height: 50px;
        }
        .signature-line {
            width: 180px;
            border-top: 1px solid #334155;
            margin: 0 auto;
            padding-top: 4px;
            font-weight: bold;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-wrap">
            @if(file_exists(public_path('assets/images/logo-pstore-horizontal.png')))
                <img src="{{ public_path('assets/images/logo-pstore-horizontal.png') }}" class="logo" alt="PStore">
            @elseif(file_exists(public_path('assets/images/logo-pstore.png')))
                <img src="{{ public_path('assets/images/logo-pstore.png') }}" class="logo" alt="PStore">
            @else
                <div class="company-name">PSTORE INDONESIA</div>
            @endif
        </div>
        <div class="title">LAPORAN DATA {{ strtoupper($catLabel) }}</div>
        <div class="subtitle">Periode: {{ $periodLabel }}</div>
        <div class="meta-info">
            Dicetak oleh: <strong>{{ $generatedBy->name ?? 'Admin' }}</strong> | Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB | Total Data: <strong>{{ count($data) }} Orang</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="18%">Nama Lengkap</th>
                <th width="11%">Login ID</th>
                <th width="14%">Kategori</th>
                <th width="16%">Cabang Asal / Lokasi</th>
                <th width="13%">Divisi / Role</th>
                <th width="10%">Tanggal</th>
                <th width="14%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left" style="font-weight: bold;">{{ $item['name'] }}</td>
                    <td class="text-center" style="font-family: monospace;">{{ $item['login_id'] }}</td>
                    <td class="text-center">
                        @if(str_contains(strtolower($item['category']), 'resign'))
                            <span class="badge badge-danger">Resign</span>
                        @else
                            <span class="badge badge-info">Non Karyawan</span>
                        @endif
                    </td>
                    <td class="text-left">{{ $item['branch'] }}</td>
                    <td class="text-left">{{ $item['division'] }} ({{ $item['role'] }})</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}</td>
                    <td class="text-left" style="font-size: 8.5px; color: #475569;">{{ $item['notes'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada data karyawan resign / non karyawan pada periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td width="50%">
                <p style="margin: 0 0 5px 0; color: #64748b; font-size: 9px;">Dibuat / Dicetak Oleh,</p>
                <div class="signature-space"></div>
                <div class="signature-line">
                    {{ $generatedBy->name ?? 'Administrator' }}<br>
                    <span style="font-weight: normal; color: #64748b;">{{ strtoupper($generatedBy->role ?? 'Admin') }}</span>
                </div>
            </td>
            <td width="50%">
                <p style="margin: 0 0 5px 0; color: #64748b; font-size: 9px;">Mengetahui / Disetujui,</p>
                <div class="signature-space"></div>
                <div class="signature-line">
                    Management / HRD PStore<br>
                    <span style="font-weight: normal; color: #64748b;">Headquarters</span>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
