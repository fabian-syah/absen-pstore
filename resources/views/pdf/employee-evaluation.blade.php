<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Evaluasi Kinerja Karyawan</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.3; margin: 0; padding: 10px; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { color: #1e3a8a; font-size: 18px; margin: 0 0 3px 0; text-transform: uppercase; }
        .header p { margin: 0; font-size: 11px; color: #64748b; }
        .employee-info { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .employee-info td { padding: 4px 8px; border: 1px solid #e2e8f0; }
        .employee-info th { padding: 4px 8px; border: 1px solid #e2e8f0; text-align: left; background-color: #f8fafc; color: #475569; width: 25%; }
        
        .score-section { margin-bottom: 15px; }
        .score-section h3 { font-size: 14px; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 8px; }
        
        .score-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .score-table th, .score-table td { padding: 5px 8px; border: 1px solid #cbd5e1; }
        .score-table th { background-color: #f1f5f9; text-align: center; color: #334155; }
        .score-table td.criteria { font-weight: bold; width: 25%; }
        .score-table td.score { text-align: center; font-weight: bold; width: 10%; font-size: 13px; }
        
        .summary-box { background-color: #f8fafc; border: 1px solid #94a3b8; padding: 10px; text-align: center; margin-bottom: 15px; border-radius: 6px; }
        .summary-box .grade { font-size: 24px; font-weight: bold; color: #10b981; margin: 3px 0; }
        .summary-box .average { font-size: 13px; color: #475569; }
        
        .remark-box { background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 10px 12px; font-style: italic; margin-bottom: 15px; font-size: 11px; line-height: 1.4; }
        
        .footer { width: 100%; margin-top: 15px; }
        .signature-box { float: right; width: 200px; text-align: center; }
        .signature-line { border-bottom: 1px solid #000; margin-top: 40px; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>RAPOR STATISTIK KARYAWAN</h1>
        <p>EVALUASI PERFORMA BULANAN</p>
    </div>

    <table class="employee-info" style="border: none;">
        <tr>
            <td rowspan="4" style="width: 140px; text-align: center; border: none; padding: 0 15px 0 0; vertical-align: middle;">
                @php
                    $photoUrl = null;
                    if ($user->profile_photo_path && file_exists(public_path('storage/' . $user->profile_photo_path))) {
                        $path = public_path('storage/' . $user->profile_photo_path);
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $photoUrl = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                @endphp
                @if($photoUrl)
                    <div style="width: 130px; height: 130px; overflow: hidden; border-radius: 8px; border: 1px solid #cbd5e1; display: inline-block;">
                        <img src="{{ $photoUrl }}" style="height: 130px; width: auto; max-width: 130px; display: block; margin: 0 auto;">
                    </div>
                @else
                    <div style="width: 130px; height: 130px; background-color: #e2e8f0; border-radius: 8px; border: 1px solid #cbd5e1; line-height: 130px; color: #64748b; font-size: 14px; margin: 0 auto;">Tanpa Foto</div>
                @endif
            </td>
            <th>Nama Karyawan</th>
            <td style="font-weight: bold; font-size: 15px;">{{ $user->name }}</td>
        </tr>
        <tr>
            <th>Periode Evaluasi</th>
            <td>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</td>
        </tr>
        <tr>
            <th>Divisi / Cabang</th>
            <td>{{ $user->branch->name ?? 'Pusat' }} {{ isset($user->division) ? ' - ' . $user->division->name : '' }}</td>
        </tr>
        @if($user->role !== 'user_biasa')
        <tr>
            <th>Posisi / Role</th>
            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $user->role) }}</td>
        </tr>
        @endif
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
