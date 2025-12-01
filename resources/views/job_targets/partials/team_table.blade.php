<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="card-title">Target Tim ({{ $title }})</h5>
    
    {{-- Tombol Tambah (Hanya Leader/Admin) --}}
    @if(in_array(Auth::user()->role, ['admin', 'leader', 'audit']))
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTeamTargetModal" 
            onclick="setModalPeriod('{{ $period }}')">
            <i class="mdi mdi-plus"></i> Tambah Target {{ $title }}
        </button>
    @endif
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Judul Pekerjaan</th>
                <th>Ditugaskan Ke</th>
                <th>Deadline</th>
                <th>Status</th>
                @if(in_array(Auth::user()->role, ['admin', 'leader', 'audit']))
                    <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($targets as $target)
                <tr>
                    <td>
                        <div class="{{ $target->status == 'completed' ? 'text-decoration-line-through text-muted' : 'fw-bold' }}">
                            {{ $target->title }}
                        </div>
                        @if($target->description)
                            <small class="text-muted d-block">{{ Str::limit($target->description, 30) }}</small>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle text-white d-flex justify-content-center align-items-center me-2" 
                                style="width: 30px; height: 30px; font-size: 12px;">
                                {{ substr($target->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="small fw-bold">{{ $target->user->name }}</div>
                                <div class="text-muted text-xs">{{ $target->user->division->name ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="{{ $target->deadline < now() && $target->status != 'completed' ? 'text-danger fw-bold' : '' }}">
                            {{ $target->deadline->format('d M Y') }}
                        </div>
                    </td>
                    <td>
                        @if($target->status == 'completed')
                            <span class="badge badge-success"><i class="mdi mdi-check"></i> Selesai</span>
                        @else
                            <span class="badge badge-warning">Proses</span>
                        @endif
                    </td>
                    
                    {{-- Aksi Leader/Admin --}}
                    @if(in_array(Auth::user()->role, ['admin', 'leader', 'audit']))
                        <td>
                            {{-- Toggle Status --}}
                            <form action="{{ route('job-targets.toggle', $target->id) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-icon {{ $target->status == 'completed' ? 'btn-inverse-warning' : 'btn-inverse-success' }}" 
                                    title="{{ $target->status == 'completed' ? 'Batal Selesai' : 'Tandai Selesai' }}">
                                    <i class="mdi {{ $target->status == 'completed' ? 'mdi-undo' : 'mdi-check' }}"></i>
                                </button>
                            </form>

                            {{-- Hapus --}}
                            <form action="{{ route('job-targets.destroy', $target->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus target tim ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-inverse-danger btn-sm btn-icon"><i class="mdi mdi-delete"></i></button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Belum ada target tim {{ strtolower($title) }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>