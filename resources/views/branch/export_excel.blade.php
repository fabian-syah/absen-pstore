<table>
    <thead>
        <tr>
            <th colspan="8" style="font-weight: bold; font-size: 14px; text-align: center;">LAPORAN ABSENSI CABANG:
                {{ strtoupper($branch->name) }}</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center;">Periode: {{ $month }}</th>
        </tr>
        <tr></tr> {{-- Empty Row --}}
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; background-color: #fca5a5;">No</th>
            <th style="font-weight: bold; border: 1px solid #000; background-color: #fca5a5;">Nama Karyawan</th>
            <th style="font-weight: bold; border: 1px solid #000; background-color: #fca5a5;">Jabatan</th>
            <th style="font-weight: bold; border: 1px solid #000; background-color: #bef264;">Hadir</th>
            <th style="font-weight: bold; border: 1px solid #000; background-color: #bef264;">Sakit</th>
            <th style="font-weight: bold; border: 1px solid #000; background-color: #bef264;">Izin</th>
            <th style="font-weight: bold; border: 1px solid #000; background-color: #bef264;">Alfa</th>
            <th style="font-weight: bold; border: 1px solid #000; background-color: #bef264;">Telat</th>
            <th style="font-weight: bold; border: 1px solid #000; background-color: #bef264;">Total Jam</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $row)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000;">{{ $row['user']->name }}</td>
                <td style="border: 1px solid #000;">{{ $row['user']->job_title ?? 'Staff' }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $row['summary']['hadir'] }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $row['summary']['sakit'] }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $row['summary']['izin'] }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $row['summary']['alfa'] }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $row['summary']['telat'] }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ number_format($row['summary']['total_jam'], 1) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>