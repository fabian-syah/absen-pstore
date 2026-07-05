<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Evaluasi Cabang</title>
    <style>
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.3; margin: 0; padding: 10px; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { color: #1e3a8a; font-size: 18px; margin: 0; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; font-size: 12px; color: #64748b; }
        
        .branch-info { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .branch-info th, .branch-info td { padding: 5px; border: 1px solid #e2e8f0; }
        .branch-info th { background-color: #f8fafc; color: #475569; text-align: left; width: 30%; }
        
        .structure-title { font-weight: bold; margin-bottom: 10px; color: #1e3a8a; font-size: 14px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; }
        
        .role-section { margin-bottom: 15px; }
        .role-header { background-color: #1e3a8a; color: white; padding: 5px 10px; font-weight: bold; border-radius: 4px; margin-bottom: 15px; font-size: 12px; }
        
        .user-card { border: 1px solid #cbd5e1; padding: 10px; margin-bottom: 15px; border-radius: 6px; page-break-inside: avoid; }
        
        .user-header { border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; }
        .user-name { font-size: 14px; font-weight: bold; color: #0f172a; }
        .user-role { font-size: 11px; color: #64748b; text-transform: capitalize; }
        
        .user-content { width: 100%; display: table; }
        .user-chart { display: table-cell; width: 40%; text-align: center; vertical-align: middle; }
        .user-chart img { max-width: 200px; height: auto; }
        
        .user-details { display: table-cell; width: 60%; vertical-align: middle; padding-left: 15px; }
        
        .score-info { margin-bottom: 8px; }
        .score-label { font-weight: bold; color: #475569; }
        .score-value { font-size: 13px; font-weight: bold; color: #1e40af; }
        .grade-value { font-size: 16px; font-weight: bold; color: #10b981; }

        .footer { text-align: right; font-size: 10px; color: #64748b; margin-top: 30px; border-top: 1px solid #cbd5e1; padding-top: 5px; }
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
            <th>Periode Evaluasi</th>
            <td>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</td>
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
        @foreach($leaders as $user)
            @php $eval = $evaluations->get($user->id); @endphp
            <div class="user-card">
                <div class="user-header">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ str_replace('_', ' ', $user->role) }}</div>
                </div>
                <div class="user-content">
                    <div class="user-chart">
                        @if(isset($userCharts[$user->id]) && $userCharts[$user->id])
                            <img src="{{ $userCharts[$user->id] }}" alt="Chart">
                        @else
                            <div style="padding: 30px; color: #94a3b8; border: 1px dashed #cbd5e1;">Belum Ada Evaluasi</div>
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
                        <div style="margin-top: 10px; font-size: 10px; color: #64748b; font-style: italic;">
                            "{{ $eval && $eval->final_remark ? $eval->final_remark : 'Belum ada catatan' }}"
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    @if($staff->count() > 0)
    <div class="role-section" style="margin-left: 20px;">
        <div class="role-header" style="background-color: #3b82f6;">Anggota Tim</div>
        @foreach($staff as $user)
            @php $eval = $evaluations->get($user->id); @endphp
            <div class="user-card">
                <div class="user-header">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ str_replace('_', ' ', $user->role) }}</div>
                </div>
                <div class="user-content">
                    <div class="user-chart">
                        @if(isset($userCharts[$user->id]) && $userCharts[$user->id])
                            <img src="{{ $userCharts[$user->id] }}" alt="Chart">
                        @else
                            <div style="padding: 30px; color: #94a3b8; border: 1px dashed #cbd5e1;">Belum Ada Evaluasi</div>
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
                        <div style="margin-top: 10px; font-size: 10px; color: #64748b; font-style: italic;">
                            "{{ $eval && $eval->final_remark ? $eval->final_remark : 'Belum ada catatan' }}"
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>

</body>
</html>
