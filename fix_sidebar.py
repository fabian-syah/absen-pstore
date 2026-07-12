import re

file_path = r'd:\bian\pstore\absensi-pstore\resources\views\layout\sidebar.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

replacements = {
    'href="{{ route(\'attendance.history\') }}"': 'class="nav-link {{ request()->routeIs(\'self.attend.history\') ? \'active\' : \'\' }}" href="{{ route(\'attendance.history\') }}"',
    'href="{{ route(\'leave-requests.personal-history\') }}"': 'class="nav-link {{ request()->routeIs(\'leave-requests.personal-history\') ? \'active\' : \'\' }}" href="{{ route(\'leave-requests.personal-history\') }}"',
    'href="{{ route(\'leave-requests.cuti-history\') }}"': 'class="nav-link {{ request()->routeIs(\'leave-requests.cuti-history\') ? \'active\' : \'\' }}" href="{{ route(\'leave-requests.cuti-history\') }}"',
    'href="{{ route(\'employment-history.index\') }}"': 'class="nav-link {{ request()->routeIs(\'employment-history.*\') ? \'active\' : \'\' }}" href="{{ route(\'employment-history.index\') }}"',
    'href="{{ route(\'violations.index\') }}"': 'class="nav-link {{ request()->routeIs(\'violations.*\') ? \'active\' : \'\' }}" href="{{ route(\'violations.index\') }}"',
    'href="{{ route(\'security.history\') }}"': 'class="nav-link {{ request()->routeIs(\'security.history\') ? \'active\' : \'\' }}" href="{{ route(\'security.history\') }}"',
    'href="{{ route(\'employee-evaluations.history\') }}"': 'class="nav-link {{ request()->routeIs(\'employee-evaluations.history\') ? \'active\' : \'\' }}" href="{{ route(\'employee-evaluations.history\') }}"'
}

for old_str, new_str in replacements.items():
    content = content.replace('class="nav-link" ' + old_str, new_str)
    content = content.replace('class="nav-link"\n                   ' + old_str, new_str)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print('Done sidebar.blade.php')

master_path = r'd:\bian\pstore\absensi-pstore\resources\views\layout\master.blade.php'
try:
    with open(master_path, 'r', encoding='utf-8') as f:
        master_content = f.read()
    master_content = master_content.replace('assets/js/template.js', 'assets/js/template.js?v={{ time() }}')
    with open(master_path, 'w', encoding='utf-8') as f:
        f.write(master_content)
    print('Done master.blade.php')
except Exception as e:
    print(e)
