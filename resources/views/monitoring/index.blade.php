@extends('layouts.layout')

@section('title', 'Monitoring Capstone Objective - Jadwal Kegiatan Konekin')

@section('content')
<div class="monitoring-container full-page-monitoring">

    <!-- Page Header & Action Bar -->
    <div class="monitoring-header glass-panel mb-4">
        <div class="monitoring-header-main">
            <div>
                <h1 class="monitoring-title">
                    <i class="fa-solid fa-ruler-combined text-primary-glow"></i> Monitoring Capstone Objective
                </h1>
                <p class="monitoring-subtitle text-muted">
                    Visualisasi ruler timeline horizontal se-halaman. Hover untuk melihat preview, klik untuk melihat anggota tim.
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
         LITERAL PHYSICAL HORIZONTAL RULER TIMELINE (PENGGARIS MURNI TANPA CARD)
         ========================================================================= -->
    <div class="literal-ruler-wrapper glass-panel">
        
        <div class="literal-ruler-legend">
            <span class="legend-item"><span class="legend-tick major"></span> Capstone Utama (Major Tick)</span>
            <span class="legend-item"><span class="legend-tick minor"></span> Sub Capstone (Minor Tick)</span>
            <span class="legend-item"><span class="legend-circle-icon"></span> Penanggung Jawab ("Bulet-bulet" di bawah garis)</span>
        </div>

        <div class="literal-ruler-scroll-area">
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
                <div class="literal-ruler-canvas">
                    
                    <!-- TICKS ROW (STANDING UP FROM BASELINE) -->
                    <div class="literal-ruler-ticks-row">
                        @foreach($mainMilestones as $mainIndex => $main)
                            
                            <!-- MAJOR TICK COLUMN (CAPSTONE UTAMA) -->
                            <div class="ruler-tick-column major-column" data-id="{{ $main->id }}">
                                
                                <!-- Hover Tooltip Preview (Muncul saat cursor diarahkan / hover) -->
                                <div class="ruler-hover-preview">
                                    <div class="preview-header">
                                        <span class="preview-tag major">Capstone #{{ $mainIndex + 1 }}</span>
                                        @auth
                                            @if(Auth::user()->is_admin)
                                                <button type="button" class="btn-xs-preview-edit" onclick="event.stopPropagation(); openEditModal({{ json_encode($main) }})">
                                                    <i class="fa-solid fa-pen"></i> Edit
                                                </button>
                                            @endif
                                        @endauth
                                    </div>
                                    <h4 class="preview-title">{{ $main->title }}</h4>
                                    @if($main->description)
                                        <p class="preview-desc">{{ $main->description }}</p>
                                    @endif
                                    <div class="preview-people text-xs">
                                        <i class="fa-solid fa-users"></i> {{ $main->people->count() }} Orang Alokasi (Klik untuk lihat daftar orang)
                                    </div>
                                </div>

                                <!-- Tick Label Number Above -->
                                <span class="ruler-tick-num major" onclick="showMilestonePeoplePopup({{ json_encode($main) }})">#{{ $mainIndex + 1 }}</span>

                                <!-- Vertical Tick Line (Tall) standing UP from baseline -->
                                <div class="ruler-vertical-tick major-tick-line {{ $main->color }}" onclick="showMilestonePeoplePopup({{ json_encode($main) }})"></div>

                                <!-- Avatar circles UNDER the baseline line ("bulet-bulet" `o o`) -->
                                <div class="ruler-avatars-below">
                                    @foreach($main->people as $person)
                                        <a href="{{ route('monitoring.people.show', $person->id) }}" 
                                           class="ruler-circle-avatar" 
                                           title="{{ $person->name }} (Klik untuk Full Page Profil)">
                                            {{ strtoupper(substr($person->name, 0, 2)) }}
                                        </a>
                                    @endforeach
                                </div>

                            </div>

                            <!-- SUB CAPSTONES (MINOR TICKS) FOR THIS CAPSTONE -->
                            @foreach($main->subMilestones as $subIndex => $sub)
                                <div class="ruler-tick-column minor-column" data-id="{{ $sub->id }}">
                                    
                                    <!-- Hover Tooltip Preview (Muncul saat cursor diarahkan / hover) -->
                                    <div class="ruler-hover-preview">
                                        <div class="preview-header">
                                            <span class="preview-tag minor">Sub {{ $mainIndex + 1 }}.{{ $subIndex + 1 }}</span>
                                            @auth
                                                @if(Auth::user()->is_admin)
                                                    <button type="button" class="btn-xs-preview-edit" onclick="event.stopPropagation(); openEditModal({{ json_encode($sub) }})">
                                                        <i class="fa-solid fa-pen"></i> Edit
                                                    </button>
                                                @endif
                                            @endauth
                                        </div>
                                        <h4 class="preview-title">{{ $sub->title }}</h4>
                                        @if($sub->description)
                                            <p class="preview-desc">{{ $sub->description }}</p>
                                        @endif
                                        <div class="preview-people text-xs">
                                            <i class="fa-solid fa-users"></i> {{ $sub->people->count() }} Orang Alokasi (Klik untuk lihat daftar orang)
                                        </div>
                                    </div>

                                    <!-- Tick Label Number Above -->
                                    <span class="ruler-tick-num minor" onclick="showMilestonePeoplePopup({{ json_encode($sub) }})">{{ $mainIndex + 1 }}.{{ $subIndex + 1 }}</span>

                                    <!-- Vertical Tick Line (Shorter) standing UP from baseline -->
                                    <div class="ruler-vertical-tick minor-tick-line" onclick="showMilestonePeoplePopup({{ json_encode($sub) }})"></div>

                                    <!-- Avatar circles UNDER the baseline line ("bulet-bulet" `o o`) -->
                                    <div class="ruler-avatars-below">
                                        @foreach($sub->people as $subPerson)
                                            <a href="{{ route('monitoring.people.show', $subPerson->id) }}" 
                                               class="ruler-circle-avatar mini" 
                                               title="{{ $subPerson->name }} (Klik untuk Full Page Profil)">
                                                {{ strtoupper(substr($subPerson->name, 0, 2)) }}
                                            </a>
                                        @endforeach
                                    </div>

                                </div>
                            @endforeach

                            @auth
                                @if(Auth::user()->is_admin)
                                    <!-- Add Sub Capstone Tick Column -->
                                    <div class="ruler-tick-column add-sub-column">
                                        <button class="btn-inline-add-sub" onclick="openCreateModal({{ $main->id }})" title="Tambah Sub Capstone">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                        <div class="ruler-vertical-tick spacer-tick-line"></div>
                                    </div>
                                @endif
                            @endauth

                        @endforeach
                    </div>

                    <!-- CONTINUOUS HORIZONTAL BASELINE LINE AT THE BOTTOM -->
                    <div class="literal-ruler-baseline-line"></div>

                </div>
            @endif
        </div>
    </div>

