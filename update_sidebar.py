import re
import sys

file_path = r'd:\bian\pstore\absensi-pstore\resources\views\layout\sidebar.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Unified Riwayat Menu HTML
riwayat_menu = '''        {{-- =================================== --}}
        {{-- MENU RIWAYAT (GABUNGAN) --}}
        {{-- =================================== --}}
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" data-bs-toggle="collapse" href="#ui-riwayat" aria-expanded="false" aria-controls="ui-riwayat">
                <i class="menu-icon mdi mdi-history"></i>
                <span class="menu-title">Riwayat Lengkap</span>
                <i class="menu-arrow mdi mdi-chevron-down ms-auto" style="font-size: 1.2rem; transition: transform 0.3s;"></i>
            </a>
            <div class="collapse" id="ui-riwayat">
                <ul class="nav flex-column sub-menu" style="list-style-type: none; padding-left: 2rem; margin-top: 0; background: rgba(0,0,0,0.02); border-radius: 0 0 12px 12px;">
                    @if (auth()->user()->role != 'admin_gaji')
                        <li class="nav-item" style="margin: 0.1rem 0;"> <a class="nav-link" href="{{ route('attendance.history') }}" style="padding: 0.5rem 1rem !important; font-size: 0.8rem !important;"><i class="mdi mdi-circle-small"></i> Riwayat Absensi</a></li>
                        <li class="nav-item" style="margin: 0.1rem 0;"> <a class="nav-link" href="{{ route('leave-requests.personal-history') }}" style="padding: 0.5rem 1rem !important; font-size: 0.8rem !important;"><i class="mdi mdi-circle-small"></i> Riwayat Izin/Sakit</a></li>
                        <li class="nav-item" style="margin: 0.1rem 0;"> <a class="nav-link" href="{{ route('leave-requests.cuti-history') }}" style="padding: 0.5rem 1rem !important; font-size: 0.8rem !important;"><i class="mdi mdi-circle-small"></i> Riwayat Cuti</a></li>
                        <li class="nav-item" style="margin: 0.1rem 0;"> <a class="nav-link" href="{{ route('employment-history.index') }}" style="padding: 0.5rem 1rem !important; font-size: 0.8rem !important;"><i class="mdi mdi-circle-small"></i> Riwayat Divisi/Cabang</a></li>
                    @endif
                    <li class="nav-item" style="margin: 0.1rem 0;"> <a class="nav-link" href="{{ route('violations.index') }}" style="padding: 0.5rem 1rem !important; font-size: 0.8rem !important;"><i class="mdi mdi-circle-small"></i> Riwayat Pelanggaran</a></li>
                    @if (auth()->user()->role == 'security' || auth()->user()->role == 'admin')
                        <li class="nav-item" style="margin: 0.1rem 0;"> <a class="nav-link" href="{{ route('security.history') }}" style="padding: 0.5rem 1rem !important; font-size: 0.8rem !important;"><i class="mdi mdi-circle-small"></i> Riwayat Scan</a></li>
                    @endif
                    @if (in_array(auth()->user()->role, ['audit', 'leader', 'admin']))
                        <li class="nav-item" style="margin: 0.1rem 0;"> <a class="nav-link" href="{{ route('employee-evaluations.history') }}" style="padding: 0.5rem 1rem !important; font-size: 0.8rem !important;"><i class="mdi mdi-circle-small"></i> Riwayat Evaluasi (Tim)</a></li>
                    @endif
                </ul>
            </div>
        </li>

        {{-- RINGKASAN TAHUNAN --}}
        @if (auth()->user()->role != 'admin_gaji')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('attendance.summary') }}">
                    <i class="mdi mdi-text-box-multiple-outline menu-icon"></i>
                    <span class="menu-title">Ringkasan Tahunan</span>
                </a>
            </li>
        @endif'''

# 1. Remove initial riwayat absensi block
content = re.sub(
    r'<li class=\"nav-item\">\s*<a class=\"nav-link\" href=\"\{\{ route\(\'attendance\.history\'\) \}\}\">.*?<\/li>.*?<li class=\"nav-item\">\s*<a class=\"nav-link\" href=\"\{\{ route\(\'leave-requests\.cuti-history\'\) \}\}\">.*?<\/li>',
    '',
    content,
    flags=re.DOTALL
)

# 2. Remove riwayat divisi and pelanggaran in Menu Umum
content = re.sub(
    r'<li class=\"nav-item\">\s*<a class=\"nav-link\" href=\"\{\{ route\(\'employment-history\.index\'\) \}\}\">.*?<\/li>',
    '',
    content,
    flags=re.DOTALL
)
content = re.sub(
    r'<li class=\"nav-item\">\s*<a class=\"nav-link\" href=\"\{\{ route\(\'violations\.index\'\) \}\}\">.*?<\/li>',
    '',
    content,
    flags=re.DOTALL
)

# 3. Remove KHUSUS ADMIN GAJI block for pelanggaran
content = re.sub(
    r'\{\{-- RIWAYAT PELANGGARAN \(KHUSUS ADMIN GAJI\) --\}\}\s*@if \(auth\(\)->user\(\)->role == \'admin_gaji\'\)\s*<li class=\"nav-item\">\s*<a class=\"nav-link\" href=\"\{\{ route\(\'violations\.index\'\) \}\}\">.*?<\/li>\s*@endif',
    '',
    content,
    flags=re.DOTALL
)

# 4. Remove riwayat scan & izin sakit in security
content = re.sub(
    r'<li class=\"nav-item\">\s*<a class=\"nav-link\" href=\"\{\{ route\(\'security\.history\'\) \}\}\">.*?<\/li>',
    '',
    content,
    flags=re.DOTALL
)
content = re.sub(
    r'\{\{-- 7\. Riwayat Izin \(Excluding Cuti\) --\}\}\s*<li class=\"nav-item\">\s*<a class=\"nav-link\" href=\"\{\{ route\(\'leave-requests\.personal-history\'\) \}\}\">.*?<\/li>',
    '',
    content,
    flags=re.DOTALL
)

# 5. Remove riwayat evaluasi
content = re.sub(
    r'<li class=\"nav-item\">\s*<a class=\"nav-link\" href=\"\{\{ route\(\'employee-evaluations\.history\'\) \}\}\">.*?<\/li>',
    '',
    content,
    flags=re.DOTALL
)

# Finally, insert the new unified menu right after DZIKIR ONLINE
old_marker = r'''        {{-- =================================== --}}
        {{-- RIWAYAT ABSENSI (EXCEPT GAJI) --}}
        {{-- =================================== --}}'''

content = content.replace(old_marker, riwayat_menu)


with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print('Done!')
