<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Evaluasi Kinerja Karyawan</title>
    <style>
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.2; margin: 0; padding: 5px; }
        .header { text-align: center; border-bottom: 1px solid #1e3a8a; padding-bottom: 5px; margin-bottom: 8px; }
        .header h1 { color: #1e3a8a; font-size: 14px; margin: 0; text-transform: uppercase; }
        .header p { margin: 0; font-size: 10px; color: #64748b; }
        .employee-info { width: 100%; margin-bottom: 8px; border-collapse: collapse; }
        .employee-info td { padding: 3px 5px; border: 1px solid #e2e8f0; }
        .employee-info th { padding: 3px 5px; border: 1px solid #e2e8f0; text-align: left; background-color: #f8fafc; color: #475569; width: 25%; }
        
        .score-section { margin-bottom: 8px; }
        .score-section h3 { font-size: 12px; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; margin-bottom: 5px; }
        
        .score-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .score-table th, .score-table td { padding: 3px 5px; border: 1px solid #cbd5e1; }
        .score-table th { background-color: #f1f5f9; text-align: center; color: #334155; }
        .score-table td.criteria { font-weight: bold; width: 25%; }
        .score-table td.score { text-align: center; font-weight: bold; width: 10%; font-size: 11px; }
        
        .remark-box { background-color: #eff6ff; border-left: 3px solid #3b82f6; padding: 8px 10px; font-style: italic; margin-bottom: 10px; font-size: 10px; line-height: 1.3; }
    </style>
</head>
<body>

    <div class="header">
        <h1>RAPOR STATISTIK KARYAWAN</h1>
        <p>EVALUASI PERFORMA BULANAN</p>
    </div>

    <table class="employee-info" style="border: none;">
        <tr>
            <td rowspan="3" style="width: 140px; text-align: center; border: none; padding: 0 12px 0 0; vertical-align: middle;">
                @php
                    if (!isset($photoUrl) || !$photoUrl) {
                        $photoUrl = \App\Http\Controllers\EmployeeEvaluationController::getProfilePhotoData($user->profile_photo_path, 280);
                    }
                @endphp
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" width="130" height="130" style="width: 130px; height: 130px; border-radius: 8px; border: 1px solid #cbd5e1; display: block; margin: 0 auto;" alt="Foto">
                @else
                    <div style="width: 130px; height: 130px; background-color: #f1f5f9; border-radius: 8px; border: 1px solid #cbd5e1; line-height: 130px; color: #94a3b8; font-size: 12px; margin: 0 auto; text-align: center;">Tanpa Foto</div>
                @endif
            </td>
            <th>Nama Karyawan</th>
            <td style="font-weight: bold; font-size: 13px;">{{ $user->name }}</td>
            <td rowspan="3" style="width: 140px; border: 2px solid #1e3a8a; text-align: center; vertical-align: middle; background-color: #f8fafc; padding: 10px;">
                <div style="font-size: 11px; color: #64748b; font-weight: bold; text-transform: uppercase; margin-bottom: 8px;">Grade Akhir</div>
                <div style="font-size: 28px; font-weight: bold; color: #10b981; margin-bottom: 5px;">{{ $evaluation->grade }}</div>
                <div style="font-size: 13px; color: #475569;">Skor: <b>{{ $evaluation->average_score }}</b></div>
            </td>
        </tr>
        <tr>
            <th>Tanggal Evaluasi</th>
            <td>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <th>Divisi / Cabang</th>
            <td>{{ $user->branch->name ?? 'Pusat' }} {{ isset($user->division) ? ' - ' . $user->division->name : '' }}</td>
        </tr>
    </table>

    <table style="width: 100%; border: none; margin-bottom: 8px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                <div class="score-section">
                    <h3>Detail Penilaian Kompetensi</h3>
                    <table class="score-table">
                        <thead>
                            <tr>
                                <th style="width: 70%;">Aspek / Kriteria</th>
                                <th style="width: 30%;">Nilai (0-100)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="criteria">Kecerdasan</td>
                                <td class="score">{{ $evaluation->kecerdasan_score ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="criteria">Amanah</td>
                                <td class="score">{{ $evaluation->amanah_score ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="criteria">Sosial Media</td>
                                <td class="score">{{ $evaluation->sosial_media_score ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="criteria">Kepemimpinan</td>
                                <td class="score">{{ $evaluation->kepemimpinan_score ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="criteria">Data & Ketelitian</td>
                                <td class="score">{{ $evaluation->data_ketelitian_score ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="criteria">Komunikasi</td>
                                <td class="score">{{ $evaluation->komunikasi_score ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="criteria">Kedisiplinan</td>
                                <td class="score">{{ $evaluation->kedisiplinan_score ?? '-' }}</td>
                            </tr>
                            @if($evaluation->custom_title)
                            <tr>
                                <td class="criteria">{{ $evaluation->custom_title }}</td>
                                <td class="score">{{ $evaluation->custom_score ?? '-' }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: center; padding-top: 5px;">
                @if(isset($chartImage) && $chartImage)
                    <img src="{{ $chartImage }}" style="width: 300px; height: 300px;">
                @endif
            </td>
        </tr>
    </table>

    <div class="score-section">
        <h3>Kesimpulan & Motivasi</h3>
        <div class="remark-box">
            "{{ $evaluation->final_remark ?? 'Belum ada catatan evaluasi untuk ditampilkan.' }}"
        </div>
    </div>

    <div class="score-section" style="margin-top: 15px;">
        <h3>Catatan</h3>
        <div style="border: 1px solid #cbd5e1; height: 180px; border-radius: 4px; padding: 10px; background-color: #f8fafc;">
        </div>
    </div>

</body>
</html>
