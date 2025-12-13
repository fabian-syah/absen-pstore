<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <tbody>
            @foreach($items as $item)
                <tr>
                    {{-- KOLOM BINTANG (Hanya jika Target & bukan Pencapaian) --}}
                    @if($is_target)
                    <td style="width: 50px;" class="text-center">
                        @if($item->star_level == 3)
                            <div class="badge rounded-pill star-badge-3 star-animation p-2" title="Prioritas Utama">
                                <i class="mdi mdi-star"></i> 3
                            </div>
                        @elseif($item->star_level == 2)
                            <div class="badge rounded-pill star-badge-2 p-2 text-dark" title="Prioritas Tinggi">
                                <i class="mdi mdi-star"></i> 2
                            </div>
                        @else
                            <div class="badge rounded-pill star-badge-1 p-2" title="Prioritas Standar">
                                <i class="mdi mdi-star-outline"></i> 1
                            </div>
                        @endif
                    </td>
                    @endif

                    <td>
                        <div class="fw-bold text-dark">{{ $item->title }}</div>
                        <div class="small text-muted text-wrap" style="max-width: 400px; line-height: 1.2;">
                            {{ Str::limit($item->description, 80) }}
                        </div>
                        
                        {{-- Tanggal --}}
                        <div class="d-flex align-items-center mt-1 gap-2">
                            <span class="badge bg-light text-secondary border px-2">
                                <i class="mdi mdi-calendar-range me-1"></i>
                                {{ $item->start_date->format('d/m') }} - {{ $item->deadline->format('d/m/Y') }}
                            </span>
                            @if($item->type == 'team_target' || $item->type == 'team_achievement')
                                <span class="badge bg-primary bg-opacity-10 text-primary">Cabang</span>
                            @endif
                        </div>
                    </td>

                    {{-- STATUS / TOMBOL AKSI --}}
                    <td class="text-end">
                        @if($item->status == 'completed' || !$is_target)
                            {{-- TAMPILAN STATUS INDONESIA --}}
                            @php
                                $badgeColor = 'bg-secondary';
                                if($item->outcome == 'Melampaui Ekspektasi') $badgeColor = 'bg-primary'; 
                                if($item->outcome == 'Tercapai Sempurna') $badgeColor = 'bg-success';
                                if($item->outcome == 'Tercapai Sebagian') $badgeColor = 'bg-warning text-dark';
                                if($item->outcome == 'Gagal Tercapai') $badgeColor = 'bg-danger';
                            @endphp
                            <span class="badge {{ $badgeColor }} rounded-pill px-3 py-2 fw-bold">
                                {{ $item->outcome ?? 'Selesai' }}
                            </span>
                        @else
                            {{-- TOMBOL UPDATE --}}
                            <button class="btn btn-warning btn-sm fw-bold text-white shadow-sm px-3 rounded-3" 
                                onclick="openActionModal({{ $item->id }}, '{{ addslashes($item->title) }}')">
                                <i class="mdi mdi-pencil-box-outline"></i> Update
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>