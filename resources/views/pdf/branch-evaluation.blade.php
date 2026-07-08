<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Evaluasi Cabang</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1e293b; line-height: 1.4; margin: 0; }
        .header { text-align: center; border-bottom: 3px solid #1e40af; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { color: #1e40af; font-size: 20px; margin: 0; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }
        .header p { margin: 5px 0 0 0; font-size: 12px; color: #64748b; }
        
        .branch-info { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .branch-info th, .branch-info td { padding: 4px 8px; }
        .branch-info th { color: #475569; text-align: left; width: 20%; font-weight: normal; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; }
        .branch-info td { font-weight: bold; font-size: 12px; color: #0f172a; }
        
        .users-table { width: 100%; border-collapse: separate; border-spacing: 10px; margin: -10px; }
        
        .user-card { border: 1px solid #cbd5e1; background-color: #ffffff; padding: 10px; border-radius: 8px; height: 380px; box-sizing: border-box; page-break-inside: avoid; }
        
        .card-layout { display: table; width: 100%; height: 100%; }
        .card-left { display: table-cell; width: 45%; vertical-align: middle; text-align: center; border-right: 1px dashed #cbd5e1; padding-right: 10px; }
        .card-right { display: table-cell; width: 55%; vertical-align: top; padding-left: 10px; }
        
        .profile-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .profile-table td { vertical-align: top; }
        .user-photo { width: 40px; height: 40px; border-radius: 20px; border: 2px solid #e2e8f0; }
        .user-photo-placeholder { width: 40px; height: 40px; border-radius: 20px; background-color: #3b82f6; color: white; text-align: center; line-height: 40px; font-weight: bold; font-size: 16px; border: 2px solid #e2e8f0; }
        
        .user-name { font-size: 13px; font-weight: bold; color: #0f172a; margin-bottom: 2px; }
        .user-meta { font-size: 10px; color: #64748b; line-height: 1.3; }
        
        .score-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px; margin-bottom: 8px; text-align: center; }
        .score-box table { width: 100%; border-collapse: collapse; }
        .score-box td { width: 50%; text-align: center; }
        .score-label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .score-value { font-size: 14px; font-weight: bold; color: #1e40af; }
        .grade-value { font-size: 14px; font-weight: bold; color: #10b981; }
        
        .notes-label { font-size: 11px; font-weight: bold; color: #475569; margin-bottom: 4px; }
        .textarea-box { border: 1px solid #cbd5e1; border-radius: 6px; height: 180px; width: 100%; background-color: #fafafa; }
        
        .footer { text-align: right; font-size: 10px; color: #94a3b8; margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 5px; }
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

    <table class="users-table">
        @php $cols = 2; @endphp
        @foreach($users->chunk($cols) as $chunk)
            <tr>
            @foreach($chunk as $user)
                @php $eval = $evaluations->get($user->id); @endphp
                <td style="width: 50%; vertical-align: top; padding: 0;">
                    <div class="user-card">
                        <div class="card-layout">
                            <div class="card-left">
                                @if(isset($userCharts[$user->id]) && $userCharts[$user->id])
                                    <img src="{{ $userCharts[$user->id] }}" alt="Chart" style="max-width: 100%; max-height: 250px;">
                                @else
                                    <div style="padding: 10px; color: #94a3b8; font-size: 10px;">Belum Ada Evaluasi</div>
                                @endif
                            </div>
                            <div class="card-right">
                                <table class="profile-table">
                                    <tr>
                                        <td style="width: 48px;">
                                            @if(isset($userPhotos[$user->id]) && $userPhotos[$user->id])
                                                <img src="{{ $userPhotos[$user->id] }}" class="user-photo" alt="Photo">
                                            @else
                                                <div class="user-photo-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="user-name">{{ $user->name }}</div>
                                            <div class="user-meta">Cabang: <strong>{{ $branch->name }}</strong></div>
                                            <div class="user-meta">Divisi: <strong>{{ str_replace('_', ' ', $user->role) }}</strong></div>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="score-box">
                                    <table>
                                        <tr>
                                            <td style="border-right: 1px solid #e2e8f0;">
                                                <div class="score-label">Skor</div>
                                                <div class="score-value">{{ $eval ? $eval->average_score : '-' }} / 100</div>
                                            </td>
                                            <td>
                                                <div class="score-label">Grade</div>
                                                <div class="grade-value">{{ $eval ? $eval->grade : '-' }}</div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class="notes-label">Catatan Evaluasi:</div>
                                <div class="textarea-box"></div>
                            </div>
                        </div>
                    </div>
                </td>
            @endforeach
            @if($chunk->count() < $cols)
                <td style="width: 50%; padding: 0;"></td>
            @endif
            </tr>
        @endforeach
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>

</body>
</html>
