<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Evaluasi Cabang</title>
    <style>
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1e293b; line-height: 1.4; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #1e40af; padding-bottom: 15px; margin-bottom: 25px; }
        .header h1 { color: #1e40af; font-size: 22px; margin: 0; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }
        .header p { margin: 5px 0 0 0; font-size: 13px; color: #64748b; }
        
        .branch-info { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
        .branch-info th, .branch-info td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        .branch-info th { color: #475569; text-align: left; width: 30%; font-weight: normal; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        .branch-info td { font-weight: bold; font-size: 13px; color: #0f172a; }
        
        .role-section { margin-bottom: 30px; }
        .role-header { background-color: #1e40af; color: white; padding: 8px 15px; font-weight: bold; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        
        .user-card { border: 1px solid #cbd5e1; background-color: #ffffff; padding: 10px; border-radius: 8px; page-break-inside: avoid; }
        
        .user-header-table { width: 100%; margin-bottom: 8px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px; border-collapse: collapse; }
        .user-header-table td { vertical-align: middle; }
        .user-photo { width: 35px; height: 35px; border-radius: 17.5px; border: 2px solid #e2e8f0; }
        .user-photo-placeholder { width: 35px; height: 35px; border-radius: 17.5px; background-color: #3b82f6; color: white; text-align: center; line-height: 35px; font-weight: bold; font-size: 14px; border: 2px solid #e2e8f0; }
        .user-info { padding-left: 10px; }
        
        .user-name { font-size: 14px; font-weight: bold; color: #0f172a; }
        .user-role { font-size: 11px; color: #64748b; text-transform: capitalize; margin-top: 2px; }
        
        .user-content { width: 100%; display: table; }
        .user-chart { display: table-cell; width: 40%; text-align: center; vertical-align: middle; padding-right: 10px; border-right: 1px dashed #cbd5e1; }
        .user-chart img { max-width: 100%; height: auto; }
        
        .user-details { display: table-cell; width: 60%; vertical-align: middle; padding-left: 10px; }
        
        .score-info { margin-bottom: 5px; }
        .score-label { font-weight: bold; color: #475569; font-size: 11px; }
        .score-value { font-size: 14px; font-weight: bold; color: #1e40af; }
        .grade-value { font-size: 16px; font-weight: bold; color: #10b981; }
        
        .remark-box { margin-top: 8px; padding: 8px; background-color: #f8fafc; border-left: 4px solid #3b82f6; font-size: 11px; color: #475569; font-style: italic; line-height: 1.4; border-radius: 0 6px 6px 0; }

        .footer { text-align: right; font-size: 10px; color: #94a3b8; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
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
            <td style="font-weight: bold;">{{ $branch->name }}</td>
        </tr>
        <tr>
            <th>Tanggal Evaluasi</th>
            <td>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <th>Total Karyawan</th>
            <td>{{ $users->count() }} Orang</td>
        </tr>
    </table>

    @php
        $leaders = $users->where('role', 'leader');
        $staff = $users->where('role', '!=', 'leader');
    @endphp

    @if($leaders->count() > 0)
    <div class="role-section">
        <div class="role-header">Leader / Pimpinan Cabang</div>
        <table style="width: 100%; border-collapse: separate; border-spacing: 10px; margin: -10px;">
        @foreach($leaders->chunk(2) as $chunk)
            <tr>
            @foreach($chunk as $user)
                @php $eval = $evaluations->get($user->id); @endphp
                <td style="width: 50%; vertical-align: top; padding: 0;">
                    <div class="user-card">
                        <table class="user-header-table">
                            <tr>
                                <td style="width: 40px; text-align: center;">
                                    @if(isset($userPhotos[$user->id]) && $userPhotos[$user->id])
                                        <img src="{{ $userPhotos[$user->id] }}" class="user-photo" alt="Photo">
                                    @else
                                        <div class="user-photo-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    @endif
                                </td>
                                <td class="user-info">
                                    <div class="user-name">{{ $user->name }}</div>
                                    <div class="user-role">{{ str_replace('_', ' ', $user->role) }}</div>
                                </td>
                            </tr>
                        </table>
                        <div class="user-content">
                            <div class="user-chart">
                                @if(isset($userCharts[$user->id]) && $userCharts[$user->id])
                                    <img src="{{ $userCharts[$user->id] }}" alt="Chart">
                                @else
                                    <div style="padding: 10px; color: #94a3b8; font-size: 10px;">Belum Ada Evaluasi</div>
                                @endif
                            </div>
                            <div class="user-details">
                                <div class="score-info">
                                    <span class="score-label">Rata-rata Skor:</span> 
                                    <span class="score-value">{{ $eval ? $eval->average_score : '-' }} / 100</span>
                                </div>
                                <div class="score-info">
                                    <span class="score-label">Grade:</span> 
                                    <span class="grade-value">{{ $eval ? $eval->grade : '-' }}</span>
                                </div>
                                <div class="remark-box">
                                    "{{ $eval && $eval->final_remark ? $eval->final_remark : 'Belum ada catatan evaluasi' }}"
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            @endforeach
            @if($chunk->count() == 1)
                <td style="width: 50%; padding: 0;"></td>
            @endif
            </tr>
        @endforeach
        </table>
    </div>
    @endif

    @if($staff->count() > 0)
    <div class="role-section">
        <div class="role-header" style="background-color: #3b82f6;">Anggota Tim</div>
        <table style="width: 100%; border-collapse: separate; border-spacing: 10px; margin: -10px;">
        @foreach($staff->chunk(2) as $chunk)
            <tr>
            @foreach($chunk as $user)
                @php $eval = $evaluations->get($user->id); @endphp
                <td style="width: 50%; vertical-align: top; padding: 0;">
                    <div class="user-card">
                        <table class="user-header-table">
                            <tr>
                                <td style="width: 40px; text-align: center;">
                                    @if(isset($userPhotos[$user->id]) && $userPhotos[$user->id])
                                        <img src="{{ $userPhotos[$user->id] }}" class="user-photo" alt="Photo">
                                    @else
                                        <div class="user-photo-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    @endif
                                </td>
                                <td class="user-info">
                                    <div class="user-name">{{ $user->name }}</div>
                                    <div class="user-role">{{ str_replace('_', ' ', $user->role) }}</div>
                                </td>
                            </tr>
                        </table>
                        <div class="user-content">
                            <div class="user-chart">
                                @if(isset($userCharts[$user->id]) && $userCharts[$user->id])
                                    <img src="{{ $userCharts[$user->id] }}" alt="Chart">
                                @else
                                    <div style="padding: 10px; color: #94a3b8; font-size: 10px;">Belum Ada Evaluasi</div>
                                @endif
                            </div>
                            <div class="user-details">
                                <div class="score-info">
                                    <span class="score-label">Rata-rata Skor:</span> 
                                    <span class="score-value">{{ $eval ? $eval->average_score : '-' }} / 100</span>
                                </div>
                                <div class="score-info">
                                    <span class="score-label">Grade:</span> 
                                    <span class="grade-value">{{ $eval ? $eval->grade : '-' }}</span>
                                </div>
                                <div class="remark-box">
                                    "{{ $eval && $eval->final_remark ? $eval->final_remark : 'Belum ada catatan evaluasi' }}"
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            @endforeach
            @if($chunk->count() == 1)
                <td style="width: 50%; padding: 0;"></td>
            @endif
            </tr>
        @endforeach
        </table>
    </div>
    @endif

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>

</body>
</html>
