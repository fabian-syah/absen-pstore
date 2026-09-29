<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Evaluasi Karyawan Pusat</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.3;
            margin: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #1e40af;
            font-size: 17px;
            margin: 0;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #64748b;
        }
        
        .summary-info {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .summary-info th, .summary-info td {
            padding: 5px 8px;
        }
        .summary-info th {
            color: #475569;
            text-align: left;
            width: 18%;
            font-weight: normal;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        .summary-info td {
            font-weight: bold;
            font-size: 11px;
            color: #0f172a;
        }
        
        .user-card {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 10px 14px;
            border-radius: 8px;
            height: 232px;
            box-sizing: border-box;
            page-break-inside: avoid;
            margin-bottom: 8px;
            position: relative;
            overflow: hidden;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            height: 100%;
        }
        
        .chart-cell {
            width: 195px;
            text-align: center;
            border-right: 1px dashed #cbd5e1;
            padding-right: 12px;
            vertical-align: middle;
        }
        .chart-img {
            width: 185px;
            height: 185px;
            object-fit: contain;
        }
        
        .score-fallback-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin: 0 auto;
        }
        .score-fallback-table td {
            padding: 2px 4px;
            border-bottom: 1px dotted #e2e8f0;
        }
        .score-fallback-table td.criteria {
            color: #475569;
            text-align: left;
        }
        .score-fallback-table td.score {
            font-weight: bold;
            text-align: right;
            color: #1e40af;
        }
        
        .info-cell {
            padding-left: 14px;
            vertical-align: top;
        }
        
        .profile-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .user-photo {
            width: 76px;
            height: 76px;
            border-radius: 6px;
            border: 2px solid #e2e8f0;
            display: block;
            margin: 0 auto;
        }
        .user-photo-placeholder {
            width: 76px;
            height: 76px;
            border-radius: 6px;
            background-color: #3b82f6;
            color: white;
            text-align: center;
            line-height: 72px;
            font-weight: bold;
            font-size: 26px;
            border: 2px solid #e2e8f0;
            display: block;
            box-sizing: border-box;
            margin: 0 auto;
        }
        
        .user-name {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .unit-badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .user-meta {
            font-size: 10px;
            color: #64748b;
            line-height: 1.3;
        }
        
        .score-box-wrapper {
            text-align: right;
            vertical-align: top;
        }
        .score-box {
            display: inline-block;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            text-align: center;
            margin-left: 6px;
        }
        .score-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 1px;
        }
        .score-value {
            font-size: 17px;
            font-weight: bold;
            color: #1e40af;
        }
        .grade-value {
            font-size: 17px;
            font-weight: bold;
            color: #10b981;
        }
        
        .notes-section {
            width: 100%;
            margin-top: 5px;
            box-sizing: border-box;
        }
        .notes-label {
            font-size: 10px;
            font-weight: bold;
            color: #475569;
            margin-bottom: 2px;
        }
        .textarea-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 4px 6px;
            min-height: 38px;
            background-color: #fafafa;
            display: block;
            box-sizing: border-box;
            font-size: 9px;
            color: #334155;
            font-style: italic;
        }
        
        .footer {
            text-align: right;
            font-size: 9px;
            color: #94a3b8;
            margin-top: 8px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>RAPOR EVALUASI KARYAWAN PUSAT</h1>
        <p>Rekapitulasi Penilaian Kinerja Karyawan Kantor & Unit Pusat (Yang Sudah Dinilai)</p>
    </div>

    <table class="summary-info">
        <tr>
            <th>Kategori</th>
            <td>Kantor & Unit Pusat PStore</td>
            <th>Tanggal Unduh</th>
            <td>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <th>Total Dinilai</th>
            <td><strong style="color: #1e40af;">{{ count($users) }} Karyawan</strong> (dari {{ count($pusatBranches) }} Unit Pusat)</td>
            <th>Status</th>
            <td><span style="color: #10b981; font-weight: bold;">Hanya yang Sudah Dinilai</span></td>
        </tr>
    </table>

    <div class="users-container">
        @foreach($users as $user)
            @php $eval = $evaluations->get($user->id); @endphp
            <div class="user-card">
                <table class="main-table">
                    <tr>
                        <td class="chart-cell">
                            @if(isset($userCharts[$user->id]) && $userCharts[$user->id])
                                <img src="{{ $userCharts[$user->id] }}" alt="Chart" class="chart-img">
                                @if($eval)
                                    <div style="font-size: 9px; color: #64748b; margin-top: 3px; font-weight: bold;">
                                        Evaluasi: {{ \Carbon\Carbon::parse($eval->evaluation_date)->translatedFormat('d M Y') }}
                                    </div>
                                @endif
                            @elseif($eval)
                                <div style="padding: 4px 6px;">
                                    <div style="font-size: 9px; font-weight: bold; color: #1e40af; margin-bottom: 4px;">Detail Skor:</div>
                                    <table class="score-fallback-table">
                                        <tr><td class="criteria">Kecerdasan</td><td class="score">{{ (int)$eval->kecerdasan_score }}</td></tr>
                                        <tr><td class="criteria">Amanah</td><td class="score">{{ (int)$eval->amanah_score }}</td></tr>
                                        <tr><td class="criteria">Sosial Media</td><td class="score">{{ (int)$eval->sosial_media_score }}</td></tr>
                                        <tr><td class="criteria">Kepemimpinan</td><td class="score">{{ (int)$eval->kepemimpinan_score }}</td></tr>
                                        <tr><td class="criteria">Data & Teliti</td><td class="score">{{ (int)$eval->data_ketelitian_score }}</td></tr>
                                        <tr><td class="criteria">Komunikasi</td><td class="score">{{ (int)$eval->komunikasi_score }}</td></tr>
                                        <tr><td class="criteria">Kedisiplinan</td><td class="score">{{ (int)$eval->kedisiplinan_score }}</td></tr>
                                        @if($eval->custom_score !== null && $eval->custom_score !== '')
                                        <tr><td class="criteria">{{ Str::limit($eval->custom_title ?? 'Kriteria Lain', 12) }}</td><td class="score">{{ (int)$eval->custom_score }}</td></tr>
                                        @endif
                                    </table>
                                    <div style="font-size: 8px; color: #64748b; margin-top: 3px;">
                                        Tgl: {{ \Carbon\Carbon::parse($eval->evaluation_date)->translatedFormat('d M Y') }}
                                    </div>
                                </div>
                            @else
                                <div style="padding: 10px; color: #94a3b8; font-size: 9px;">Belum Ada Evaluasi</div>
                            @endif
                        </td>
                        <td class="info-cell">
                            <table class="profile-table">
                                <tr>
                                    <td style="width: 88px; vertical-align: middle;">
                                        @if(isset($userPhotos[$user->id]) && $userPhotos[$user->id])
                                            <img src="{{ $userPhotos[$user->id] }}" width="76" height="76" class="user-photo" alt="Photo">
                                        @else
                                            <div class="user-photo-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="unit-badge">Pusat &bull; {{ $user->branch->name ?? '-' }}</div>
                                        <div class="user-name">{{ $user->name }}</div>
                                        <div class="user-meta">Role: <strong>{{ ucwords(str_replace('_', ' ', $user->role)) }}</strong></div>
                                        <div class="user-meta">Divisi: <strong>{{ $user->divisions && $user->divisions->count() > 0 ? $user->divisions->pluck('name')->join(', ') : ($user->division ? $user->division->name : '-') }}</strong></div>
                                        @if($eval && $eval->assessor)
                                        <div class="user-meta">Dinilai Oleh: <strong>{{ $eval->assessor->name }}</strong></div>
                                        @endif
                                    </td>
                                    <td class="score-box-wrapper">
                                        <div class="score-box">
                                            <div class="score-label">Skor</div>
                                            <div class="score-value">{{ $eval ? number_format($eval->average_score, 1) : '-' }}</div>
                                        </div>
                                        <div class="score-box">
                                            <div class="score-label">Grade</div>
                                            <div class="grade-value">{{ $eval ? $eval->grade : '-' }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <div class="notes-section">
                                <div class="notes-label">Catatan & Motivasi:</div>
                                <div class="textarea-box">
                                    {{ $eval && $eval->notes ? $eval->notes : 'Tidak ada catatan khusus.' }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} &bull; Total {{ count($users) }} Karyawan Pusat
    </div>

</body>
</html>
