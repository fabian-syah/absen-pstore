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
        
        .chart-container { text-align: center; margin-bottom: 20px; }
        .chart-container img { max-width: 350px; height: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; background-color: #fff; }
        .chart-title { font-weight: bold; margin-bottom: 10px; color: #1e3a8a; font-size: 14px; text-align: center; }
        
        .structure-container { margin-bottom: 20px; }
        .structure-title { font-weight: bold; margin-bottom: 10px; color: #1e3a8a; font-size: 14px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; }
        
        .role-section { margin-bottom: 15px; }
        .role-header { background-color: #1e3a8a; color: white; padding: 5px 10px; font-weight: bold; border-radius: 4px; margin-bottom: 5px; font-size: 12px; }
        
        .employee-list { width: 100%; border-collapse: collapse; }
        .employee-list th, .employee-list td { padding: 5px; border: 1px solid #cbd5e1; }
        .employee-list th { background-color: #f1f5f9; text-align: center; }
        .employee-list td.score { text-align: center; font-weight: bold; }
        .employee-list td.grade { text-align: center; font-weight: bold; color: #10b981; }
        
        .footer { text-align: right; font-size: 10px; color: #64748b; margin-top: 30px; border-top: 1px solid #cbd5e1; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>RAPOR EVALUASI CABANG</h1>
        <p>Ringkasan Performa & Kompetensi</p>
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

    @if($chartImage)
    <div class="chart-container">
        <div class="chart-title">Analisis Kompetensi Rata-Rata Cabang</div>
        <img src="{{ $chartImage }}" alt="Radar Chart">
    </div>
    @endif

    <div class="structure-container">
        <div class="structure-title">Struktur & Hasil Evaluasi Karyawan</div>
        
        @php
            $leaders = $users->where('role', 'leader');
            $staff = $users->where('role', '!=', 'leader');
        @endphp

        @if($leaders->count() > 0)
        <div class="role-section">
            <div class="role-header">Leader / Pimpinan Cabang</div>
            <table class="employee-list">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="45%">Nama</th>
                        <th width="20%">Posisi</th>
                        <th width="15%">Grade</th>
                        <th width="15%">Skor Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaders as $index => $user)
                    @php $eval = $evaluations->get($user->id); @endphp
                    <tr>
                        <td align="center">{{ $loop->iteration }}</td>
                        <td>{{ $user->name }}</td>
                        <td align="center" style="text-transform: capitalize;">{{ str_replace('_', ' ', $user->role) }}</td>
                        <td class="grade">{{ $eval ? $eval->grade : '-' }}</td>
                        <td class="score">{{ $eval ? $eval->average_score : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($staff->count() > 0)
        <div class="role-section" style="margin-left: 20px;">
            <div class="role-header" style="background-color: #3b82f6;">Anggota Tim</div>
            <table class="employee-list">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="45%">Nama</th>
                        <th width="20%">Posisi</th>
                        <th width="15%">Grade</th>
                        <th width="15%">Skor Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $index => $user)
                    @php $eval = $evaluations->get($user->id); @endphp
                    <tr>
                        <td align="center">{{ $loop->iteration }}</td>
                        <td>{{ $user->name }}</td>
                        <td align="center" style="text-transform: capitalize;">{{ str_replace('_', ' ', $user->role) }}</td>
                        <td class="grade">{{ $eval ? $eval->grade : '-' }}</td>
                        <td class="score">{{ $eval ? $eval->average_score : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>

</body>
</html>
