<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <tbody>
            @foreach($items as $item)
                <tr class="filterable-item" 
                    data-date="{{ $item->deadline->format('Y-m-d') }}" 
                    data-month="{{ $item->deadline->format('Y-m') }}" 
                    data-year="{{ $item->deadline->format('Y') }}">
                    
                    {{-- 1. ICON / LEVEL --}}
                    <td style="width: 60px; min-width: 60px;" class="text-center">
                        @if(Str::contains($item->type, 'achievement'))
                            <div class="bg-warning bg-opacity-25 text-warning rounded-circle p-2 mx-auto" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                <i class="mdi mdi-trophy mdi-18px"></i>
                            </div>
                        @else
                            @if($item->star_level == 3)
                                <div class="badge rounded-pill star-badge-3 star-animation p-2 w-100">Lvl 3</div>
                            @elseif($item->star_level == 2)
                                <div class="badge rounded-pill star-badge-2 p-2 w-100 text-dark">Lvl 2</div>
                            @else
                                <div class="badge rounded-pill star-badge-1 p-2 w-100">Lvl 1</div>
                            @endif
                        @endif
                    </td>

                    {{-- 2. KONTEN (TITLE & DESC) --}}
                    <td>
                        <div class="d-flex align-items-center mb-1 flex-wrap gap-2">
                            <span class="fw-bold text-dark">{{ $item->title }}</span>
                            @if(Str::contains($item->type, 'achievement'))
                                <span class="badge bg-warning text-dark" style="font-size: 9px;">PRESTASI</span>
                            @endif
                        </div>
                        <div class="small text-muted text-wrap mb-2" style="max-width: 500px; line-height: 1.3;">
                            {{ Str::limit($item->description, 90) }}
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.75rem;">
                            @if($item->user)
                                <div class="d-flex align-items-center text-secondary bg-light px-2 py-1 rounded-pill border">
                                    <i class="mdi mdi-account-circle me-1"></i> {{ $item->user->name }}
                                </div>
                            @endif
                            <div class="text-secondary bg-light px-2 py-1 rounded-pill border">
                                <i class="mdi mdi-calendar-clock me-1"></i> {{ $item->deadline->format('d M Y') }}
                            </div>
                        </div>
                    </td>

                    {{-- 3. STATUS / AKSI --}}
                    <td class="text-end" style="min-width: 140px;">
                        @if($item->status == 'completed' || Str::contains($item->type, 'achievement'))
                            @php
                                $c = 'bg-secondary';
                                if($item->outcome == 'Melampaui Ekspektasi') $c = 'bg-primary'; 
                                if($item->outcome == 'Tercapai Sempurna') $c = 'bg-success';
                                if($item->outcome == 'Tercapai Sebagian') $c = 'bg-warning text-dark';
                                if($item->outcome == 'Gagal Tercapai') $c = 'bg-danger';
                            @endphp
                            <span class="badge {{ $c }} rounded-pill px-3 py-2 fw-bold shadow-sm d-inline-block">
                                {{ $item->outcome ?? 'Selesai' }}
                            </span>
                        @else
                            {{-- LOGIKA BARU PEMISAHAN TOMBOL --}}
                            <div class="d-flex justify-content-end gap-1">
                                
                                {{-- A. TOMBOL EDIT DETAIL (Hanya muncul jika allow_edit_detail TRUE) --}}
                                @if(isset($allow_edit_detail) && $allow_edit_detail)
                                    <a href="{{ route('job-targets.edit', $item->id) }}" class="btn btn-light btn-sm border" title="Edit Data">
                                        <i class="mdi mdi-pencil text-muted"></i>
                                    </a>
                                @endif

                                {{-- B. TOMBOL UPDATE STATUS (Hanya muncul jika allow_update_status TRUE) --}}
                                @if(isset($allow_update_status) && $allow_update_status)
                                    <button class="btn btn-warning btn-sm fw-bold text-white shadow-sm px-3 rounded-3" 
                                        onclick="openActionModal({{ $item->id }}, '{{ addslashes($item->title) }}')">
                                        Update Hasil
                                    </button>
                                @endif

                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            
            {{-- Bagian No Data Message --}}
            <tr class="no-data-message d-none">
                <td colspan="3" class="text-center py-4 text-muted small">
                    <i class="mdi mdi-magnify-remove mdi-24px d-block mb-1"></i>
                    Tidak ada data pada tanggal tersebut.
                </td>
            </tr>
        </tbody>
    </table>
</div>