<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Evaluasi Cabang</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 15mm;
        }
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.3; margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-bottom: 8px; }
        .header h1 { color: #1e40af; font-size: 16px; margin: 0; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }
        .header p { margin: 2px 0 0 0; font-size: 10px; color: #64748b; }
        
        .branch-info { width: 100%; margin-bottom: 8px; border-collapse: collapse; }
        .branch-info th, .branch-info td { padding: 3px 6px; }
        .branch-info th { color: #475569; text-align: left; width: 15%; font-weight: normal; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; }
        .branch-info td { font-weight: bold; font-size: 11px; color: #0f172a; }
        
        .user-card { border: 1px solid #cbd5e1; background-color: #ffffff; padding: 10px 15px; border-radius: 8px; height: 220px; box-sizing: border-box; page-break-inside: avoid; margin-bottom: 8px; }
        
        .main-table { width: 100%; border-collapse: collapse; height: 100%; }
        
        .chart-cell { width: 200px; text-align: center; border-right: 1px dashed #cbd5e1; padding-right: 15px; vertical-align: middle; }
        .chart-img { width: 190px; height: 190px; object-fit: contain; }
        
        .info-cell { padding-left: 15px; vertical-align: top; }
        
        .profile-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .user-photo { width: 75px; height: 75px; border-radius: 37.5px; border: 2px solid #e2e8f0; object-fit: cover; display: inline-block; }
        .user-photo-placeholder { width: 75px; height: 75px; border-radius: 37.5px; background-color: #3b82f6; color: white; text-align: center; line-height: 71px; font-weight: bold; font-size: 26px; border: 2px solid #e2e8f0; display: inline-block; box-sizing: border-box; vertical-align: middle; }
        
        .user-name { font-size: 16px; font-weight: bold; color: #0f172a; margin-bottom: 4px; }
        .user-meta { font-size: 12px; color: #64748b; line-height: 1.4; }
        
        .score-box-wrapper { text-align: right; vertical-align: top; }
        .score-box { display: inline-block; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; text-align: center; margin-left: 8px; }
        .score-label { font-size: 10px; color: #64748b; text-transform: uppercase; margin-bottom: 3px;}
        .score-value { font-size: 18px; font-weight: bold; color: #1e40af; }
        .grade-value { font-size: 18px; font-weight: bold; color: #10b981; }
        
        .notes-section { width: 100%; }
        .notes-label { font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px; }
        .textarea-box { border: 1px solid #cbd5e1; border-radius: 6px; height: 60px; width: 100%; background-color: #fafafa; }
        
        .footer { text-align: right; font-size: 9px; color: #94a3b8; margin-top: 8px; border-top: 1px solid #e2e8f0; padding-top: 4px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>RAPOR EVALUASI CABANG</h1>
        <p>Detail Kompetensi per Karyawan</p>
    </div>

    <table class="branch-info">
        <tr>
            <th>Nama Cabang</th>
            <td>{{ $branch->name }}</td>
            <th>Tanggal Evaluasi</th>
            <td>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
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
                            @else
                                <div style="padding: 10px; color: #94a3b8; font-size: 9px;">Belum Ada Evaluasi</div>
                            @endif
                        </td>
                        <td class="info-cell">
                            <table class="profile-table">
                                <tr>
                                    <td style="width: 85px; vertical-align: middle;">
                                        @if(isset($userPhotos[$user->id]) && $userPhotos[$user->id])
                                            <img src="{{ $userPhotos[$user->id] }}" class="user-photo" alt="Photo">
                                        @else
                                            <div class="user-photo-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="user-name">{{ $user->name }}</div>
                                        <div class="user-meta">Cabang: <strong>{{ $branch->name }}</strong></div>
                                        <div class="user-meta">Divisi: <strong>{{ $user->divisions && $user->divisions->count() > 0 ? $user->divisions->pluck('name')->join(', ') : ($user->division ? $user->division->name : '-') }}</strong></div>
                                    </td>
                                    <td class="score-box-wrapper">
                                        <div class="score-box">
                                            <div class="score-label">Skor</div>
                                            <div class="score-value">{{ $eval ? $eval->average_score : '-' }}</div>
                                        </div>
                                        <div class="score-box">
                                            <div class="score-label">Grade</div>
                                            <div class="grade-value">{{ $eval ? $eval->grade : '-' }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <div class="notes-section">
                                <div class="notes-label">Catatan Evaluasi:</div>
                                <div class="textarea-box"></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>

</body>
</html>
