@extends('layouts.layout')

@section('title', 'Detail Profil - ' . $person->name . ' - Jadwal Kegiatan Konekin')

@section('content')
<div class="person-fullpage-container">

    <!-- Top Action Navigation Bar -->
    <div class="person-fullpage-nav glass-panel mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <a href="{{ route('monitoring.index') }}" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Monitoring Capstone
            </a>
            <div class="person-header-identity d-flex align-items-center gap-3">
                <span class="person-circle-avatar-lg">{{ strtoupper(substr($person->name, 0, 2)) }}</span>
                <div>
                    <h1 class="person-name-title mb-0">{{ $person->name }}</h1>
                    <span class="text-muted text-xs"><i class="fa-solid fa-id-card"></i> Profile ID: #{{ $person->id }} • Registered Member</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Full-Page 2-Column Content Grid -->
    <div class="person-fullpage-grid">

        <!-- =========================================================================
             KOLOM KIRI: Target Checklist (Format Post / Card), Multi Catatan, & Kalender
             ========================================================================= -->
        <div class="person-grid-col">

            <!-- 1. TARGET CHECKLIST (FORMAT POST / CARD - LOCKED ONCE COMPLETED) -->
            <div class="person-card-section glass-panel">
                <div class="section-header-title mb-3">
                    <h3 class="title-text"><i class="fa-solid fa-bullseye icon-accent"></i> Target Master & Assigned Posts</h3>
                    <p class="subtitle-text text-muted">Target dalam format postingan. Target yang sudah dicentang akan otomatis terkunci (non-fillable).</p>
                </div>

                <div class="target-posts-list mb-4">
                    @forelse($person->assignedTargets as $target)
                        <div class="target-post-card {{ $target->is_completed ? 'locked-done' : 'active-pending' }}">
                            <div class="target-post-header">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="target-post-badge {{ $target->is_completed ? 'badge-locked' : 'badge-pending' }}">
                                        @if($target->is_completed)
                                            <i class="fa-solid fa-lock"></i> LOCKED / DONE
                                        @else
                                            <i class="fa-solid fa-hourglass-half"></i> IN PROGRESS
                                        @endif
                                    </span>
                                    <h4 class="target-post-title mb-0">{{ $target->title }}</h4>
                                </div>
                                <div class="target-post-action">
                                    @if($target->is_completed)
                                        <button class="btn btn-locked-done btn-xs" disabled title="Target sudah selesai dan terkunci (Non-fillable)">
                                            <i class="fa-solid fa-circle-check"></i> Selesai
                                        </button>
                                    @else
                                        <button class="btn btn-primary btn-xs" onclick="toggleTargetStatus({{ $target->id }}, this)">
                                            <i class="fa-regular fa-square"></i> Ceklis Target Ini
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @if($target->masterTarget && $target->masterTarget->description)
                                <p class="target-post-desc text-muted text-xs mt-2 mb-0">{{ $target->masterTarget->description }}</p>
                            @endif
                            <div class="target-post-footer text-muted text-xs mt-2">
                                @if($target->is_completed && $target->completed_at)
                                    <span><i class="fa-regular fa-clock"></i> Selesai pada: {{ $target->completed_at->format('d M Y H:i') }}</span>
                                @else
                                    <span><i class="fa-solid fa-pen-clip"></i> Siap dikerjakan</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-box-message">
                            <p class="text-muted text-xs mb-0">Belum ada target yang di-assign ke {{ $person->name }}.</p>
                        </div>
                    @endforelse
                </div>

                @auth
                    @if(Auth::user()->is_admin)
                        <!-- Assign Target Form -->
                        <div class="assign-target-box border-top-dark pt-3">
                            <h4 class="text-xs font-semibold text-muted mb-2"><i class="fa-solid fa-plus-circle"></i> Assign Target Baru Ke Postingan:</h4>
                            <form action="{{ route('monitoring.people.targets.assign', $person->id) }}" method="POST">
                                @csrf
                                <div class="form-group mb-2">
                                    <select name="master_target_id" class="form-control text-xs" onchange="toggleCustomTargetInput(this)">
                                        <option value="">-- Pilih dari Master Target --</option>
                                        @foreach($masterTargets as $mt)
                                            <option value="{{ $mt->id }}">{{ $mt->title }}</option>
                                        @endforeach
                                        <option value="custom">+ Ketik Target Kustom Baru...</option>
                                    </select>
                                </div>
                                <div id="customTargetTitleWrapper" class="form-group mb-2" style="display: none;">
                                    <input type="text" name="custom_title" class="form-control text-xs" placeholder="Ketik nama target kustom baru...">
                                </div>
                                <button type="submit" class="btn btn-outline btn-xs w-100"><i class="fa-solid fa-paper-plane"></i> Tambah Target Post</button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- 2. MULTI CATATAN (FORMAT POST / CARD) -->
            <div class="person-card-section glass-panel">
                <div class="section-header-title mb-3">
                    <h3 class="title-text"><i class="fa-solid fa-clipboard-list icon-accent"></i> Multi Catatan & Postings</h3>
                    <p class="subtitle-text text-muted">Daftar catatan berganda per kategori untuk {{ $person->name }}.</p>
                </div>

                <div class="multi-notes-posts-list mb-4">
                    @forelse($person->notes as $note)
                        <div class="note-post-card">
                            <div class="note-post-header">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="note-category-pill">{{ $note->category }}</span>
                                    @if($note->status_label)
                                        <span class="note-status-badge">{{ $note->status_label }}</span>
                                    @endif
                                </div>
                                @auth
                                    @if(Auth::user()->is_admin)
                                        <form action="{{ route('monitoring.notes.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Hapus catatan ini?')">
                                            @csrf
                                            <button type="submit" class="btn-icon-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                            @if($note->note)
                                <p class="note-post-content text-xs mt-2 mb-2">{{ $note->note }}</p>
                            @endif
                            <div class="note-post-footer text-muted text-xs">
                                <span><i class="fa-regular fa-user"></i> {{ $note->user ? $note->user.name : 'Admin' }}</span>
                                <span><i class="fa-regular fa-clock"></i> {{ $note->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-box-message">
                            <p class="text-muted text-xs mb-0">Belum ada catatan.</p>
                        </div>
                    @endforelse
                </div>

                @auth
                    @if(Auth::user()->is_admin)
                        <!-- Add Note Form -->
                        <div class="add-note-box border-top-dark pt-3">
                            <h4 class="text-xs font-semibold text-muted mb-2"><i class="fa-solid fa-comment-medical"></i> Tambah Post Catatan Baru:</h4>
                            <form action="{{ route('monitoring.people.notes.store', $person->id) }}" method="POST">
                                @csrf
                                <div class="form-row mb-2">
                                    <div class="col-6">
                                        <input type="text" name="category" class="form-control text-xs" placeholder="Kategori (misal: Akademik, Keuangan)" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="status_label" class="form-control text-xs" placeholder="Status Label (misal: Aktif, Lunas)">
                                    </div>
                                </div>
                                <textarea name="note" class="form-control text-xs mb-2" rows="2" placeholder="Isi rincian catatan..."></textarea>
                                <button type="submit" class="btn btn-outline btn-xs w-100"><i class="fa-solid fa-plus"></i> Simpan Catatan Post</button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- 3. KEGIATAN KALENDER (HANYA NON-PIC) -->
            <div class="person-card-section glass-panel">
                <div class="section-header-title mb-3">
                    <h3 class="title-text"><i class="fa-solid fa-calendar-days icon-accent"></i> Keterlibatan Kegiatan (Kalender)</h3>
                    <p class="subtitle-text text-muted">Daftar acara di mana {{ $person->name }} terlibat (kegiatan tempat {{ $person->name }} menjadi PIC tidak dimasukkan).</p>
                </div>

                <div class="calendar-activities-posts">
                    @forelse($nonPicActivities as $act)
                        <div class="activity-post-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="activity-category-tag">{{ $act->category }}</span>
                                <span class="activity-date-badge text-muted text-xs">
                                    <i class="fa-regular fa-calendar"></i> {{ $act->activity_date ? \Carbon\Carbon::parse($act->activity_date)->format('d M Y') : '-' }}
                                </span>
                            </div>
                            <p class="activity-desc-text font-semibold text-xs mt-2 mb-1">{{ $act->description }}</p>
                            @if($act->pic)
                                <span class="text-xs text-muted"><i class="fa-solid fa-user-tie"></i> PIC Acara: {{ $act->pic->name }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="empty-box-message">
                            <p class="text-muted text-xs mb-0">Belum ada riwayat kegiatan non-PIC di Kalender.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- =========================================================================
             KOLOM KANAN: Penilaian Skala 1 - 5, Form Update Alasan, & History Timeline
             ========================================================================= -->
        <div class="person-grid-col">

            <!-- 4. RATING SKALA 1 - 5 & FORM UPDATE DENGAN ALASAN -->
            <div class="person-card-section glass-panel">
                <div class="section-header-title mb-3">
                    <h3 class="title-text"><i class="fa-solid fa-sliders icon-accent"></i> Penilaian Skala (1 - 5)</h3>
                    <p class="subtitle-text text-muted">Evaluasi rating keaktifan dan kesiapan tugas {{ $person->name }}.</p>
                </div>

                <form action="{{ route('monitoring.people.profile.update', $person->id) }}" method="POST">
                    @csrf

                    <!-- Skala 1: Sales -->
                    <div class="scale-box-row mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="scale-title-text font-semibold text-xs">Skala siap bertugas, sebagai sales</span>
                            <span class="scale-number-badge" id="valSales">{{ $person->profile->skala_sales ?? 3 }}</span>
                        </div>
                        <div class="scale-slider-track">
                            <span class="range-text">1</span>
                            <input type="range" name="skala_sales" min="1" max="5" value="{{ $person->profile->skala_sales ?? 3 }}" class="custom-slider" oninput="document.getElementById('valSales').textContent=this.value">
                            <span class="range-text">5</span>
                        </div>
                    </div>

                    <!-- Skala 2: Katim -->
                    <div class="scale-box-row mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="scale-title-text font-semibold text-xs">Skala siap bertugas, sebagai katim</span>
                            <span class="scale-number-badge" id="valKatim">{{ $person->profile->skala_katim ?? 3 }}</span>
                        </div>
                        <div class="scale-slider-track">
                            <span class="range-text">1</span>
                            <input type="range" name="skala_katim" min="1" max="5" value="{{ $person->profile->skala_katim ?? 3 }}" class="custom-slider" oninput="document.getElementById('valKatim').textContent=this.value">
                            <span class="range-text">5</span>
                        </div>
                    </div>

                    <!-- Skala 3: Keaktifan -->
                    <div class="scale-box-row mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="scale-title-text font-semibold text-xs">Skala keaktifan kelas</span>
                            <span class="scale-number-badge" id="valKeaktifan">{{ $person->profile->skala_keaktifan ?? 4 }}</span>
                        </div>
                        <div class="scale-slider-track">
                            <span class="range-text">1</span>
                            <input type="range" name="skala_keaktifan" min="1" max="5" value="{{ $person->profile->skala_keaktifan ?? 4 }}" class="custom-slider" oninput="document.getElementById('valKeaktifan').textContent=this.value">
                            <span class="range-text">5</span>
                        </div>
                    </div>

                    <!-- Skala 4: Prioritas -->
                    <div class="scale-box-row mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="scale-title-text font-semibold text-xs">Skala Prioritas</span>
                            <span class="scale-number-badge" id="valPrioritas">{{ $person->profile->skala_prioritas ?? 3 }}</span>
                        </div>
                        <div class="scale-slider-track">
                            <span class="range-text">1</span>
                            <input type="range" name="skala_prioritas" min="1" max="5" value="{{ $person->profile->skala_prioritas ?? 3 }}" class="custom-slider" oninput="document.getElementById('valPrioritas').textContent=this.value">
                            <span class="range-text">5</span>
                        </div>
                    </div>

                    <!-- Cara agar bisa aktif -->
                    <div class="form-group mb-3 mt-3">
                        <label class="text-xs font-semibold text-muted mb-1"><i class="fa-solid fa-lightbulb"></i> Cara Agar Bisa Aktif / Catatan Strategi:</label>
                        @auth
                            @if(Auth::user()->is_admin)
                                <textarea name="cara_aktif" class="form-control text-xs" rows="2" placeholder="Catatan masukan strategi keaktifan...">{{ $person->profile->cara_aktif ?? '' }}</textarea>
                            @else
                                <p class="text-muted text-xs italic mb-0">{{ $person->profile->cara_aktif ?? 'Belum ada catatan strategi keaktifan.' }}</p>
                            @endif
                        @else
                            <p class="text-muted text-xs italic mb-0">{{ $person->profile->cara_aktif ?? 'Belum ada catatan strategi keaktifan.' }}</p>
                        @endauth
                    </div>

                    @auth
                        @if(Auth::user()->is_admin)
                            <!-- Alasan Perubahan Skala -->
                            <div class="form-group mb-3 border-top-dark pt-2">
                                <label class="text-xs font-semibold text-warning mb-1"><i class="fa-solid fa-comment-dots"></i> Alasan Perubahan Skala (Wajib Diisi untuk Histori):</label>
                                <input type="text" name="reason" class="form-control text-xs" placeholder="Contoh: Menunjukkan kepemimpinan yang baik saat rapat tim">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-save"></i> Simpan Penilaian Skala & Alasan</button>
                        @endif
                    @endauth
                </form>
            </div>

            <!-- 5. HISTORI PERUBAHAN SKALA -->
            <div class="person-card-section glass-panel">
                <div class="section-header-title mb-3">
                    <h3 class="title-text"><i class="fa-solid fa-history icon-accent"></i> Histori Perubahan Skala</h3>
                    <p class="subtitle-text text-muted">Catatan jejak perubahan nilai rating skala beserta alasannya.</p>
                </div>

                <div class="history-timeline-list">
                    @forelse($person->scaleHistories as $history)
                        <div class="history-timeline-card">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="history-scale-tag">{{ strtoupper($history->scale_type) }}</span>
                                <span class="history-value-badge">{{ $history->old_value }} <i class="fa-solid fa-arrow-right text-xs"></i> {{ $history->new_value }}</span>
                            </div>
                            <p class="history-reason-text text-xs text-muted mb-2">{{ $history->reason ?? 'Tidak ada alasan khusus.' }}</p>
                            <div class="history-meta-info text-muted text-xs d-flex justify-content-between">
                                <span><i class="fa-regular fa-user"></i> {{ $history->user ? $history->user.name : 'Admin' }}</span>
                                <span><i class="fa-regular fa-clock"></i> {{ $history->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-box-message">
                            <p class="text-muted text-xs mb-0">Belum ada riwayat perubahan skala.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    function toggleTargetStatus(targetId, btnEl) {
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
                const card = btnEl.closest('.target-post-card');
                card.classList.remove('active-pending');
                card.classList.add('locked-done');

                const badge = card.querySelector('.target-post-badge');
                if (badge) {
                    badge.className = 'target-post-badge badge-locked';
                    badge.innerHTML = '<i class="fa-solid fa-lock"></i> LOCKED / DONE';
                }

                btnEl.className = 'btn btn-locked-done btn-xs';
                btnEl.disabled = true;
                btnEl.title = 'Target sudah selesai dan terkunci (Non-fillable)';
                btnEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Selesai';

                const footer = card.querySelector('.target-post-footer');
                if (footer) {
                    footer.innerHTML = `<span><i class="fa-regular fa-clock"></i> Selesai pada: ${data.completed_at}</span>`;
                }
            } else {
                alert(data.message || 'Target ini telah terkunci.');
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
</script>
@endsection
