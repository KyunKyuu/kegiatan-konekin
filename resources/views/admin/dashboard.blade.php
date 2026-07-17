@extends('layouts.layout')

@section('title', 'Dashboard Analitik Admin - Jadwal Kegiatan')

@section('styles')
<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* CSS for Dashboard Tab Navigation */
    .dashboard-tabs {
        display: flex;
        gap: 12px;
        margin-bottom: 28px;
        background: rgba(255, 255, 255, 0.02);
        padding: 6px;
        border-radius: 12px;
        border: 1px solid var(--card-border);
        width: fit-content;
    }

    .tab-btn {
        background: transparent;
        border: none;
        color: var(--text-secondary);
        padding: 10px 20px;
        border-radius: 8px;
        font-family: var(--font-primary);
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s ease;
    }

    .tab-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.03);
    }

    .tab-btn.active {
        color: #fff;
        background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
    }

    /* Tab content hiding without breaking Chart.js layout size */
    .tab-content {
        position: absolute;
        left: -9999px;
        opacity: 0;
        visibility: hidden;
        height: 0;
        overflow: hidden;
        width: 100%;
        transition: opacity 0.15s ease-out;
    }

    .tab-content.active {
        position: static;
        opacity: 1;
        visibility: visible;
        height: auto;
        overflow: visible;
        width: auto;
    }

    /* Leaderboard Podium CSS */
    .leaderboard-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 24px;
        backdrop-filter: blur(12px);
        margin-bottom: 24px;
    }

    .leaderboard-filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .inline-filters {
        display: flex;
        gap: 10px;
    }

    .filter-select {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--card-border);
        padding: 8px 12px;
        border-radius: 8px;
        color: #fff;
        outline: none;
        font-family: var(--font-primary);
        font-size: 0.9rem;
        cursor: pointer;
        color-scheme: dark; /* Forces Chrome, Edge, Safari to render dropdown list in dark mode */
    }

    .filter-select option {
        background-color: #121824; /* Matches var(--card-bg) */
        color: #fff;
    }

    .filter-select:focus {
        border-color: var(--primary);
    }

    .podium-wrapper {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 20px;
        margin: 40px 0 20px;
        flex-wrap: wrap;
    }

    .podium-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 170px;
        position: relative;
    }

    .podium-crown {
        position: absolute;
        top: -34px;
        font-size: 1.8rem;
        animation: float 2s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    .podium-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        position: relative;
        margin-bottom: 12px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.4);
    }

    .podium-avatar.gold {
        background: radial-gradient(circle, rgba(251, 191, 36, 0.2) 0%, rgba(251, 191, 36, 0.05) 100%);
        border: 2px solid #fbbf24;
        box-shadow: 0 0 20px rgba(251, 191, 36, 0.2);
    }

    .podium-avatar.silver {
        background: radial-gradient(circle, rgba(209, 213, 219, 0.2) 0%, rgba(209, 213, 219, 0.05) 100%);
        border: 2px solid #d1d5db;
        box-shadow: 0 0 20px rgba(209, 213, 219, 0.15);
    }

    .podium-avatar.bronze {
        background: radial-gradient(circle, rgba(180, 83, 9, 0.2) 0%, rgba(180, 83, 9, 0.05) 100%);
        border: 2px solid #b45309;
        box-shadow: 0 0 20px rgba(180, 83, 9, 0.15);
    }

    .rank-badge {
        position: absolute;
        bottom: -4px;
        right: -4px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 800;
        color: #000;
    }

    .gold .rank-badge { background: #fbbf24; }
    .silver .rank-badge { background: #d1d5db; }
    .bronze .rank-badge { background: #b45309; color: #fff; }

    .icon-gold { color: #fbbf24; filter: drop-shadow(0 0 6px rgba(251,191,36,0.6)); }
    .icon-silver { color: #d1d5db; filter: drop-shadow(0 0 6px rgba(209,213,219,0.5)); }
    .icon-bronze { color: #b45309; filter: drop-shadow(0 0 6px rgba(180,83,9,0.5)); }

    .podium-info {
        text-align: center;
        margin-bottom: 12px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .podium-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: #fff;
    }

    .podium-score {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--primary);
    }

    .podium-meta {
        display: flex;
        gap: 8px;
        font-size: 0.72rem;
        color: var(--text-secondary);
        justify-content: center;
    }

    .podium-pedestal {
        width: 100%;
        border-radius: 8px 8px 0 0;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 100%);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-bottom: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .podium-pedestal.p-1 { height: 110px; background: linear-gradient(180deg, rgba(251, 191, 36, 0.1) 0%, rgba(255, 255, 255, 0.01) 100%); border-color: rgba(251, 191, 36, 0.15); }
    .podium-pedestal.p-2 { height: 80px; background: linear-gradient(180deg, rgba(209, 213, 219, 0.08) 0%, rgba(255, 255, 255, 0.01) 100%); border-color: rgba(209, 213, 219, 0.1); }
    .podium-pedestal.p-3 { height: 60px; background: linear-gradient(180deg, rgba(180, 83, 9, 0.08) 0%, rgba(255, 255, 255, 0.01) 100%); border-color: rgba(180, 83, 9, 0.1); }

    .pedestal-number {
        font-size: 2.2rem;
        font-family: var(--font-display);
        font-weight: 800;
        opacity: 0.15;
    }
</style>
@endsection

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-header-container">
        <div>
            <h1 class="dashboard-title">Dashboard Analitik</h1>
            <p class="dashboard-subtitle">Pantau aktivitas, produktivitas tim, dan persebaran kategori kegiatan secara real-time.</p>
        </div>
        <div class="dashboard-header-badge">
            <span class="badge badge-admin"><i class="fa-solid fa-user-shield"></i> Mode Administrator</span>
        </div>
    </div>

    <!-- TABS NAVIGATION BUTTONS -->
    <div class="dashboard-tabs">
        <button class="tab-btn active" onclick="switchTab('analytics')">
            <i class="fa-solid fa-chart-pie"></i> Analitik & Tren
        </button>
        <button class="tab-btn" onclick="switchTab('members')">
            <i class="fa-solid fa-users"></i> Keterlibatan & Log
        </button>
        <button class="tab-btn" onclick="switchTab('users')">
            <i class="fa-solid fa-users-gear"></i> Manajemen User
        </button>
        <button class="tab-btn" onclick="switchTab('settings')">
            <i class="fa-solid fa-gears"></i> Pengaturan & Laporan
        </button>
    </div>

    <!-- TAB 1: ANALYTICS & TRENDS -->
    <div id="tab-analytics" class="tab-content active">
        <!-- METRIC CARDS -->
        <div class="metrics-grid">
            <div class="metric-card card-purple animate-scale-up">
                <div class="metric-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="metric-info">
                    <h3>Total Kegiatan</h3>
                    <p class="metric-value">{{ $totalActivities }}</p>
                    <span class="metric-sub">Kegiatan terjadwal</span>
                </div>
            </div>

            <div class="metric-card card-blue animate-scale-up">
                <div class="metric-icon"><i class="fa-solid fa-users"></i></div>
                <div class="metric-info">
                    <h3>Orang Terlibat</h3>
                    <p class="metric-value">{{ $totalPeople }}</p>
                    <span class="metric-sub">PIC & Peserta terdaftar</span>
                </div>
            </div>

            <div class="metric-card card-green animate-scale-up">
                <div class="metric-icon"><i class="fa-solid fa-users-gear"></i></div>
                <div class="metric-info">
                    <h3>Pengguna Sistem</h3>
                    <p class="metric-value">{{ $totalUsers }}</p>
                    <span class="metric-sub">Akun login aktif</span>
                </div>
            </div>

            <div class="metric-card card-orange animate-scale-up">
                <div class="metric-icon"><i class="fa-solid fa-award"></i></div>
                <div class="metric-info">
                    <h3>PIC Paling Aktif</h3>
                    <p class="metric-value text-lg">
                        {{ $mostActivePerson ? Str::limit($mostActivePerson->name, 16) : 'Tidak ada' }}
                    </p>
                    <span class="metric-sub">
                        {{ $mostActivePerson ? $mostActivePerson->total_activities . ' Kegiatan' : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- CHARTS SECTION -->
        <div class="charts-grid" style="margin-top: 24px;">
            <!-- Chart 2: Categories Share -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fa-solid fa-pie-chart"></i> Persebaran Kategori</h3>
                    <span class="chart-subtitle">Proporsi kategori kegiatan</span>
                </div>
                <div class="chart-body flex-center">
                    <div class="chart-container-doughnut">
                        <canvas id="categoriesShareChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart 3: Monthly Trend -->
            <div class="chart-card card-span-2">
                <div class="chart-header">
                    <h3><i class="fa-solid fa-chart-line"></i> Tren Kegiatan Bulanan</h3>
                    <span class="chart-subtitle">Jumlah kegiatan dari bulan ke bulan</span>
                </div>
                <div class="chart-body">
                    <canvas id="monthlyTrendChart" height="260"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: MEMBERS & LOGS -->
    <div id="tab-members" class="tab-content">        <!-- Leaderboard Podium with Filter -->
        <div class="leaderboard-container animate-scale-up">
            <div class="leaderboard-filter-header">
                <div class="leaderboard-title-group">
                    <h3><i class="fa-solid fa-trophy icon-gold"></i> Klasemen Anggota Teraktif</h3>
                    <span class="chart-subtitle">Orang dengan total keterlibatan kegiatan terbanyak (sebagai PIC + Peserta)</span>
                </div>
                <form action="{{ route('admin.dashboard') }}" method="GET" id="leaderboardFilterForm" class="leaderboard-filter-form">
                    <div class="inline-filters">
                        <select name="lb_month" onchange="this.form.submit()" class="filter-select">
                            <option value="">Semua Bulan (All Time)</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $lbMonth == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                        <select name="lb_year" onchange="this.form.submit()" class="filter-select">
                            <option value="">Semua Tahun</option>
                            @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                                <option value="{{ $y }}" {{ ($lbYear ?? date('Y')) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>

            @if($topPeople->count() > 0)
                <div class="podium-wrapper">
                    <!-- 2nd Place (Silver) -->
                    @if($topPeople->count() > 1)
                        @php $second = $topPeople[1]; @endphp
                        <div class="podium-step step-2">
                            <div class="podium-avatar silver">
                                <i class="fa-solid fa-medal icon-silver"></i>
                                <span class="rank-badge">2</span>
                            </div>
                            <div class="podium-info">
                                <span class="podium-name">{{ $second->name }}</span>
                                <span class="podium-score">{{ $second->total_activities }} Kegiatan</span>
                                <div class="podium-meta">
                                    <span>PIC: {{ $second->pic_count }}</span>
                                    <span>Peserta: {{ $second->participant_count }}</span>
                                </div>
                            </div>
                            <div class="podium-pedestal p-2">
                                <div class="pedestal-number">2</div>
                            </div>
                        </div>
                    @endif

                    <!-- 1st Place (Gold) -->
                    @if($topPeople->count() > 0)
                        @php $first = $topPeople[0]; @endphp
                        <div class="podium-step step-1">
                            <div class="podium-crown">
                                <i class="fa-solid fa-crown icon-gold"></i>
                            </div>
                            <div class="podium-avatar gold">
                                <i class="fa-solid fa-medal icon-gold"></i>
                                <span class="rank-badge">1</span>
                            </div>
                            <div class="podium-info">
                                <span class="podium-name font-bold" style="font-size: 1.05rem; font-weight: 700; color: #fff;">{{ $first->name }}</span>
                                <span class="podium-score text-lg font-bold" style="color: #fbbf24;">{{ $first->total_activities }} Kegiatan</span>
                                <div class="podium-meta">
                                    <span>PIC: {{ $first->pic_count }}</span>
                                    <span>Peserta: {{ $first->participant_count }}</span>
                                </div>
                            </div>
                            <div class="podium-pedestal p-1">
                                <div class="pedestal-number">1</div>
                            </div>
                        </div>
                    @endif

                    <!-- 3rd Place (Bronze) -->
                    @if($topPeople->count() > 2)
                        @php $third = $topPeople[2]; @endphp
                        <div class="podium-step step-3">
                            <div class="podium-avatar bronze">
                                <i class="fa-solid fa-medal icon-bronze"></i>
                                <span class="rank-badge">3</span>
                            </div>
                            <div class="podium-info">
                                <span class="podium-name">{{ $third->name }}</span>
                                <span class="podium-score">{{ $third->total_activities }} Kegiatan</span>
                                <div class="podium-meta">
                                    <span>PIC: {{ $third->pic_count }}</span>
                                    <span>Peserta: {{ $third->participant_count }}</span>
                                </div>
                            </div>
                            <div class="podium-pedestal p-3">
                                <div class="pedestal-number">3</div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="empty-leaderboard text-center p-8 text-muted" style="padding: 40px; text-align: center;">
                    <i class="fa-solid fa-trophy" style="font-size: 3rem; opacity: 0.3; margin-bottom: 12px; display: inline-block;"></i>
                    <p style="font-size: 1rem; margin-top: 10px;">Tidak ada kegiatan tercatat pada rentang waktu ini.</p>
                </div>
            @endif
        </div>

        <!-- Tables section -->
        <div class="details-grid">
            <!-- Table 1: All People (PIC & Participants) -->
            <div class="details-card card-span-2">
                <div class="details-header">
                    <h3><i class="fa-solid fa-address-book"></i> Daftar Keterlibatan Anggota</h3>
                    <span class="badge">{{ $allPeople->total() }} Orang</span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Anggota</th>
                                <th>Sebagai PIC</th>
                                <th>Sebagai Peserta</th>
                                <th>Total Kegiatan</th>
                                <th>Terdaftar Pada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allPeople as $person)
                                <tr>
                                    <td class="font-semibold">{{ $person->name }}</td>
                                    <td><span class="badge badge-pic">{{ $person->pic_count }} kali</span></td>
                                    <td><span class="badge badge-participant">{{ $person->participant_count }} kali</span></td>
                                    <td><span class="badge badge-total">{{ $person->total_count }} kegiatan</span></td>
                                    <td class="text-muted text-sm">{{ \Carbon\Carbon::parse($person->created_at)->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data anggota.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pagination-wrapper">
                    {{ $allPeople->links() }}
                </div>
            </div>

            <!-- Table 2: Recent Activity Logs -->
            <div class="details-card">
                <div class="details-header">
                    <h3><i class="fa-solid fa-history"></i> Log Kegiatan Terbaru</h3>
                </div>
                <div class="activity-logs-container">
                    @forelse($recentActivities as $act)
                        <div class="log-item">
                            <div class="log-badge-dot theme-{{ $categoryColors[$act->category] ?? 'grey' }}"></div>
                            <div class="log-info">
                                <span class="log-category">{{ $act->category }}</span>
                                <p class="log-desc">{{ Str::limit($act->description, 50) }}</p>
                                <div class="log-meta">
                                    <span><i class="fa-solid fa-calendar-day"></i> {{ $act->activity_date->format('d M Y') }}</span>
                                    <span><i class="fa-solid fa-user-tie"></i> PIC: {{ $act->pics->pluck('name')->implode(', ') ?: '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted p-4">Belum ada kegiatan terbaru.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: SETTINGS & REPORTS -->
    <div id="tab-settings" class="tab-content">
        <!-- EXPORT EXCEL SECTION -->
        <div class="details-card animate-scale-up" style="margin-bottom: 24px; padding: 20px;">
            <div class="details-header" style="margin-bottom: 16px;">
                <h3><i class="fa-solid fa-file-excel text-primary"></i> Ekspor Laporan Kegiatan ke Excel</h3>
                <span class="chart-subtitle">Unduh rekapitulasi seluruh kegiatan dalam format Excel (.csv)</span>
            </div>
            <form action="{{ route('admin.activities.export') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
                <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                    <label style="margin-bottom: 6px;">Ekspor Berdasarkan Bulan</label>
                    <div style="display: flex; gap: 8px;">
                        <select name="month" style="padding: 8px 12px; width: 100%;">
                            <option value="">Pilih Bulan</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <select name="year" style="padding: 8px 12px; width: 100%;">
                            <option value="">Pilih Tahun</option>
                            @for($y = date('Y') - 5; $y <= date('Y') + 5; $y++)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; justify-content: center; height: 40px; color: var(--text-muted); font-weight: 600;">ATAU</div>

                <div class="form-group" style="flex: 1; min-width: 320px; margin-bottom: 0;">
                    <label style="margin-bottom: 6px;">Ekspor Berdasarkan Rentang Tanggal</label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="date" name="start_date" style="padding: 8px 12px;">
                        <span style="color: var(--text-muted);">s.d.</span>
                        <input type="date" name="end_date" style="padding: 8px 12px;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="height: 42px; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                    <i class="fa-solid fa-download"></i> Ekspor Sekarang
                </button>
            </form>
        </div>

        <!-- CATEGORY MANAGEMENT SECTION -->
        <div class="details-grid animate-scale-up">
            <div class="details-card card-span-2">
                <div class="details-header">
                    <h3><i class="fa-solid fa-list-check"></i> Manajemen Kategori Kegiatan</h3>
                    <span class="badge">{{ $categories->count() }} Kategori</span>
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success" style="padding: 10px; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger" style="padding: 10px; background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); color: var(--error); border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
                    </div>
                @endif

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Representasi Warna</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td class="font-semibold">{{ $category->name }}</td>
                                    <td>
                                        <span style="display: inline-flex; align-items: center; gap: 8px;">
                                            <span class="category-badge-dot theme-{{ $category->color }}" style="width: 12px; height: 12px;"></span>
                                            <span style="text-transform: capitalize; font-size: 0.85rem; color: var(--text-secondary);">{{ $category->color }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm" style="color: var(--error); border-color: rgba(239, 68, 68, 0.2); padding: 4px 8px;">
                                                <i class="fa-regular fa-trash-can"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="details-card">
                <div class="details-header">
                    <h3><i class="fa-solid fa-plus-circle"></i> Tambah Kategori Baru</h3>
                </div>
                <form action="{{ route('admin.categories.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
                    @csrf
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="category_name" style="margin-bottom: 6px;">Nama Kategori</label>
                        <input type="text" id="category_name" name="name" required placeholder="Contoh: Pelatihan, Webinar, dll..." style="padding: 10px 14px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="category_color" style="margin-bottom: 6px;">Pilihan Warna</label>
                        <select id="category_color" name="color" required style="padding: 10px 14px;">
                            <option value="" disabled selected>Pilih Warna</option>
                            <option value="purple">Ungu (Purple)</option>
                            <option value="blue">Biru (Blue)</option>
                            <option value="green">Hijau (Green)</option>
                            <option value="orange">Oranye (Orange)</option>
                            <option value="red">Merah (Red)</option>
                            <option value="cyan">Sian (Cyan)</option>
                            <option value="grey">Abu-abu (Grey)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="font-weight: 600; padding: 12px;">
                        <i class="fa-solid fa-save"></i> Simpan Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB 4: USER MANAGEMENT -->
    <div id="tab-users" class="tab-content">
        <div class="details-grid animate-scale-up">
            <!-- Table: All Users -->
            <div class="details-card card-span-2">
                <div class="details-header">
                    <h3><i class="fa-solid fa-users-gear"></i> Daftar Pengguna Sistem</h3>
                    <span class="badge">{{ $users->total() }} User</span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Pengguna</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $u)
                                <tr>
                                    <td class="font-semibold">{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td>
                                        @if($u->is_admin)
                                            <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: var(--color-rapat); border: 1px solid rgba(139, 92, 246, 0.3);">Administrator</span>
                                        @else
                                            <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-secondary); border: 1px solid var(--card-border);">User Biasa</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <button type="button" class="btn btn-outline btn-sm" onclick="openEditUserModal({{ json_encode($u) }})" style="padding: 4px 8px;">
                                                <i class="fa-regular fa-edit"></i> Edit
                                            </button>
                                            @if(Auth::id() !== $u->id)
                                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--error); border-color: rgba(239, 68, 68, 0.2); padding: 4px 8px;">
                                                        <i class="fa-regular fa-trash-can"></i> Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada user terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pagination-wrapper">
                    {{ $users->links() }}
                </div>
            </div>

            <!-- Form Add User -->
            <div class="details-card">
                <div class="details-header">
                    <h3><i class="fa-solid fa-user-plus"></i> Tambah User Baru</h3>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
                    @csrf
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="user_name" style="margin-bottom: 6px;">Nama Lengkap</label>
                        <input type="text" id="user_name" name="name" required placeholder="Nama Lengkap..." style="padding: 10px 14px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="user_email" style="margin-bottom: 6px;">Alamat Email</label>
                        <input type="email" id="user_email" name="email" required placeholder="nama@email.com" style="padding: 10px 14px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="user_password" style="margin-bottom: 6px;">Password</label>
                        <input type="password" id="user_password" name="password" required placeholder="Minimal 8 karakter..." style="padding: 10px 14px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="user_is_admin" name="is_admin" value="1" style="width: auto; margin-right: 6px; cursor: pointer;">
                        <label for="user_is_admin" style="margin-bottom: 0; cursor: pointer;">Jadikan Administrator (Superadmin)</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="font-weight: 600; padding: 12px;">
                        <i class="fa-solid fa-save"></i> Simpan User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: EDIT USER -->
<div id="editUserModal" class="modal-overlay" onclick="closeEditUserModal(event)">
    <div class="modal-card animate-scale-up" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="modal-close" onclick="document.getElementById('editUserModal').classList.remove('active')">&times;</button>
        </div>
        <form id="editUserForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_user_name">Nama Lengkap</label>
                    <input type="text" id="edit_user_name" name="name" required style="padding: 10px 14px;">
                </div>

                <div class="form-group">
                    <label for="edit_user_email">Alamat Email</label>
                    <input type="email" id="edit_user_email" name="email" required style="padding: 10px 14px;">
                </div>

                <div class="form-group">
                    <label for="edit_user_password">Password Baru <span class="text-muted">(Kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" id="edit_user_password" name="password" placeholder="Minimal 8 karakter..." style="padding: 10px 14px;">
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 0;">
                    <input type="checkbox" id="edit_user_is_admin" name="is_admin" value="1" style="width: auto; margin-right: 6px; cursor: pointer;">
                    <label for="edit_user_is_admin" style="margin-bottom: 0; cursor: pointer;">Jadikan Administrator (Superadmin)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editUserModal').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Setup shared colors matching our vanilla CSS colors
        const colors = {
            purple: 'rgba(139, 92, 246, 0.75)',
            purpleBorder: 'rgba(139, 92, 246, 1)',
            blue: 'rgba(59, 130, 246, 0.75)',
            blueBorder: 'rgba(59, 130, 246, 1)',
            green: 'rgba(16, 185, 129, 0.75)',
            greenBorder: 'rgba(16, 185, 129, 1)',
            orange: 'rgba(249, 115, 22, 0.75)',
            orangeBorder: 'rgba(249, 115, 22, 1)',
            red: 'rgba(239, 68, 68, 0.75)',
            redBorder: 'rgba(239, 68, 68, 1)',
            cyan: 'rgba(6, 182, 212, 0.75)',
            cyanBorder: 'rgba(6, 182, 212, 1)',
            gridLines: 'rgba(255, 255, 255, 0.05)',
            text: '#e2e8f0'
        };

        // Chart.js global defaults
        Chart.defaults.color = colors.text;
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";



        // =============================================
        // 2. CATEGORIES SHARE CHART (Doughnut)
        // =============================================
        const categoriesData = {!! json_encode($categoriesShare) !!};
        const catLabels = categoriesData.map(item => item.category);
        const catCounts = categoriesData.map(item => item.count);
        
        // Dynamic color mapper
        const categoryColorsMap = {
            @foreach($categoryColors as $name => $color)
                '{{ strtolower($name) }}': colors.{{ $color }} || colors.cyan,
            @endforeach
        };
        const categoryBordersMap = {
            @foreach($categoryColors as $name => $color)
                '{{ strtolower($name) }}': colors.{{ $color }}Border || colors.cyanBorder,
            @endforeach
        };

        const doughnutBgColors = catLabels.map(label => categoryColorsMap[label.toLowerCase()] || 'rgba(156, 163, 175, 0.7)');
        const doughnutBorderColors = catLabels.map(label => categoryBordersMap[label.toLowerCase()] || 'rgba(156, 163, 175, 1)');

        const ctxCategories = document.getElementById('categoriesShareChart').getContext('2d');
        new Chart(ctxCategories, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catCounts,
                    backgroundColor: doughnutBgColors,
                    borderColor: doughnutBorderColors,
                    borderWidth: 1.5,
                    hoverOffset: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                },
                cutout: '65%'
            }
        });

        // =============================================
        // 3. MONTHLY TREND CHART (Line)
        // =============================================
        const trendData = {!! json_encode($monthlyTrend) !!};
        const trendLabels = trendData.map(item => item.label);
        const trendCounts = trendData.map(item => item.count);

        const ctxTrend = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Jumlah Kegiatan',
                    data: trendCounts,
                    fill: true,
                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                    borderColor: colors.blueBorder,
                    borderWidth: 3,
                    tension: 0.4, // Curved lines
                    pointBackgroundColor: colors.blueBorder,
                    pointHoverRadius: 8,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { color: colors.gridLines }
                    },
                    y: {
                        grid: { color: colors.gridLines },
                        ticks: { precision: 0 },
                        beginAtZero: true
                    }
                }
            }
        });

        // Tab switcher logic
        window.switchTab = function(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            const activeContent = document.getElementById('tab-' + tabId);
            if (activeContent) {
                activeContent.classList.add('active');
            }
            
            const activeBtn = document.querySelector(`[onclick="switchTab('${tabId}')"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }
            
            localStorage.setItem('active_dashboard_tab', tabId);
        };

        // Restore tab from localStorage on page load
        const storedTab = localStorage.getItem('active_dashboard_tab') || 'analytics';
        window.switchTab(storedTab);
    });

    window.openEditUserModal = function(user) {
        const modal = document.getElementById('editUserModal');
        const form = document.getElementById('editUserForm');
        
        form.action = `/admin/users/${user.id}`;
        document.getElementById('edit_user_name').value = user.name;
        document.getElementById('edit_user_email').value = user.email;
        document.getElementById('edit_user_password').value = '';
        document.getElementById('edit_user_is_admin').checked = user.is_admin === 1 || user.is_admin === true || user.is_admin === '1' || user.is_admin === 'true';
        
        modal.classList.add('active');
    };

    window.closeEditUserModal = function(event) {
        if (event.target.id === 'editUserModal') {
            document.getElementById('editUserModal').classList.remove('active');
        }
    };
</script>
@endsection