</div>

<!-- =========================================================================
     MODAL POP-UP ANGGOTA TIM DI MILESTONE (MUNCIUL PAS DIPENCET TICK PENGGARIS)
     ========================================================================= -->
<div class="modal-backdrop" id="milestonePeopleModalBackdrop" style="display: none;">
    <div class="modal-content glass-panel modal-md">
        <div class="modal-header">
            <div>
                <h3 id="milestonePeopleModalTitle" class="mb-0"><i class="fa-solid fa-users icon-accent"></i> Anggota Tim Milestone</h3>
                <span id="milestonePeopleModalSub" class="text-muted text-xs">Daftar penanggung jawab pada objective ini</span>
            </div>
            <button class="btn-close" onclick="closeMilestonePeopleModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="milestonePeopleModalDesc" class="text-muted text-xs mb-3"></p>
            
            <div class="assigned-people-section">
                <h4 class="text-xs font-semibold text-muted mb-2"><i class="fa-solid fa-user-check"></i> Daftar Orang ("Siapa-Siapa Aja"):</h4>
                <div id="milestonePeopleListGroup" class="people-pop-list-group">
                    <!-- Populated via JS -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            @auth
                @if(Auth::user()->is_admin)
                    <button id="btnEditMilestoneFromPeopleModal" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i> Edit Capstone</button>
                    <form id="deleteMilestoneFormFromPeopleModal" method="POST" action="" class="d-inline" onsubmit="return confirm('Hapus capstone ini?')">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-icon-danger btn-sm"><i class="fa-solid fa-trash"></i> Hapus</button>
                    </form>
                @endif
            @endauth
            <button type="button" class="btn btn-outline btn-sm" onclick="closeMilestonePeopleModal()">Tutup</button>
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
                        <div class="form-group mb-2">
                            <label for="masterTitleInput" class="text-xs font-semibold">Judul Master Target Baru</label>
                            <input type="text" id="masterTitleInput" name="title" class="form-control text-sm" placeholder="Contoh: ++PZ, ++01, Sertifikasi Katim" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="masterDescInput" class="text-xs font-semibold">Deskripsi Singkat (Opsional)</label>
                            <input type="text" id="masterDescInput" name="description" class="form-control text-sm" placeholder="Keterangan singkat target...">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-plus"></i> Tambah Master Target</button>
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
                    <a href="{{ route('monitoring.people.show', $person->id) }}" class="team-person-item-link">
                        <span class="person-circle-avatar">{{ strtoupper(substr($person->name, 0, 2)) }}</span>
                        <div class="team-person-info">
                            <span class="team-person-name">{{ $person->name }}</span>
                            <span class="team-person-sub text-muted">Klik untuk lihat Full-Page Profil</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-muted"></i>
                    </a>
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
            
            <div class="form-group mb-3">
                <label for="milestoneParentId">Tipe Milestone / Parent Capstone</label>
                <select id="milestoneParentId" name="parent_id" class="form-control">
                    <option value="">-- Capstone Utama (Top-level) --</option>
                    @foreach($mainMilestones as $parentCandidate)
                        <option value="{{ $parentCandidate->id }}">Sub Capstone dari: #{{ $loop->iteration }} {{ $parentCandidate->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="milestoneTitle">Judul Capstone / Sub Objective <span class="text-danger">*</span></label>
                <input type="text" id="milestoneTitle" name="title" class="form-control" placeholder="Contoh: Perancangan Arsitektur Database" required>
            </div>

            <div class="form-group mb-3">
                <label for="milestoneDescription">Deskripsi Tambahan</label>
                <textarea id="milestoneDescription" name="description" class="form-control" rows="3" placeholder="Jelaskan rincian objective ini..."></textarea>
            </div>

            <div class="form-group mb-3">
                <label class="mb-2 font-semibold">Pilih Warna Akses Tema</label>
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

            <!-- People Selection -->
            <div class="form-group mb-3">
                <label class="mb-2 font-semibold"><i class="fa-solid fa-users"></i> Penanggung Jawab (Orang dari Kalender)</label>
                <div class="people-select-box">
                    @foreach($allPeople as $person)
                        <label class="people-checkbox-item">
                            <input type="checkbox" name="people[]" value="{{ $person->id }}" class="person-checkbox-input">
                            <span>{{ $person->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group mb-3">
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

@endsection

@section('scripts')
<script>
    // Open Milestone People Pop-up Modal when clicking a ruler tick ("pas di pencet baru muncul pop up siapa saja")
    function showMilestonePeoplePopup(milestone) {
        document.getElementById('milestonePeopleModalTitle').textContent = milestone.title;
        document.getElementById('milestonePeopleModalDesc').textContent = milestone.description || 'Tidak ada deskripsi rincian.';

        const listContainer = document.getElementById('milestonePeopleListGroup');
        listContainer.innerHTML = '';

        if (milestone.people && milestone.people.length > 0) {
            milestone.people.forEach(person => {
                const item = document.createElement('a');
                item.href = `/monitoring/people/${person.id}`;
                item.className = 'pop-person-item-card';
                item.innerHTML = `
                    <div class="d-flex align-items-center gap-3">
                        <span class="person-circle-avatar-md">${person.name.substring(0, 2).toUpperCase()}</span>
                        <div>
                            <h4 class="person-name text-sm mb-0">${person.name}</h4>
                            <span class="text-xs text-muted">Klik untuk lihat Full-Page Profil & Target</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-muted"></i>
                `;
                listContainer.appendChild(item);
            });
        } else {
            listContainer.innerHTML = '<p class="text-muted text-xs italic py-2">Belum ada anggota tim/orang yang ditugaskan pada milestone ini.</p>';
        }

        const editBtn = document.getElementById('btnEditMilestoneFromPeopleModal');
        if (editBtn) {
            editBtn.onclick = function() {
                closeMilestonePeopleModal();
                openEditModal(milestone);
            };
        }

        const deleteForm = document.getElementById('deleteMilestoneFormFromPeopleModal');
        if (deleteForm) {
            deleteForm.action = "{{ route('monitoring.milestones.destroy', ':id') }}".replace(':id', milestone.id);
        }

        document.getElementById('milestonePeopleModalBackdrop').style.display = 'flex';
    }

    function closeMilestonePeopleModal() {
        document.getElementById('milestonePeopleModalBackdrop').style.display = 'none';
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

    // Close modals on click backdrop
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            event.target.style.display = 'none';
        }
    };
</script>
@endsection
