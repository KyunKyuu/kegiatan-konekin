@extends('layouts.layout')

@section('title', 'Monitoring Capstone Objective - Jadwal Kegiatan Konekin')

@section('content')
<div class="monitoring-container full-page-monitoring">

    <!-- Page Header & Action Bar -->
    <div class="monitoring-header glass-panel">
        <div class="monitoring-header-main">
            <div>
                <h1 class="monitoring-title">
                    <i class="fa-solid fa-ruler-combined text-primary-glow"></i> Monitoring Capstone Objective
                </h1>
                <p class="monitoring-subtitle text-muted">
                    Visualisasi ruler timeline permanen se-halaman untuk Capstone Utama & Sub Capstone
                </p>
            </div>
            <div class="monitoring-header-actions">
                <button class="btn btn-outline" onclick="openTeamDrawerModal()">
                    <i class="fa-solid fa-users"></i> Daftar Tim / Orang
                </button>
                @auth
                    @if(Auth::user()->is_admin)
                        <button class="btn btn-outline" onclick="openMasterTargetsModal()">
                            <i class="fa-solid fa-list-check"></i> Master Target
                        </button>
                        <button class="btn btn-primary" onclick="openCreateModal(null)">
                            <i class="fa-solid fa-plus"></i> Capstone Utama
                        </button>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- =========================================================================
         FULL-PAGE HERO RULER TIMELINE ("ALA ALA PENGGARIS")
         ========================================================================= -->
    <div class="ruler-wrapper glass-panel ruler-hero-panel">
        <div class="ruler-header">
            <div class="ruler-title">
                <i class="fa-solid fa-sliders"></i> Full-Page Timeline Ruler
            </div>
            <div class="ruler-legend">
                <span class="legend-item"><span class="legend-tick major"></span> Capstone Utama (Major Tick)</span>
                <span class="legend-item"><span class="legend-tick minor"></span> Sub Capstone (Minor Tick)</span>
                <span class="legend-item"><span class="legend-avatar"></span> Penanggung Jawab ("Bulet-bulet")</span>
            </div>
        </div>

        <div class="ruler-track-container" id="rulerTrackContainer">
            @if($mainMilestones->isEmpty())
                <div class="ruler-empty-state">
                    <i class="fa-solid fa-ruler-horizontal empty-icon"></i>
                    <p>Belum ada Capstone Objective yang ditambahkan.</p>
                    @auth
                        @if(Auth::user()->is_admin)
                            <button class="btn btn-primary btn-sm" onclick="openCreateModal(null)">
                                <i class="fa-solid fa-plus"></i> Tambah Capstone Utama Pertama
                            </button>
                        @endif
                    @endauth
                </div>
            @else
                <div class="ruler-track">
                    <!-- Continuous Horizontal Baseline Ruler Line -->
                    <div class="ruler-main-line"></div>

                    <!-- Render Main Capstones & Sub Capstones along the full-width ruler -->
                    <div class="ruler-milestones-flex">
                        @foreach($mainMilestones as $mainIndex => $main)
                            <div class="ruler-segment {{ $main->color }}" data-id="{{ $main->id }}">
                                
                                <!-- MAJOR TICK (CAPSTONE UTAMA) -->
                                <div class="ruler-tick-item major-tick" onclick="showMilestoneDetail({{ json_encode($main) }})">
                                    <div class="ruler-tick-mark major"></div>
                                    <div class="ruler-tick-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="ruler-capstone-num">#{{ $mainIndex + 1 }}</span>
                                            @auth
                                                @if(Auth::user()->is_admin)
                                                    <span class="btn-icon btn-xs" onclick="event.stopPropagation(); openEditModal({{ json_encode($main) }})" title="Edit Capstone">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </span>
                                                @endif
                                            @endauth
                                        </div>
                                        <span class="ruler-capstone-title">{{ $main->title }}</span>
                                        @if($main->description)
                                            <span class="ruler-capstone-desc text-muted text-xs">{{ Str::limit($main->description, 45) }}</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Avatar circles under Major Tick ("bulet-bulet") -->
                                    <div class="ruler-avatar-group">
                                        @foreach($main->people->take(5) as $person)
                                            <span class="person-circle-avatar" 
                                                  title="Klik untuk detail {{ $person->name }}" 
                                                  onclick="event.stopPropagation(); openPersonDetailModal({{ $person->id }})">
                                                {{ strtoupper(substr($person->name, 0, 2)) }}
                                            </span>
                                        @endforeach
                                        @if($main->people->count() > 5)
                                            <span class="person-circle-avatar more-avatar" title="{{ $main->people->count() - 5 }} orang lainnya" onclick="event.stopPropagation(); showMilestoneDetail({{ json_encode($main) }})">
                                                +{{ $main->people->count() - 5 }}
                                            </span>
                                        @endif
                                        @if($main->people->isEmpty())
                                            <span class="no-person-indicator text-muted text-xs" title="Belum ada penanggung jawab">-</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- SUB CAPSTONES (MINOR TICKS) -->
                                <div class="ruler-sub-ticks-container">
                                    @foreach($main->subMilestones as $subIndex => $sub)
                                        <div class="ruler-tick-item minor-tick" onclick="showMilestoneDetail({{ json_encode($sub) }})">
                                            <div class="ruler-tick-mark minor"></div>
                                            <div class="ruler-sub-header">
                                                <span class="ruler-sub-num">{{ $mainIndex + 1 }}.{{ $subIndex + 1 }}</span>
                                                <span class="ruler-sub-title" title="{{ $sub->title }}">{{ $sub->title }}</span>
                                            </div>

                                            <!-- Avatar circles under Minor Tick ("bulet-bulet") -->
                                            <div class="ruler-avatar-group minor-group">
                                                @foreach($sub->people->take(4) as $subPerson)
                                                    <span class="person-circle-avatar mini-avatar" 
                                                          title="Klik untuk detail {{ $subPerson->name }}" 
                                                          onclick="event.stopPropagation(); openPersonDetailModal({{ $subPerson->id }})">
                                                        {{ strtoupper(substr($subPerson->name, 0, 2)) }}
                                                    </span>
                                                @endforeach
                                                @if($sub->people->count() > 4)
                                                    <span class="person-circle-avatar mini-avatar more-avatar" title="{{ $sub->people->count() - 4 }} orang lainnya">
                                                        +{{ $sub->people->count() - 4 }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    @auth
                                        @if(Auth::user()->is_admin)
                                            <button class="btn-add-sub-tick" onclick="openCreateModal({{ $main->id }})" title="Tambah Sub Capstone">
                                                <i class="fa-solid fa-plus"></i> Sub
                                            </button>
                                        @endif
                                    @endauth
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- =========================================================================
     MODAL DETAIL PROFIL ORANG (TARGET CHECKLIST, SKALA HISTORY, MULTI NOTES)
     ========================================================================= -->
<div class="modal-backdrop" id="personDetailModalBackdrop" style="display: none;">
    <div class="modal-content glass-panel modal-lg">
        <div class="modal-header">
            <div class="person-modal-header-title">
                <span class="person-modal-avatar" id="modalPersonAvatar">--</span>
                <div>
                    <h2 id="modalPersonName" class="person-modal-name">Nama Orang</h2>
                    <span class="person-modal-sub text-muted"><i class="fa-solid fa-id-badge"></i> Detail Profil & Histori Target</span>
                </div>
            </div>
            <button class="btn-close" onclick="closePersonDetailModal()">&times;</button>
        </div>

        <div class="person-profile-grid">
            
            <!-- KOLOM KIRI (Target Checklist, Assign Target, & Multi-Catatan) -->
            <div class="profile-col-left">
                
                <!-- Section 1: Target Checklist & Assign Master Target -->
                <div class="profile-section-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="profile-section-title mb-0"><i class="fa-solid fa-bullseye text-primary-glow"></i> Target Checklist</h4>
                    </div>
                    
                    <div id="personTargetsDisplay" class="target-items-list">
                        <!-- Target checkboxes populated via JS -->
                    </div>

                    @auth
                        @if(Auth::user()->is_admin)
                            <!-- Assign Target Form -->
                            <div class="assign-target-box mt-3">
                                <label class="text-xs font-semibold text-muted mb-1"><i class="fa-solid fa-plus-circle"></i> Assign Target Baru:</label>
                                <form id="assignTargetForm" method="POST" action="">
                                    @csrf
                                    <div class="d-flex gap-2">
                                        <select name="master_target_id" id="assignMasterSelect" class="form-control text-xs" onchange="toggleCustomTargetInput(this)">
                                            <option value="">-- Pilih dari Master Target --</option>
                                            @foreach($masterTargets as $mt)
                                                <option value="{{ $mt->id }}">{{ $mt->title }}</option>
                                            @endforeach
                                            <option value="custom">+ Ketik Target Kustom...</option>
                                        </select>
                                    </div>
                                    <div id="customTargetTitleWrapper" class="mt-2" style="display: none;">
                                        <input type="text" name="custom_title" id="customTargetTitle" class="form-control text-xs" placeholder="Nama target kustom baru...">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-xs mt-2 w-100"><i class="fa-solid fa-check"></i> Assign Target Ke Orang Ini</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>

                <!-- Section 2: Multi Catatan (Notes List & Add Form) -->
                <div class="profile-section-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="profile-section-title mb-0"><i class="fa-solid fa-clipboard-list text-primary-glow"></i> Multi Catatan</h4>
                    </div>

                    <div id="personNotesList" class="multi-notes-list mb-3">
                        <!-- Dynamic notes list -->
                    </div>

                    @auth
                        @if(Auth::user()->is_admin)
                            <!-- Add Note Form -->
                            <div class="add-note-box border-top-dark pt-2">
                                <label class="text-xs font-semibold text-muted mb-1"><i class="fa-solid fa-comment-medical"></i> Tambah Catatan Baru:</label>
                                <form id="addNoteForm" method="POST" action="">
                                    @csrf
                                    <div class="form-row mb-2">
                                        <div class="col-6">
                                            <input type="text" name="category" class="form-control text-xs" placeholder="Kategori (misal: Akademik, Keuangan)" required>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" name="status_label" class="form-control text-xs" placeholder="Status (misal: Aktif, Lunas)">
                                        </div>
                                    </div>
                                    <textarea name="note" class="form-control text-xs mb-2" rows="2" placeholder="Isi rincian catatan..."></textarea>
                                    <button type="submit" class="btn btn-outline btn-xs w-100"><i class="fa-solid fa-plus"></i> Simpan Catatan Baru</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>

                <!-- Section 3: Kegiatan Kalender -->
                <div class="profile-section-box">
                    <h4 class="profile-section-title"><i class="fa-solid fa-calendar-days text-primary-glow"></i> Kegiatan (Dari Kalender)</h4>
                    <div id="personCalendarActivitiesList" class="calendar-activities-list">
                        <!-- Calendar activities populated via JS -->
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN (Skala Ratings 1-5, Form Ubah dengan Alasan, & History Perubahan) -->
            <div class="profile-col-right">
                
                <!-- Section 4: Rating Skala 1 - 5 & Form Update dengan Alasan -->
                <form id="personProfileForm" method="POST" action="">
                    @csrf
                    <div class="profile-section-box">
                        <h4 class="profile-section-title"><i class="fa-solid fa-chart-bar text-primary-glow"></i> Penilaian Skala (1 - 5)</h4>
                        
                        <!-- Skala 1: Sales -->
                        <div class="scale-rating-group">
                            <div class="scale-label-row">
                                <span>Skala siap bertugas, sebagai sales</span>
                                <span class="scale-val-badge" id="valSales">3</span>
                            </div>
                            <div class="scale-range-bar">
                                <span class="range-min">1</span>
                                <input type="range" name="skala_sales" id="skalaSales" min="1" max="5" value="3" class="scale-slider" oninput="document.getElementById('valSales').textContent=this.value">
                                <span class="range-max">5</span>
                            </div>
                        </div>

                        <!-- Skala 2: Katim -->
                        <div class="scale-rating-group">
                            <div class="scale-label-row">
                                <span>Skala siap bertugas, sebagai katim</span>
                                <span class="scale-val-badge" id="valKatim">3</span>
                            </div>
                            <div class="scale-range-bar">
                                <span class="range-min">1</span>
                                <input type="range" name="skala_katim" id="skalaKatim" min="1" max="5" value="3" class="scale-slider" oninput="document.getElementById('valKatim').textContent=this.value">
                                <span class="range-max">5</span>
                            </div>
                        </div>

                        <!-- Skala 3: Keaktifan Kelas -->
                        <div class="scale-rating-group">
                            <div class="scale-label-row">
                                <span>Skala keaktifan kelas</span>
                                <span class="scale-val-badge" id="valKeaktifan">4</span>
                            </div>
                            <div class="scale-range-bar">
                                <span class="range-min">1</span>
                                <input type="range" name="skala_keaktifan" id="skalaKeaktifan" min="1" max="5" value="4" class="scale-slider" oninput="document.getElementById('valKeaktifan').textContent=this.value">
                                <span class="range-max">5</span>
                            </div>
                        </div>

                        <!-- Skala 4: Prioritas -->
                        <div class="scale-rating-group">
                            <div class="scale-label-row">
                                <span>Skala Prioritas</span>
                                <span class="scale-val-badge" id="valPrioritas">3</span>
                            </div>
                            <div class="scale-range-bar">
                                <span class="range-min">1</span>
                                <input type="range" name="skala_prioritas" id="skalaPrioritas" min="1" max="5" value="3" class="scale-slider" oninput="document.getElementById('valPrioritas').textContent=this.value">
                                <span class="range-max">5</span>
                            </div>
                        </div>

                        <!-- Cara agar bisa aktif -->
                        <div class="form-group mt-3">
                            <label for="caraAktifInput"><i class="fa-solid fa-lightbulb"></i> Cara agar bisa aktif</label>
                            @auth
                                @if(Auth::user()->is_admin)
                                    <textarea name="cara_aktif" id="caraAktifInput" class="form-control text-xs" rows="2" placeholder="Saran/strategi keaktifan..."></textarea>
                                @else
                                    <p id="caraAktifView" class="text-muted text-xs italic">Belum ada catatan strategi keaktifan.</p>
                                @endif
                            @else
                                <p id="caraAktifView" class="text-muted text-xs italic">Belum ada catatan strategi keaktifan.</p>
                            @endauth
                        </div>

                        @auth
                            @if(Auth::user()->is_admin)
                                <!-- Alasan Perubahan Skala -->
                                <div class="form-group mt-2 border-top-dark pt-2">
                                    <label for="scaleReasonInput" class="text-xs text-warning"><i class="fa-solid fa-comment-dots"></i> Alasan Perubahan Skala (Tercatat di Histori):</label>
                                    <input type="text" name="reason" id="scaleReasonInput" class="form-control text-xs" placeholder="Contoh: Peningkatan performa dalam rapat minggu ini">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 mt-2"><i class="fa-solid fa-save"></i> Simpan Nilai Skala & Alasan</button>
                            @endif
                        @endauth
                    </div>
                </form>

                <!-- Section 5: Riwayat Histori Perubahan Skala -->
                <div class="profile-section-box">
                    <h4 class="profile-section-title"><i class="fa-solid fa-history text-primary-glow"></i> Histori Perubahan Skala</h4>
                    <div id="personScaleHistoryTimeline" class="scale-history-timeline">
                        <!-- History timeline populated via JS -->
                    </div>
                </div>

            </div>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closePersonDetailModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL MASTER TARGETS MANAGEMENT (ADMIN ONLY)
     ========================================================================= -->
<div class="modal-backdrop" id="masterTargetsModalBackdrop" style="display: none;">
    <div class="modal-content glass-panel modal-md">
        <div class="modal-header">
            <h3><i class="fa-solid fa-list-check"></i> Kelola Master Target Global</h3>
            <button class="btn-close" onclick="closeMasterTargetsModal()">&times;</button>
        </div>
        <div class="modal-body">
            @auth
                @if(Auth::user()->is_admin)
                    <form action="{{ route('monitoring.master_targets.store') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="form-group">
                            <label for="masterTitleInput">Judul Master Target Baru</label>
                            <input type="text" id="masterTitleInput" name="title" class="form-control text-sm" placeholder="Contoh: ++PZ, ++01, Sertifikasi Katim" required>
                        </div>
                        <div class="form-group">
                            <label for="masterDescInput">Deskripsi Singkat (Opsional)</label>
                            <input type="text" id="masterDescInput" name="description" class="form-control text-sm" placeholder="Keterangan singkat target...">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Tambah Master Target</button>
                    </form>
                @endif
            @endauth

            <div class="master-targets-list border-top-dark pt-2">
                <h4 class="text-xs font-semibold text-muted mb-2">Daftar Master Target Saat Ini:</h4>
                <div class="list-group">
                    @forelse($masterTargets as $mt)
                        <div class="master-target-item">
                            <div>
                                <span class="font-semibold text-sm">{{ $mt->title }}</span>
                                @if($mt->description)
                                    <p class="text-xs text-muted mb-0">{{ $mt->description }}</p>
                                @endif
                            </div>
                            @auth
                                @if(Auth::user()->is_admin)
                                    <form action="{{ route('monitoring.master_targets.destroy', $mt->id) }}" method="POST" onsubmit="return confirm('Hapus master target ini?')">
                                        @csrf
                                        <button type="submit" class="btn-icon btn-icon-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    @empty
                        <p class="text-muted text-xs">Belum ada master target global.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeMasterTargetsModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL DAFTAR TIM / ORANG (TEAM DRAWER)
     ========================================================================= -->
<div class="modal-backdrop" id="teamDrawerModalBackdrop" style="display: none;">
    <div class="modal-content glass-panel modal-sm">
        <div class="modal-header">
            <h3><i class="fa-solid fa-users"></i> Daftar Tim / Orang ({{ $allPeople->count() }})</h3>
            <button class="btn-close" onclick="closeTeamDrawerModal()">&times;</button>
        </div>
        <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
            <div class="team-list-group">
                @forelse($allPeople as $person)
                    <div class="team-person-item" onclick="closeTeamDrawerModal(); openPersonDetailModal({{ $person->id }});">
                        <span class="person-circle-avatar">{{ strtoupper(substr($person->name, 0, 2)) }}</span>
                        <div class="team-person-info">
                            <span class="team-person-name">{{ $person->name }}</span>
                            <span class="team-person-sub text-muted">Klik untuk lihat rincian profil</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-muted"></i>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">Belum ada orang di database.</p>
                @endforelse
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeTeamDrawerModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL CREATE / EDIT CAPSTONE & SUB CAPSTONE
     ========================================================================= -->
<div class="modal-backdrop" id="milestoneModalBackdrop" style="display: none;">
    <div class="modal-content glass-panel">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fa-solid fa-flag"></i> Tambah Capstone Utama</h3>
            <button class="btn-close" onclick="closeMilestoneModal()">&times;</button>
        </div>
        
        <form id="milestoneForm" action="{{ route('monitoring.milestones.store') }}" method="POST">
            @csrf
            <input type="hidden" id="milestoneId" name="milestone_id" value="">
            
            <div class="form-group">
                <label for="milestoneParentId">Tipe Milestone / Parent Capstone</label>
                <select id="milestoneParentId" name="parent_id" class="form-control">
                    <option value="">-- Capstone Utama (Top-level) --</option>
                    @foreach($mainMilestones as $parentCandidate)
                        <option value="{{ $parentCandidate->id }}">Sub Capstone dari: #{{ $loop->iteration }} {{ $parentCandidate->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="milestoneTitle">Judul Capstone / Sub Objective <span class="text-danger">*</span></label>
                <input type="text" id="milestoneTitle" name="title" class="form-control" placeholder="Contoh: Perancangan Arsitektur Database" required>
            </div>

            <div class="form-group">
                <label for="milestoneDescription">Deskripsi Tambahan</label>
                <textarea id="milestoneDescription" name="description" class="form-control" rows="3" placeholder="Jelaskan rincian objective ini..."></textarea>
            </div>

            <div class="form-group">
                <label>Pilih Warna Akses Tema</label>
                <div class="color-options-picker">
                    <label class="color-radio">
                        <input type="radio" name="color" value="theme-purple" checked>
                        <span class="color-box theme-purple"></span> Ungu
                    </label>
                    <label class="color-radio">
                        <input type="radio" name="color" value="theme-blue">
                        <span class="color-box theme-blue"></span> Biru
                    </label>
                    <label class="color-radio">
                        <input type="radio" name="color" value="theme-green">
                        <span class="color-box theme-green"></span> Hijau
                    </label>
                    <label class="color-radio">
                        <input type="radio" name="color" value="theme-orange">
                        <span class="color-box theme-orange"></span> Oranye
                    </label>
                    <label class="color-radio">
                        <input type="radio" name="color" value="theme-cyan">
                        <span class="color-box theme-cyan"></span> Cyan
                    </label>
                    <label class="color-radio">
                        <input type="radio" name="color" value="theme-red">
                        <span class="color-box theme-red"></span> Merah
                    </label>
                </div>
            </div>

            <!-- People Selection (From database people table) -->
            <div class="form-group">
                <label><i class="fa-solid fa-users"></i> Penanggung Jawab (Orang dari Kalender)</label>
                <div class="people-select-box">
                    @foreach($allPeople as $person)
                        <label class="people-checkbox-item">
                            <input type="checkbox" name="people[]" value="{{ $person->id }}" class="person-checkbox-input">
                            <span>{{ $person->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label for="newPeopleInput"><i class="fa-solid fa-user-plus"></i> Tambah Nama Orang Baru (Opsional)</label>
                <input type="text" id="newPeopleInput" name="new_people" class="form-control" placeholder="Ketik nama dipisah koma, contoh: Andi Budi, Citra Dewi">
                <small class="text-muted">Orang baru yang ditambahkan otomatis tersimpan di database `people` Kalender.</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeMilestoneModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan Capstone</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL DETAIL PREVIEW MILESTONE (CLICK FROM RULER TICK)
     ========================================================================= -->
<div class="modal-backdrop" id="detailModalBackdrop" style="display: none;">
    <div class="modal-content glass-panel modal-sm">
        <div class="modal-header">
            <h3 id="detailTitle"><i class="fa-solid fa-circle-info"></i> Detail Capstone Objective</h3>
            <button class="btn-close" onclick="closeDetailModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="detailDescription" class="text-muted mb-3"></p>
            <div class="detail-people-box">
                <h4 class="text-sm font-semibold mb-2"><i class="fa-solid fa-users"></i> Penanggung Jawab ("Bulet-bulet"):</h4>
                <div id="detailPeopleList" class="people-avatars-list"></div>
            </div>
        </div>
        <div class="modal-footer">
            @auth
                @if(Auth::user()->is_admin)
                    <button id="btnEditFromDetail" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i> Edit Capstone</button>
                    <form id="deleteMilestoneFormFromDetail" method="POST" action="" class="d-inline" onsubmit="return confirm('Hapus capstone ini?')">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-icon-danger btn-sm"><i class="fa-solid fa-trash"></i> Hapus</button>
                    </form>
                @endif
            @endauth
            <button type="button" class="btn btn-outline btn-sm" onclick="closeDetailModal()">Tutup</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Fetch and open Person Detail Modal
    function openPersonDetailModal(personId) {
        fetch(`/api/people/${personId}/detail`)
            .then(res => res.json())
            .then(data => {
                const person = data.person;
                const profile = data.profile || {};
                const calendarActivities = data.calendar_activities || [];
                const assignedTargets = data.assigned_targets || [];
                const scaleHistories = data.scale_histories || [];
                const notes = data.notes || [];

                document.getElementById('modalPersonName').textContent = person.name;
                document.getElementById('modalPersonAvatar').textContent = person.name.substring(0, 2).toUpperCase();
                
                document.getElementById('personProfileForm').action = `/monitoring/people/${person.id}/profile`;
                
                const assignForm = document.getElementById('assignTargetForm');
                if (assignForm) assignForm.action = `/monitoring/people/${person.id}/targets`;

                const addNoteForm = document.getElementById('addNoteForm');
                if (addNoteForm) addNoteForm.action = `/monitoring/people/${person.id}/notes`;

                // 1. Populate Target Checklist
                const targetsDisplay = document.getElementById('personTargetsDisplay');
                targetsDisplay.innerHTML = '';

                if (assignedTargets.length > 0) {
                    assignedTargets.forEach(t => {
                        const item = document.createElement('div');
                        item.className = `target-item-check ${t.is_completed ? 'done' : ''}`;
                        item.innerHTML = `
                            <label class="target-checkbox-label">
                                <input type="checkbox" ${t.is_completed ? 'checked' : ''} onchange="toggleTargetStatus(${t.id}, this)">
                                <span class="target-title-text">${t.title}</span>
                            </label>
                            ${t.is_completed ? '<span class="target-done-tag">(Done ?)</span>' : ''}
                        `;
                        targetsDisplay.appendChild(item);
                    });
                } else {
                    targetsDisplay.innerHTML = '<span class="text-muted text-xs italic">Belum ada target yang di-assign.</span>';
                }

                // 2. Populate Multi Notes
                const notesList = document.getElementById('personNotesList');
                notesList.innerHTML = '';
                if (notes.length > 0) {
                    notes.forEach(n => {
                        const nEl = document.createElement('div');
                        nEl.className = 'note-card-item';
                        nEl.innerHTML = `
                            <div class="note-card-header">
                                <span class="note-cat-badge">${n.category}</span>
                                ${n.status_label ? `<span class="note-status-pill">${n.status_label}</span>` : ''}
                            </div>
                            ${n.note ? `<p class="note-card-body text-xs">${n.note}</p>` : ''}
                            <div class="note-card-footer text-muted text-xs">
                                <span><i class="fa-regular fa-clock"></i> ${new Date(n.created_at).toLocaleDateString('id-ID')}</span>
                            </div>
                        `;
                        notesList.appendChild(nEl);
                    });
                } else {
                    notesList.innerHTML = '<span class="text-muted text-xs italic">Belum ada catatan.</span>';
                }

                // 3. Populate Calendar Activities
                const activitiesList = document.getElementById('personCalendarActivitiesList');
                activitiesList.innerHTML = '';
                if (calendarActivities.length > 0) {
                    calendarActivities.forEach(act => {
                        const actEl = document.createElement('div');
                        actEl.className = 'calendar-activity-item';
                        const dateStr = act.activity_date ? new Date(act.activity_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '';
                        actEl.innerHTML = `
                            <span class="activity-cat-tag theme-purple">${act.category}</span>
                            <div class="activity-detail-info">
                                <span class="activity-desc">${act.description}</span>
                                <span class="activity-date text-muted"><i class="fa-regular fa-calendar"></i> ${dateStr}</span>
                            </div>
                        `;
                        activitiesList.appendChild(actEl);
                    });
                } else {
                    activitiesList.innerHTML = '<span class="text-muted text-xs italic">Belum ada riwayat kegiatan di Kalender.</span>';
                }

                // 4. Populate Scales (1 - 5)
                const sales = profile.skala_sales || 3;
                const katim = profile.skala_katim || 3;
                const keaktifan = profile.skala_keaktifan || 4;
                const prioritas = profile.skala_prioritas || 3;

                document.getElementById('skalaSales').value = sales;
                document.getElementById('valSales').textContent = sales;

                document.getElementById('skalaKatim').value = katim;
                document.getElementById('valKatim').textContent = katim;

                document.getElementById('skalaKeaktifan').value = keaktifan;
                document.getElementById('valKeaktifan').textContent = keaktifan;

                document.getElementById('skalaPrioritas').value = prioritas;
                document.getElementById('valPrioritas').textContent = prioritas;

                const caraInput = document.getElementById('caraAktifInput');
                if (caraInput) caraInput.value = profile.cara_aktif || '';
                const caraView = document.getElementById('caraAktifView');
                if (caraView) caraView.textContent = profile.cara_aktif || 'Belum ada catatan strategi keaktifan.';

                // 5. Populate Scale History Timeline
                const historyContainer = document.getElementById('personScaleHistoryTimeline');
                historyContainer.innerHTML = '';
                if (scaleHistories.length > 0) {
                    scaleHistories.forEach(h => {
                        const hItem = document.createElement('div');
                        hItem.className = 'scale-history-item';
                        const changeDate = new Date(h.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
                        hItem.innerHTML = `
                            <div class="history-item-top">
                                <span class="history-scale-type font-semibold text-xs text-primary">${h.scale_type.toUpperCase()}</span>
                                <span class="history-change-val">${h.old_value} <i class="fa-solid fa-arrow-right text-xs"></i> ${h.new_value}</span>
                            </div>
                            <p class="history-reason text-xs text-muted mb-1">${h.reason || 'Tidak ada alasan khusus.'}</p>
                            <div class="history-meta text-xs text-muted">
                                <span><i class="fa-regular fa-user"></i> ${h.user ? h.user.name : 'Admin'}</span>
                                <span><i class="fa-regular fa-clock"></i> ${changeDate}</span>
                            </div>
                        `;
                        historyContainer.appendChild(hItem);
                    });
                } else {
                    historyContainer.innerHTML = '<span class="text-muted text-xs italic">Belum ada riwayat perubahan skala.</span>';
                }

                document.getElementById('personDetailModalBackdrop').style.display = 'flex';
            })
            .catch(err => {
                console.error('Error fetching person detail:', err);
                alert('Gagal mengambil data profil orang.');
            });
    }

    function toggleTargetStatus(targetId, checkboxEl) {
        fetch(`/monitoring/targets/${targetId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const parent = checkboxEl.closest('.target-item-check');
                if (data.is_completed) {
                    parent.classList.add('done');
                    if (!parent.querySelector('.target-done-tag')) {
                        const tag = document.createElement('span');
                        tag.className = 'target-done-tag';
                        tag.textContent = '(Done ?)';
                        parent.appendChild(tag);
                    }
                } else {
                    parent.classList.remove('done');
                    const tag = parent.querySelector('.target-done-tag');
                    if (tag) tag.remove();
                }
            }
        })
        .catch(err => console.error('Error toggling target:', err));
    }

    function toggleCustomTargetInput(selectEl) {
        const customWrapper = document.getElementById('customTargetTitleWrapper');
        if (selectEl.value === 'custom') {
            customWrapper.style.display = 'block';
        } else {
            customWrapper.style.display = 'none';
        }
    }

    function closePersonDetailModal() {
        document.getElementById('personDetailModalBackdrop').style.display = 'none';
    }

    function openMasterTargetsModal() {
        document.getElementById('masterTargetsModalBackdrop').style.display = 'flex';
    }

    function closeMasterTargetsModal() {
        document.getElementById('masterTargetsModalBackdrop').style.display = 'none';
    }

    function openTeamDrawerModal() {
        document.getElementById('teamDrawerModalBackdrop').style.display = 'flex';
    }

    function closeTeamDrawerModal() {
        document.getElementById('teamDrawerModalBackdrop').style.display = 'none';
    }

    // Modal Helpers for Milestones
    function openCreateModal(parentId = null) {
        document.getElementById('milestoneForm').action = "{{ route('monitoring.milestones.store') }}";
        document.getElementById('milestoneId').value = '';
        document.getElementById('modalTitle').innerHTML = parentId ? '<i class="fa-solid fa-diagram-project"></i> Tambah Sub Capstone' : '<i class="fa-solid fa-flag"></i> Tambah Capstone Utama';
        document.getElementById('milestoneParentId').value = parentId ? parentId : '';
        document.getElementById('milestoneTitle').value = '';
        document.getElementById('milestoneDescription').value = '';
        document.getElementById('newPeopleInput').value = '';
        
        document.querySelectorAll('.person-checkbox-input').forEach(cb => cb.checked = false);

        document.getElementById('milestoneModalBackdrop').style.display = 'flex';
    }

    function openEditModal(milestone) {
        const updateUrl = "{{ route('monitoring.milestones.update', ':id') }}".replace(':id', milestone.id);
        document.getElementById('milestoneForm').action = updateUrl;
        document.getElementById('milestoneId').value = milestone.id;
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Edit Capstone Objective';
        document.getElementById('milestoneParentId').value = milestone.parent_id ? milestone.parent_id : '';
        document.getElementById('milestoneTitle').value = milestone.title || '';
        document.getElementById('milestoneDescription').value = milestone.description || '';
        document.getElementById('newPeopleInput').value = '';

        if (milestone.color) {
            const radio = document.querySelector(`input[name="color"][value="${milestone.color}"]`);
            if (radio) radio.checked = true;
        }

        const assignedIds = (milestone.people || []).map(p => p.id);
        document.querySelectorAll('.person-checkbox-input').forEach(cb => {
            cb.checked = assignedIds.includes(parseInt(cb.value));
        });

        document.getElementById('milestoneModalBackdrop').style.display = 'flex';
    }

    function closeMilestoneModal() {
        document.getElementById('milestoneModalBackdrop').style.display = 'none';
    }

    function showMilestoneDetail(milestone) {
        document.getElementById('detailTitle').textContent = milestone.title;
        document.getElementById('detailDescription').textContent = milestone.description || 'Tidak ada deskripsi.';

        const peopleList = document.getElementById('detailPeopleList');
        peopleList.innerHTML = '';
        if (milestone.people && milestone.people.length > 0) {
            milestone.people.forEach(p => {
                const chip = document.createElement('div');
                chip.className = 'person-chip';
                chip.onclick = function() {
                    closeDetailModal();
                    openPersonDetailModal(p.id);
                };
                chip.innerHTML = `<span class="person-chip-avatar">${p.name.substring(0, 2).toUpperCase()}</span><span class="person-chip-name">${p.name}</span>`;
                peopleList.appendChild(chip);
            });
        } else {
            peopleList.innerHTML = '<span class="text-muted text-sm italic">Belum ada penanggung jawab.</span>';
        }

        const editBtn = document.getElementById('btnEditFromDetail');
        if (editBtn) {
            editBtn.onclick = function() {
                closeDetailModal();
                openEditModal(milestone);
            };
        }

        const deleteForm = document.getElementById('deleteMilestoneFormFromDetail');
        if (deleteForm) {
            deleteForm.action = "{{ route('monitoring.milestones.destroy', ':id') }}".replace(':id', milestone.id);
        }

        document.getElementById('detailModalBackdrop').style.display = 'flex';
    }

    function closeDetailModal() {
        document.getElementById('detailModalBackdrop').style.display = 'none';
    }

    // Close modals on click backdrop
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            event.target.style.display = 'none';
        }
    };
</script>
@endsection
