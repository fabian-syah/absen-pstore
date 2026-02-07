<!DOCTYPE html>
<html>

<head>
    <title>Data KTP User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 20px;
        }

        .page-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .page-break {
            page-break-after: always;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 20px;
            padding-bottom: 20px;
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
            font-size: 10px;
            text-align: center;
            color: #888;
            margin-top: 20px;
        }

        .no-print {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .btn-print {
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-print:hover {
            background-color: #0b5ed7;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .page-container {
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                border-bottom: none;
                margin-bottom: 0;
                height: 0;
            }

            .footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <h3>Prathama Store - Data KTP User</h3>
        <p>Silakan klik tombol di bawah untuk mencetak atau simpan sebagai PDF.</p>
        <button onclick="window.print()" class="btn-print">Cetak / Simpan PDF</button>
        <button onclick="window.history.back()"
            style="margin-left: 10px; padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">Kembali</button>
    </div>

    <div class="page-container">
        @foreach($users as $user)
            <div class="{{ !$loop->last ? 'page-break' : '' }}">
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
                            <td>: {{ $user->branch_name ?? '-' }} / {{ $user->division_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Email</td>
                            <td>: {{ $user->email }}</td>
                        </tr>
                    </table>
                </div>

                <div class="ktp-image-container">
                    <h3>Foto KTP</h3>
                    @if(isset($user->ktp_url) && $user->ktp_url)
                        <img src="{{ $user->ktp_url }}" class="ktp-image">
                    @else
                        <div style="padding: 50px; border: 1px dashed #ccc; color: #888;">
                            Foto KTP tidak tersedia / gagal dimuat.
                        </div>
                    @endif
                </div>

                <div class="footer">
                    Dokumen ini digenerate otomatis pada {{ date('d F Y H:i') }}
                </div>
            </div>
        @endforeach
    </div>

    <script>
        // Optional: Auto print on load
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>