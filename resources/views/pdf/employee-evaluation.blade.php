<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Evaluasi Kinerja Karyawan</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; line-height: 1.5; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #1e3a8a; font-size: 24px; margin: 0 0 5px 0; text-transform: uppercase; }
        .header p { margin: 0; font-size: 14px; color: #64748b; }
        .employee-info { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
        .employee-info td { padding: 8px 12px; border: 1px solid #e2e8f0; }
        .employee-info th { padding: 8px 12px; border: 1px solid #e2e8f0; text-align: left; background-color: #f8fafc; color: #475569; width: 30%; }
        
        .score-section { margin-bottom: 30px; }
        .score-section h3 { font-size: 18px; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px; margin-bottom: 15px; }
        
        .score-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .score-table th, .score-table td { padding: 10px; border: 1px solid #cbd5e1; }
        .score-table th { background-color: #f1f5f9; text-align: center; color: #334155; }
        .score-table td.criteria { font-weight: bold; width: 25%; }
        .score-table td.score { text-align: center; font-weight: bold; width: 15%; font-size: 16px; }
        
        .summary-box { background-color: #f8fafc; border: 1px solid #94a3b8; padding: 20px; text-align: center; margin-bottom: 30px; border-radius: 8px; }
        .summary-box .grade { font-size: 36px; font-weight: bold; color: #10b981; margin: 10px 0; }
        .summary-box .average { font-size: 18px; color: #475569; }
        
        .remark-box { background-color: #eff6ff; border-left: 5px solid #3b82f6; padding: 15px 20px; font-style: italic; margin-bottom: 40px; }
        
        .footer { width: 100%; margin-top: 50px; }
        .signature-box { float: right; width: 300px; text-align: center; }
        .signature-line { border-bottom: 1px solid #000; margin-top: 60px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>RAPOR STATISTIK KARYAWAN</h1>
        <p>EVALUASI PERFORMA BULANAN</p>
    </div>

    <table class="employee-info">
        <tr>
            <th>Nama Karyawan</th>
            <td style="font-weight: bold; font-size: 16px;">{{ $user->name }}</td>
        </tr>
        <tr>
            <th>Periode Evaluasi</th>
            <td>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</td>
        </tr>
        <tr>
            <th>Divisi / Cabang</th>
            <td>{{ $user->branch->name ?? 'Pusat' }} {{ isset($user->division) ? ' - ' . $user->division->name : '' }}</td>
        </tr>
        <tr>
            <th>Posisi / Role</th>
            <td>{{ $user->role }}</td>
        </tr>
    </table>

    <div class="score-section">
        <h3>Detail Penilaian Kompetensi</h3>
        <table class="score-table">
            <thead>
                <tr>
                    <th>Aspek / Kriteria</th>
                    <th>Nilai (0-100)</th>
                    <th>Catatan Penilai (Opsional)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="criteria">Kecerdasan</td>
                    <td class="score">{{ $evaluation->kecerdasan_score ?? '-' }}</td>
                    <td>{{ $evaluation->kecerdasan_note ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="criteria">Amanah</td>
                    <td class="score">{{ $evaluation->amanah_score ?? '-' }}</td>
                    <td>{{ $evaluation->amanah_note ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="criteria">Sosial Media</td>
                    <td class="score">{{ $evaluation->sosial_media_score ?? '-' }}</td>
                    <td>{{ $evaluation->sosial_media_note ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="criteria">Kepemimpinan</td>
                    <td class="score">{{ $evaluation->kepemimpinan_score ?? '-' }}</td>
                    <td>{{ $evaluation->kepemimpinan_note ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="criteria">Data & Ketelitian</td>
                    <td class="score">{{ $evaluation->data_ketelitian_score ?? '-' }}</td>
                    <td>{{ $evaluation->data_ketelitian_note ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="criteria">Komunikasi</td>
                    <td class="score">{{ $evaluation->komunikasi_score ?? '-' }}</td>
                    <td>{{ $evaluation->komunikasi_note ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="criteria">Kedisiplinan</td>
                    <td class="score">{{ $evaluation->kedisiplinan_score ?? '-' }}</td>
                    <td>{{ $evaluation->kedisiplinan_note ?? '-' }}</td>
                </tr>
                @if($evaluation->custom_title)
                <tr>
                    <td class="criteria">{{ $evaluation->custom_title }}</td>
                    <td class="score">{{ $evaluation->custom_score ?? '-' }}</td>
                    <td>{{ $evaluation->custom_note ?? '-' }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="summary-box">
        <div style="font-size: 16px; font-weight: bold; text-transform: uppercase;">Hasil Akhir Penilaian</div>
        <div class="grade">GRADE {{ $evaluation->grade }}</div>
        <div class="average">Rata-rata Skor: <b>{{ $evaluation->average_score }}</b> / 100</div>
    </div>

    <div class="score-section">
        <h3>Kesimpulan & Motivasi</h3>
        <div class="remark-box">
            "{{ $evaluation->final_remark ?? 'Belum ada catatan evaluasi untuk ditampilkan.' }}"
        </div>
    </div>

    <div class="footer">
        <div class="signature-box">
            <p>Dinilai Oleh,</p>
            <div class="signature-line"></div>
            <strong>{{ $evaluation->assessor ? $evaluation->assessor->name : 'Tim HR / Penilai' }}</strong>
            <div style="font-size: 12px; color: #666; margin-top: 5px;">{{ $evaluation->assessor ? $evaluation->assessor->role : 'Manajemen' }}</div>
            <div style="font-size: 12px; color: #666;">Tanggal: {{ \Carbon\Carbon::parse($evaluation->updated_at)->translatedFormat('d F Y') }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
