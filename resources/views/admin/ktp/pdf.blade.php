<!DOCTYPE html>
<html>

<head>
    <title>Data KTP User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .page-break {
            page-break-after: always;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .user-info {
            margin-bottom: 20px;
        }

        .user-info table {
            width: 100%;
        }

        .user-info td {
            padding: 5px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 150px;
        }

        .ktp-image-container {
            text-align: center;
            margin-top: 20px;
        }

        .ktp-image {
            max-width: 100%;
            max-height: 500px;
            border: 1px solid #ddd;
            padding: 5px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 10px;
            text-align: center;
            color: #888;
        }
    </style>
</head>

<body>
    @foreach($users as $user)
        <div class="header">
            <h2>DATA KTP USER</h2>
        </div>

        <div class="user-info">
            <table>
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td>: {{ $user->name }}</td>
                </tr>
                <tr>
                    <td class="label">NIK / ID Karyawan</td>
                    <td>: {{ $user->employee_id ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Jabatan</td>
                    <td>: {{ $user->position ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Cabang / Divisi</td>
                    <td>: {{ $user->branch->name ?? '-' }} / {{ $user->division->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td>: {{ $user->email }}</td>
                </tr>
            </table>
        </div>

        <div class="ktp-image-container">
            <h3>Foto KTP</h3>
            @if($user->ktp_photo_path)
                {{--
                DomPDF butuh absolute path untuk gambar lokal.
                storage_path('app/public/') digunakan jika file ada di storage public.
                Tapi karena user upload via storage link, path di DB mungkin 'ktp_photos/filename.jpg'.
                Kita perlu cek path-nya.
                --}}
                @php
                    $path = $user->ktp_photo_path;
                    // Fix path if it starts with 'public/' or just filename
                    // Asumsi: file disimpan di storage/app/public/...
                    // Jika path di DB 'ktp_photos/xyz.jpg', full path adalah storage_path('app/public/ktp_photos/xyz.jpg')

                    // Cek apakah file ada
                    $fullPath = storage_path('app/public/' . $path);
                    if (!file_exists($fullPath)) {
                        // Coba cek tanpa 'public/' di path DB jika sudah termasuk
                        $fullPath = storage_path('app/' . $path);
                    }
                @endphp

                @if(file_exists($fullPath))
                    <img src="{{ $fullPath }}" class="ktp-image">
                @else
                    <p style="color: red;">File Foto Tidak Ditemukan di Server (Path: {{ $path }})</p>
                @endif
            @else
                <p>Belum upload KTP</p>
            @endif
        </div>

        <div class="footer">
            Dokumen ini digenerate otomatis pada {{ date('d F Y H:i') }}
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>

</html>