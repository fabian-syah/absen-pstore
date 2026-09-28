<div class="target-items-wrapper">
    @foreach($items as $item)
        <div class="target-item-card filterable-item" 
             data-date="{{ $item->deadline->format('Y-m-d') }}" 
             data-month="{{ $item->deadline->format('Y-m') }}" 
             data-year="{{ $item->deadline->format('Y') }}">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-start gap-3 flex-grow-1">
                    {{-- Priority / Level Badge --}}
                    <div class="flex-shrink-0 mt-1">
                        @if(Str::contains($item->type, 'achievement'))
                            <span class="priority-badge-achievement">Prestasi</span>
                        @elseif($item->star_level == 3)
                            <span class="priority-badge-3">Prioritas Tinggi</span>
                        @elseif($item->star_level == 2)
                            <span class="priority-badge-2">Prioritas Sedang</span>
                        @else
                            <span class="priority-badge-1">Prioritas Normal</span>
                        @endif
                    </div>

                    {{-- Target Details --}}
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h6 class="fw-bold text-dark mb-0">{{ $item->title }}</h6>
                        </div>
                        @if($item->description)
                            <p class="text-muted small mb-2 text-break" style="line-height: 1.45;">
                                {{ Str::limit($item->description, 160) }}
                            </p>
                        @endif
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if($item->user)
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small fw-normal d-inline-flex align-items-center gap-1">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span>{{ $item->user->name }}</span>
                                </span>
                            @endif
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small fw-normal d-inline-flex align-items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>Tenggat: {{ $item->deadline->format('d M Y') }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons / Outcome Status --}}
                <div class="d-flex align-items-center justify-content-end gap-2 w-100 w-md-auto flex-shrink-0 pt-2 pt-md-0 border-top border-md-0">
                    @if($item->status == 'completed' || Str::contains($item->type, 'achievement'))
                        @php
                            $badgeClass = 'bg-secondary text-white';
                            if($item->outcome == 'Melampaui Ekspektasi') $badgeClass = 'bg-primary text-white'; 
                            if($item->outcome == 'Tercapai Sempurna') $badgeClass = 'bg-success text-white';
                            if($item->outcome == 'Tercapai Sebagian') $badgeClass = 'bg-warning text-dark';
                            if($item->outcome == 'Gagal Tercapai') $badgeClass = 'bg-danger text-white';
                        @endphp
                        <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill fw-semibold small shadow-none">
                            {{ $item->outcome ?? 'Selesai' }}
                        </span>
                    @else
                        <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-end">
                            @if(isset($allow_edit_detail) && $allow_edit_detail)
                                <a href="{{ route('job-targets.edit', $item->id) }}" class="btn btn-light btn-sm border text-muted px-2 py-1 rounded-2" title="Edit Data">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                            @endif
                            @if(isset($allow_update_status) && $allow_update_status)
                                <button class="btn btn-warning btn-sm text-dark fw-bold px-3 py-1 rounded-2 flex-fill flex-md-grow-0" onclick="openActionModal({{ $item->id }}, '{{ addslashes($item->title) }}')">
                                    Update Hasil
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>