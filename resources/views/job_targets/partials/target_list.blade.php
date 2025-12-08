{{-- resources/views/job_targets/partials/target_list.blade.php --}}
@if($targets->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Periode</th>
                    <th>PIC</th>
                    <th>Status / Hasil</th>
                    @if($allow_action) <th>Aksi</th> @endif
                </tr>
            </thead>
            <tbody>
                @foreach($targets as $target)
                    <tr>
                        <td>
                            <span class="fw-bold d-block">{{ $target->title }}</span>
                            <small class="text-muted">{{ Str::limit($target->description, 50) }}</small>
                        </td>
                        <td>
                            <small>{{ $target->start_date->format('d M Y') }} <br> s/d <br> {{ $target->deadline->format('d M Y') }}</small>
                        </td>
                        <td>
                            @if($target->user)
                                <div class="d-flex align-items-center">
                                    {{-- Placeholder Avatar --}}
                                    <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width:30px;height:30px;font-size:12px;">
                                        {{ substr($target->user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 12px">{{ $target->user->name }}</div>
                                        <div class="text-muted" style="font-size: 10px">{{ $target->user->division->name ?? '' }}</div>
                                    </div>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($target->status == 'pending')
                                <span class="badge bg-warning text-dark">On Going</span>
                            @else
                                {{-- Tampilkan Badge berdasarkan Outcome --}}
                                @php
                                    $badgeClass = 'bg-secondary';
                                    $outcomeText = $target->outcome;
                                    if($target->outcome == 'exceeded') $badgeClass = 'bg-primary'; // Biru
                                    if($target->outcome == 'achieved') $badgeClass = 'bg-success'; // Hijau
                                    if($target->outcome == 'partial') $badgeClass = 'bg-warning text-dark'; // Kuning
                                    if($target->outcome == 'failed') $badgeClass = 'bg-danger'; // Merah
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($outcomeText) }}</span>
                                
                                {{-- Tombol Lihat Bukti jika ada --}}
                                @if($target->evidence_photo)
                                    <a href="{{ asset('storage/'.$target->evidence_photo) }}" target="_blank" class="ms-1 text-primary"><i class="mdi mdi-image"></i></a>
                                @endif
                            @endif
                        </td>
                        @if($allow_action)
                            <td>
                                {{-- Cek Hak Akses Tombol Aksi --}}
                                @php
                                    $canEdit = false;
                                    if(auth()->user()->id == $target->user_id) $canEdit = true; // Milik sendiri
                                    if(in_array(auth()->user()->role, ['admin', 'leader']) && $target->type == 'team') $canEdit = true; // Atasan edit tim
                                @endphp

                                @if($canEdit && $target->status != 'completed')
                                    <button class="btn btn-outline-primary btn-sm py-1 px-2" onclick="openActionModal({{ $target->id }}, '{{ addslashes($target->title) }}')">
                                        <i class="mdi mdi-pencil"></i> Update
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-light text-center border-0">
        <i class="mdi mdi-folder-open-outline mdi-24px d-block mb-2"></i>
        Data tidak ditemukan untuk kategori ini.
    </div>
@endif