<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <tbody>
            @foreach($items as $item)
                <tr>
                    {{-- BINTANG / ICON TIPE --}}
                    <td style="width: 60px;" class="text-center">
                        @if(Str::contains($item->type, 'achievement'))
                            {{-- Icon Piala jika Tipe Achievement Murni --}}
                            <div class="bg-warning bg-opacity-25 text-warning rounded-circle p-2 mx-auto" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                <i class="mdi mdi-trophy mdi-18px"></i>
                            </div>
                        @else
                            {{-- Icon Bintang untuk Target Biasa --}}
                            @if($item->star_level == 3)
                                <div class="badge rounded-pill star-badge-3 star-animation p-2 w-100">Lvl 3</div>
                            @elseif($item->star_level == 2)
                                <div class="badge rounded-pill star-badge-2 p-2 w-100 text-dark">Lvl 2</div>
                            @else
                                <div class="badge rounded-pill star-badge-1 p-2 w-100">Lvl 1</div>
                            @endif
                        @endif
                    </td>

                    {{-- DETAIL ITEM --}}
                    <td>
                        <div class="d-flex align-items-center mb-1">
                            <span class="fw-bold text-dark me-2">{{ $item->title }}</span>
                            @if(Str::contains($item->type, 'achievement'))
                                <span class="badge bg-warning text-dark" style="font-size: 10px;">PRESTASI</span>
                            @endif
                        </div>
                        
                        <div class="small text-muted text-wrap" style="max-width: 450px; line-height: 1.3;">
                            {{ Str::limit($item->description, 90) }}
                        </div>
                        
                        <div class="d-flex align-items-center mt-2 gap-2" style="font-size: 0.75rem;">
                            {{-- Avatar User --}}
                            @if($item->user)
                                <div class="d-flex align-items-center text-secondary bg-light px-2 py-1 rounded-pill">
                                    <i class="mdi mdi-account-circle me-1"></i> {{ $item->user->name }}
                                </div>
                            @endif

                            {{-- Tanggal --}}
                            <div class="text-secondary bg-light px-2 py-1 rounded-pill border">
                                <i class="mdi mdi-calendar-clock me-1"></i>
                                @if($item->period == 'daily')
                                    {{ $item->deadline->format('d M Y') }}
                                @elseif($item->period == 'monthly')
                                    {{ $item->deadline->format('M Y') }}
                                @else
                                    {{ $item->deadline->format('Y') }}
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- STATUS / ACTION --}}
                    <td class="text-end">
                        @if($item->status == 'completed' || Str::contains($item->type, 'achievement'))
                            {{-- HASIL AKHIR (Indonesia) --}}
                            @php
                                $badgeColor = 'bg-secondary';
                                if($item->outcome == 'Melampaui Ekspektasi') $badgeColor = 'bg-primary'; 
                                if($item->outcome == 'Tercapai Sempurna') $badgeColor = 'bg-success';
                                if($item->outcome == 'Tercapai Sebagian') $badgeColor = 'bg-warning text-dark';
                                if($item->outcome == 'Gagal Tercapai') $badgeColor = 'bg-danger';
                            @endphp
                            <span class="badge {{ $badgeColor }} rounded-pill px-3 py-2 fw-bold shadow-sm">
                                {{ $item->outcome ?? 'Selesai' }}
                            </span>
                        @else
                            {{-- TOMBOL UPDATE (Hanya untuk On Going) --}}
                            @if($allow_action)
                                <button class="btn btn-warning btn-sm fw-bold text-white shadow-sm px-3 rounded-3 py-2" 
                                    onclick="openActionModal({{ $item->id }}, '{{ addslashes($item->title) }}')">
                                    <i class="mdi mdi-pencil-box-outline me-1"></i> Update
                                </button>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>