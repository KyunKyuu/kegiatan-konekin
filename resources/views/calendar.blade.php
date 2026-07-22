@extends('layouts.layout')

@section('title', 'Kalender Kegiatan - Google Calendar Clone')

@section('styles')
<style>
    .active-day-title-wrapper h2 {
        font-family: var(--font-display);
        color: #fff;
    }
    
    .calendar-day-view-container {
        background: #0d121f;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
    }

    .all-day-banner {
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid var(--card-border);
        padding: 16px 24px;
    }

    .all-day-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .all-day-events-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .agenda-item-row.compact {
        padding: 8px 12px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--card-border);
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        font-size: 0.85rem;
        transition: background 0.2s;
    }

    .agenda-item-row.compact:hover {
        background: rgba(255, 255, 255, 0.06);
    }

    .agenda-item-title-text {
        flex: 1;
        color: var(--text-primary);
    }

    .agenda-item-people-mini {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    /* Time Grid Layout */
    .time-grid-container {
        background: #090c14;
        max-height: 700px;
        overflow-y: auto;
    }

    .time-timeline {
        display: flex;
        flex-direction: column;
    }

    .time-row {
        display: flex;
        border-bottom: 1px solid var(--card-border);
        min-height: 90px;
    }

    .time-row:last-child {
        border-bottom: none;
    }

    .time-label {
        width: 90px;
        padding: 16px;
        text-align: right;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        border-right: 1px solid var(--card-border);
        background: rgba(255, 255, 255, 0.01);
        user-select: none;
    }

    .time-slot-content {
        flex: 1;
        position: relative;
        padding: 10px 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s;
    }

    .time-slot-content:hover {
        background: rgba(255, 255, 255, 0.015);
    }

    .slot-activities {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
        z-index: 2;
    }

    .day-activity-card {
        padding: 12px 16px;
        border-radius: 8px;
        border-left: 4px solid;
        cursor: pointer;
        transition: transform 0.2s, filter 0.2s;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .day-activity-card:hover {
        transform: translateY(-1px);
        filter: brightness(1.08);
    }

    .day-activity-card.rapat { background: var(--bg-rapat); border-left-color: var(--color-rapat); color: var(--color-rapat); }
    .day-activity-card.kerja\ bakti { background: var(--bg-kerja-bakti); border-left-color: var(--color-kerja-bakti); color: var(--color-kerja-bakti); }
    .day-activity-card.sosialisasi { background: var(--bg-sosialisasi); border-left-color: var(--color-sosialisasi); color: var(--color-sosialisasi); }
    .day-activity-card.seminar { background: var(--bg-seminar); border-left-color: var(--color-seminar); color: var(--color-seminar); }
    .day-activity-card.evaluasi { background: var(--bg-evaluasi); border-left-color: var(--color-evaluasi); color: var(--color-evaluasi); }
    .day-activity-card.outing { background: var(--bg-outing); border-left-color: var(--color-outing); color: var(--color-outing); }

    .card-time-badge {
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .card-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .card-meta {
        font-size: 0.75rem;
        color: var(--text-secondary);
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .empty-slot-hint {
        opacity: 0;
        color: var(--text-muted);
        font-size: 0.8rem;
        transition: opacity 0.2s;
        pointer-events: none;
    }

    .time-slot-content:hover .empty-slot-hint {
        opacity: 1;
    }

    .hint-text {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Style for option tags in select dropdowns */
    select option {
        background-color: #111827 !important;
        color: var(--text-primary) !important;
        font-family: var(--font-primary) !important;
        font-size: 0.95rem !important;
    }

    /* Style for time inputs to match the dark theme */
    input[type="time"] {
        width: 100% !important;
        padding: 12px 16px !important;
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid var(--card-border) !important;
        border-radius: 8px !important;
        color: var(--text-primary) !important;
        font-family: var(--font-primary) !important;
        font-size: 0.95rem !important;
        outline: none !important;
        color-scheme: dark !important;
        transition: all 0.25s ease !important;
    }

    input[type="time"]:focus {
        border-color: var(--primary) !important;
        background: rgba(255, 255, 255, 0.05) !important;
        box-shadow: 0 0 0 3px var(--primary-glow) !important;
    }

    input[type="time"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }

    /* Mobile Responsive Sidebar Drawer & FAB */
    .sidebar-mobile-header {
        display: none;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--card-border);
    }
    
    .sidebar-mobile-header h3 {
        margin: 0;
        font-size: 1.2rem;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-close-sidebar {
        background: transparent;
        border: none;
        color: var(--text-secondary);
        font-size: 2rem;
        cursor: pointer;
        line-height: 1;
        transition: color 0.2s;
    }
    
    .btn-close-sidebar:hover {
        color: #fff;
    }

    .filter-toggle-mobile {
        display: none;
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(3, 7, 18, 0.6);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 998;
        animation: fadeIn 0.2s ease-out;
    }

    .btn-fab-mobile {
        display: none;
    }

    @media (max-width: 768px) {
        .calendar-layout {
            grid-template-columns: 1fr !important;
        }

        .calendar-sidebar {
            position: fixed !important;
            top: 0;
            left: -320px;
            width: 300px;
            height: 100vh;
            background: #090d16 !important;
            border-right: 1px solid var(--card-border);
            z-index: 999 !important;
            padding: 24px !important;
            box-shadow: 25px 0 50px -12px rgba(0, 0, 0, 0.8) !important;
            transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            overflow-y: auto;
            display: flex !important;
            flex-direction: column;
        }

        .calendar-sidebar.active {
            left: 0 !important;
        }

        .sidebar-mobile-header {
            display: flex !important;
        }

        .filter-toggle-mobile {
            display: inline-flex !important;
            align-items: center;
            gap: 8px;
        }

        .controls-mobile-row {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            gap: 12px;
        }

        .btn-fab-mobile {
            display: flex !important;
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 25px rgba(124, 58, 237, 0.4);
            z-index: 99;
            border: none;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
        }

        .btn-fab-mobile:active {
            transform: scale(0.9);
        }
    }

    /* Category Color Themes */
    .theme-purple { --theme-color: #a78bfa; --theme-bg: rgba(167, 139, 250, 0.12); }
    .theme-blue { --theme-color: #60a5fa; --theme-bg: rgba(96, 165, 250, 0.12); }
    .theme-green { --theme-color: #34d399; --theme-bg: rgba(52, 211, 153, 0.12); }
    .theme-orange { --theme-color: #fb923c; --theme-bg: rgba(251, 146, 60, 0.12); }
    .theme-red { --theme-color: #f87171; --theme-bg: rgba(248, 113, 113, 0.12); }
    .theme-cyan { --theme-color: #22d3ee; --theme-bg: rgba(34, 211, 238, 0.12); }
    .theme-grey { --theme-color: #94a3b8; --theme-bg: rgba(148, 163, 184, 0.12); }

    /* Theme mapping overrides for component styling */
    .day-activity-card.theme-purple, .day-activity-card.theme-blue, .day-activity-card.theme-green, .day-activity-card.theme-orange, .day-activity-card.theme-red, .day-activity-card.theme-cyan, .day-activity-card.theme-grey {
        background: var(--theme-bg) !important;
        border-left-color: var(--theme-color) !important;
        color: var(--theme-color) !important;
    }

    .agenda-item-category.theme-purple, .agenda-item-category.theme-blue, .agenda-item-category.theme-green, .agenda-item-category.theme-orange, .agenda-item-category.theme-red, .agenda-item-category.theme-cyan, .agenda-item-category.theme-grey {
        background-color: var(--theme-bg) !important;
        color: var(--theme-color) !important;
        border-left-color: var(--theme-color) !important;
    }

    .activity-badge-item.theme-purple, .activity-badge-item.theme-blue, .activity-badge-item.theme-green, .activity-badge-item.theme-orange, .activity-badge-item.theme-red, .activity-badge-item.theme-cyan, .activity-badge-item.theme-grey {
        background-color: var(--theme-bg) !important;
        color: var(--theme-color) !important;
        border-left-color: var(--theme-color) !important;
    }

    .category-badge-dot.theme-purple, .category-badge-dot.theme-blue, .category-badge-dot.theme-green, .category-badge-dot.theme-orange, .category-badge-dot.theme-red, .category-badge-dot.theme-cyan, .category-badge-dot.theme-grey {
        background-color: var(--theme-color) !important;
    }

    /* Lock Overlay styles */
    .calendar-views-wrapper {
        position: relative;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
    }

    .calendar-lock-overlay {
        position: absolute;
        inset: 0;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(9, 13, 22, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        padding: 24px;
    }

    .lock-card {
        background: rgba(17, 25, 40, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 32px;
        max-width: 400px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(16px);
        animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .lock-icon-wrapper {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(139, 92, 246, 0.1);
        color: var(--primary);
        border: 1px solid rgba(139, 92, 246, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 20px;
        filter: drop-shadow(0 0 8px var(--primary-glow));
    }

    .lock-card h3 {
        font-size: 1.3rem;
        color: #fff;
        margin-bottom: 12px;
        font-family: var(--font-display);
    }

    .lock-card p {
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.5;
        margin-bottom: 24px;
    }

    .lock-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .calendar-blurred {
        filter: blur(6px);
        pointer-events: none;
        user-select: none;
    }
</style>
@endsection

@section('content')
<div class="calendar-layout">
    
    <!-- Sidebar Filter & Controls -->
    <aside class="calendar-sidebar">
        <!-- Mobile Header (Visible only on mobile drawer) -->
        <div class="sidebar-mobile-header">
            <h3><i class="fa-solid fa-filter"></i> Saring Kegiatan</h3>
            <button type="button" class="btn-close-sidebar" onclick="toggleMobileSidebar()">&times;</button>
        </div>

        @auth
            <button class="btn btn-primary btn-block btn-lg btn-add-event" onclick="openAddModal()">
                <i class="fa-solid fa-plus animate-bounce"></i> Tambah Kegiatan
            </button>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-block btn-lg btn-add-event">
                <i class="fa-solid fa-right-to-bracket"></i> Login untuk Input
            </a>
        @endauth

        <!-- Filter Panel -->
        <div class="filter-card">
            <h3 class="filter-title"><i class="fa-solid fa-filter"></i> Saring Kegiatan</h3>
            
            <form action="{{ route('calendar') }}" method="GET" class="filter-form">
                <!-- Keep month, year, view, and date -->
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="date" value="{{ $activeDateStr }}">

                <div class="form-group">
                    <label for="search_input">Cari Isi Kegiatan</label>
                    <div class="search-input-wrapper">
                        <input type="text" id="search_input" name="search" value="{{ $search }}" placeholder="Ketik kata kunci...">
                        <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kategori Kegiatan</label>
                    <div class="categories-checkbox-list">
                        @foreach($allCategories as $cat)
                            <label class="checkbox-label">
                                <input type="checkbox" name="categories[]" value="{{ $cat }}" 
                                    {{ in_array($cat, $selectedCategories) ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <span class="custom-checkbox"></span>
                                <span class="category-badge-dot theme-{{ $categoryColors[$cat] ?? 'grey' }}"></span>
                                <span class="checkbox-text">{{ $cat }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if($search || !empty($selectedCategories))
                    <a href="{{ route('calendar', ['month' => $month, 'year' => $year, 'view' => $view, 'date' => $activeDateStr]) }}" class="btn btn-outline btn-block btn-sm">
                        <i class="fa-solid fa-rotate-left"></i> Atur Ulang Filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Info Card -->
        <div class="info-card">
            <h4><i class="fa-regular fa-lightbulb"></i> Petunjuk</h4>
            <ul>
                <li>Klik pada kegiatan di kalender/agenda untuk melihat detail.</li>
                @auth
                    <li>Klik sel tanggal kosong untuk menambah kegiatan baru.</li>
                @else
                    <li>Login untuk dapat menginput kegiatan baru.</li>
                @endauth
            </ul>
        </div>
    </aside>

    <!-- Main Calendar Grid -->
    <section class="calendar-container">
        <!-- Calendar Header Controls -->
        <div class="calendar-header-controls">
            <div class="calendar-month-selector">
                @if($view === 'day')
                    <div class="active-day-title-wrapper" style="margin-right: 15px;">
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #fff;">{{ $activeDate->translatedFormat('l, d F Y') }}</h2>
                    </div>
                @endif
                <form action="{{ route('calendar') }}" method="GET" class="jump-to-date-form">
                    @if($search)
                        <input type="hidden" name="search" value="{{ $search }}">
                    @endif
                    @foreach($selectedCategories as $cat)
                        <input type="hidden" name="categories[]" value="{{ $cat }}">
                    @endforeach
                    <input type="hidden" name="view" value="{{ $view }}">

                    @if($view === 'day')
                        <div class="inline-select-wrapper" style="display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.05); padding: 5px 12px; border-radius: 8px; border: 1px solid var(--card-border); cursor: pointer;">
                            <i class="fa-regular fa-calendar text-primary" style="font-size: 0.95rem;"></i>
                            <input type="date" name="date" value="{{ $activeDateStr }}" onchange="this.form.submit()" style="background: transparent; border: none; color: #fff; font-family: var(--font-display); font-weight: 700; font-size: 1.05rem; cursor: pointer; outline: none; padding: 0; width: 140px;">
                        </div>
                    @else
                        <input type="hidden" name="date" value="{{ $activeDateStr }}">
                        <div class="inline-select-wrapper">
                            <select name="month" onchange="this.form.submit()">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                            <select name="year" onchange="this.form.submit()">
                                @for($y = date('Y') - 10; $y <= date('Y') + 10; $y++)
                                    <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    @endif
                </form>
                <div class="calendar-nav-buttons">
                    @if($view === 'day')
                        @php
                            $prevDay = $activeDate->copy()->subDay()->format('Y-m-d');
                            $nextDay = $activeDate->copy()->addDay()->format('Y-m-d');
                        @endphp
                        <a href="{{ route('calendar', ['view' => 'day', 'date' => $prevDay, 'search' => $search, 'categories' => $selectedCategories]) }}" class="nav-btn" title="Hari Sebelumnya">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('calendar', ['view' => 'day', 'date' => date('Y-m-d'), 'search' => $search, 'categories' => $selectedCategories]) }}" class="btn btn-outline btn-sm">Hari Ini</a>
                        <a href="{{ route('calendar', ['view' => 'day', 'date' => $nextDay, 'search' => $search, 'categories' => $selectedCategories]) }}" class="nav-btn" title="Hari Berikutnya">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @else
                        <a href="{{ route('calendar', ['view' => $view, 'month' => $month - 1, 'year' => $year, 'search' => $search, 'categories' => $selectedCategories, 'date' => $activeDateStr]) }}" class="nav-btn" title="Bulan Sebelumnya">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('calendar', ['view' => $view, 'month' => date('n'), 'year' => date('Y'), 'date' => date('Y-m-d'), 'search' => $search, 'categories' => $selectedCategories]) }}" class="btn btn-outline btn-sm">Hari Ini</a>
                        <a href="{{ route('calendar', ['view' => $view, 'month' => $month + 1, 'year' => $year, 'search' => $search, 'categories' => $selectedCategories, 'date' => $activeDateStr]) }}" class="nav-btn" title="Bulan Berikutnya">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>
            
            <div class="controls-mobile-row">
                <button type="button" class="btn btn-outline filter-toggle-mobile" onclick="toggleMobileSidebar()">
                    <i class="fa-solid fa-filter"></i> Saring
                </button>
                <div class="view-toggle-buttons">
                    <a href="{{ route('calendar', ['view' => 'month', 'date' => $activeDateStr, 'search' => $search, 'categories' => $selectedCategories]) }}" id="btn-month-view" class="toggle-btn {{ $view === 'month' ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-days"></i> Bulanan
                    </a>
                    <a href="{{ route('calendar', ['view' => 'day', 'date' => $activeDateStr, 'search' => $search, 'categories' => $selectedCategories]) }}" id="btn-day-view" class="toggle-btn {{ $view === 'day' ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-day"></i> Harian
                    </a>
                    <a href="{{ route('calendar', ['view' => 'list', 'date' => $activeDateStr, 'search' => $search, 'categories' => $selectedCategories]) }}" id="btn-list-view" class="toggle-btn {{ $view === 'list' ? 'active' : '' }}">
                        <i class="fa-solid fa-list-ul"></i> Agenda
                    </a>
                </div>
            </div>
        </div>

        <div class="calendar-views-wrapper" style="position: relative;">
            @guest
                <div class="calendar-lock-overlay">
                    <div class="lock-card">
                        <div class="lock-icon-wrapper">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h3>Jadwal Terkunci</h3>
                        <p>Silakan masuk terlebih dahulu untuk melihat jadwal kegiatan secara lengkap.</p>
                        <div class="lock-actions">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-block">
                                <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            @endguest

            <div class="calendar-view-content @guest calendar-blurred @endguest">
                @if($view === 'month')
                    <!-- Month Grid Table -->
                    <div class="calendar-grid">
                        <!-- Day Names Headers -->
                        <div class="calendar-day-header sun">Min</div>
                        <div class="calendar-day-header">Sen</div>
                        <div class="calendar-day-header">Sel</div>
                        <div class="calendar-day-header">Rab</div>
                        <div class="calendar-day-header">Kam</div>
                        <div class="calendar-day-header">Jum</div>
                        <div class="calendar-day-header sat">Sab</div>

                        <!-- Calendar Days -->
                        @foreach($days as $day)
                            @php
                                $isWeekend = $day['date']->isWeekend();
                                $dayClass = '';
                                if (!$day['is_current_month']) $dayClass .= ' out-of-month';
                                if ($day['is_today']) $dayClass .= ' today';
                                if ($isWeekend) $dayClass .= ' weekend';
                            @endphp
                            <div class="calendar-day-cell{{ $dayClass }}" 
                                 @auth data-date="{{ $day['formatted'] }}" onclick="handleCellClick(event, '{{ $day['formatted'] }}')" @endauth>
                                
                                <div class="day-number-wrapper">
                                    <a href="{{ route('calendar', ['view' => 'day', 'date' => $day['formatted'], 'search' => $search, 'categories' => $selectedCategories]) }}" class="day-number" style="z-index: 10; cursor: pointer;">{{ $day['date']->day }}</a>
                                </div>

                                <div class="day-activities-list">
                                    @auth
                                        @foreach($day['activities']->take(4) as $activity)
                                            <div class="activity-badge-item theme-{{ $categoryColors[$activity->category] ?? 'grey' }}" 
                                                 onclick="showActivityDetail(event, {{ json_encode($activity) }}, {{ json_encode($activity->participants->pluck('name')) }}, {{ json_encode($activity->pics->pluck('name')) }}, {{ json_encode(Auth::check() && (Auth::id() === $activity->user_id || Auth::user()->is_admin)) }})">
                                                <span class="activity-category-indicator"></span>
                                                <span class="activity-text-preview">
                                                    @if($activity->start_time)
                                                        [{{ Carbon\Carbon::parse($activity->start_time)->format('H:i') }}]
                                                    @endif
                                                    {{ $activity->category }}: {{ Str::limit($activity->description, 20) }}
                                                </span>
                                            </div>
                                        @endforeach
                                        
                                        @if($day['activities']->count() > 4)
                                            <div class="activity-more-badge">
                                                +{{ $day['activities']->count() - 4 }} kegiatan lainnya
                                            </div>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        @endforeach
            </div>
        @endif

        @if($view === 'day')
            <!-- Day View Grid (Google Calendar style) -->
            <div class="calendar-day-view-container">
                
                <!-- All Day (Sepanjang Hari) Section -->
                @auth
                    @if($allDayActivities->count() > 0)
                        <div class="all-day-banner">
                            <div class="all-day-title"><i class="fa-solid fa-clock"></i> Sepanjang Hari (All Day)</div>
                            <div class="all-day-events-list">
                                @foreach($allDayActivities as $activity)
                                    <div class="agenda-item-row compact" onclick="showActivityDetail(event, {{ json_encode($activity) }}, {{ json_encode($activity->participants->pluck('name')) }}, {{ json_encode($activity->pics->pluck('name')) }}, {{ json_encode(Auth::check() && (Auth::id() === $activity->user_id || Auth::user()->is_admin)) }})">
                                        <span class="agenda-item-category theme-{{ $categoryColors[$activity->category] ?? 'grey' }}">
                                            {{ $activity->category }}
                                        </span>
                                        <span class="agenda-item-title-text">{{ Str::limit($activity->description, 80) }}</span>
                                        <span class="agenda-item-people-mini">
                                            <i class="fa-solid fa-user-tie"></i> PIC: {{ $activity->pics->pluck('name')->implode(', ') ?: '-' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endauth

                <!-- Time Grid Schedule -->
                <div class="time-grid-container">
                    @php
                        $startHour = 7;
                        $endHour = 22;
                    @endphp
                    
                    <div class="time-timeline">
                        @for($h = $startHour; $h <= $endHour; $h++)
                            @php
                                $timeStr = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                                // Get events that start within this hour slot
                                $slotEvents = $timedActivities->filter(function($act) use ($h) {
                                    $actHour = (int) explode(':', $act->start_time)[0];
                                    return $actHour === $h;
                                });
                            @endphp
                            <div class="time-row">
                                <div class="time-label">{{ $timeStr }}</div>
                                <div class="time-slot-content" 
                                     @auth 
                                         onclick="handleTimeSlotClick(event, '{{ $activeDateStr }}', '{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00')" 
                                     @endauth>
                                     
                                    @auth
                                        @if($slotEvents->count() > 0)
                                            <div class="slot-activities">
                                                @foreach($slotEvents as $activity)
                                                    @php
                                                        $st = Carbon\Carbon::parse($activity->start_time)->format('H:i');
                                                        $et = $activity->end_time ? Carbon\Carbon::parse($activity->end_time)->format('H:i') : '';
                                                    @endphp
                                                    <div class="day-activity-card theme-{{ $categoryColors[$activity->category] ?? 'grey' }}" 
                                                         onclick="showActivityDetail(event, {{ json_encode($activity) }}, {{ json_encode($activity->participants->pluck('name')) }}, {{ json_encode($activity->pics->pluck('name')) }}, {{ json_encode(Auth::check() && (Auth::id() === $activity->user_id || Auth::user()->is_admin)) }})">
                                                        <div class="card-time-badge"><i class="fa-solid fa-clock"></i> {{ $st }}{{ $et ? ' - ' . $et : '' }}</div>
                                                        <div class="card-title">{{ $activity->description }}</div>
                                                        <div class="card-meta">
                                                            <span><i class="fa-solid fa-user-tie"></i> PIC: {{ $activity->pics->pluck('name')->implode(', ') ?: '-' }}</span>
                                                            <span><i class="fa-solid fa-users"></i> Terlibat: {{ $activity->participants->pluck('name')->implode(', ') ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="empty-slot-hint">
                                                <span class="hint-text"><i class="fa-solid fa-plus animate-pulse"></i> Tambah Kegiatan Jam {{ $timeStr }}</span>
                                            </div>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

            </div>
        @endif

        @if($view === 'list')
            <!-- Agenda List View -->
            <div class="calendar-agenda-view" id="agenda-view" style="display: block;">
                @php
                    $hasActivities = false;
                @endphp

                @auth
                    @foreach($days as $day)
                        @if($day['is_current_month'] && $day['activities']->count() > 0)
                            @php $hasActivities = true; @endphp
                            <div class="agenda-day-group">
                                <div class="agenda-day-header">
                                    <i class="fa-solid fa-calendar-day text-primary"></i> 
                                    <a href="{{ route('calendar', ['view' => 'day', 'date' => $day['formatted'], 'search' => $search, 'categories' => $selectedCategories]) }}" style="color: inherit; font-weight: inherit;">
                                        {{ $day['date']->translatedFormat('l, d F Y') }}
                                    </a>
                                    @if($day['is_today'])
                                        <span class="badge" style="background: var(--primary); color: #fff; margin-left: 10px;">Hari Ini</span>
                                    @endif
                                </div>
                                <div class="agenda-items-list">
                                    @foreach($day['activities'] as $activity)
                                        <div class="agenda-item-row" onclick="showActivityDetail(event, {{ json_encode($activity) }}, {{ json_encode($activity->participants->pluck('name')) }}, {{ json_encode($activity->pics->pluck('name')) }}, {{ json_encode(Auth::check() && (Auth::id() === $activity->user_id || Auth::user()->is_admin)) }})">
                                            <div class="agenda-item-left">
                                                <span class="agenda-item-category theme-{{ $categoryColors[$activity->category] ?? 'grey' }}">
                                                    {{ $activity->category }}
                                                </span>
                                                <div class="agenda-item-content">
                                                    <div class="agenda-item-title">
                                                        @if($activity->start_time)
                                                            <span class="text-primary font-semibold" style="margin-right: 8px;">
                                                                <i class="fa-solid fa-clock"></i> {{ Carbon\Carbon::parse($activity->start_time)->format('H:i') }}{{ $activity->end_time ? ' - ' . Carbon\Carbon::parse($activity->end_time)->format('H:i') : '' }}
                                                            </span>
                                                        @endif
                                                        {{ Str::limit($activity->description, 80) }}
                                                    </div>
                                                    <div class="agenda-item-people">
                                                        <span><i class="fa-solid fa-user-tie"></i> PIC: {{ $activity->pics->pluck('name')->implode(', ') ?: '-' }}</span>
                                                        <span><i class="fa-solid fa-users"></i> Terlibat: {{ $activity->participants->pluck('name')->implode(', ') ?: '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="agenda-item-right">
                                                <span class="badge badge-total" style="opacity: 0.6;"><i class="fa-regular fa-eye"></i> Detail</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if(!$hasActivities)
                        <div class="agenda-no-activities">
                            <i class="fa-regular fa-calendar-times"></i>
                            <p>Tidak ada kegiatan terjadwal untuk bulan ini.</p>
                            <span class="text-muted text-sm">Gunakan tombol "Tambah Kegiatan" di sidebar untuk memulai.</span>
                        </div>
                    @endif
                @else
                    <div class="agenda-no-activities">
                        <i class="fa-solid fa-lock"></i>
                        <p>Jadwal Terkunci</p>
                        <span class="text-muted text-sm">Silakan login untuk melihat daftar kegiatan.</span>
                    </div>
                @endauth
            </div>
        @endif
            </div>
        </div>
    </section>
</div>

<!-- Backdrop Overlay for Mobile Sidebar Drawer -->
<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleMobileSidebar()" style="display: none;"></div>

<!-- Floating Action Button (FAB) for Mobile -->
@auth
    <button class="btn-fab-mobile" onclick="openAddModal()" title="Tambah Kegiatan">
        <i class="fa-solid fa-plus"></i>
    </button>
@else
    <a href="{{ route('login') }}" class="btn-fab-mobile" title="Login untuk Input">
        <i class="fa-solid fa-plus"></i>
    </a>
@endauth

<!-- ============================================= -->
<!-- MODAL: DETAIL KEGIATAN -->
<!-- ============================================= -->
<div id="detailModal" class="modal-overlay" onclick="closeDetailModal(event)">
    <div class="modal-card modal-detail animate-fade-in">
        <div class="modal-header">
            <span class="modal-badge-category" id="detail-category">Rapat</span>
            <button class="modal-close" onclick="document.getElementById('detailModal').classList.remove('active')">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="detail-section">
                <div class="detail-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="detail-content">
                    <label>Tanggal Kegiatan</label>
                    <p id="detail-date">-</p>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-icon"><i class="fa-solid fa-align-left"></i></div>
                <div class="detail-content">
                    <label>Isi Kegiatan</label>
                    <p id="detail-description" class="detail-desc-text">-</p>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-icon"><i class="fa-solid fa-user-tie"></i></div>
                <div class="detail-content">
                    <label>PIC Kegiatan</label>
                    <div id="detail-pics" class="participants-tags-list">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-icon"><i class="fa-solid fa-users"></i></div>
                <div class="detail-content">
                    <label>Yang Terlibat (Peserta)</label>
                    <div id="detail-participants" class="participants-tags-list">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>
            
            <div class="detail-section creator-section">
                <div class="detail-icon"><i class="fa-solid fa-circle-user"></i></div>
                <div class="detail-content">
                    <label>Diinput oleh</label>
                    <p id="detail-creator" class="creator-name">-</p>
                </div>
            </div>
        </div>

        <div class="modal-footer" id="detail-actions">
            <!-- Edit / Delete Buttons populated by JS if authorized -->
        </div>
    </div>
</div>

@auth
<!-- ============================================= -->
<!-- MODAL: TAMBAH / EDIT KEGIATAN -->
<!-- ============================================= -->
<div id="activityModal" class="modal-overlay" onclick="closeActivityModal(event)">
    <div class="modal-card animate-scale-up">
        <div class="modal-header">
            <h3 id="modal-title">Tambah Kegiatan Baru</h3>
            <button class="modal-close" onclick="document.getElementById('activityModal').classList.remove('active')">&times;</button>
        </div>
        
        <form id="activityForm" action="{{ route('activities.store') }}" method="POST">
            @csrf
            <!-- Edit Mode Method spoofing & ID -->
            <input type="hidden" id="form-method" name="_method" value="POST">
            <input type="hidden" id="form-activity-id" name="id" value="">

            <div class="modal-body">
                <div class="row-2">
                    <div class="form-group">
                        <label for="activity_date">Tanggal Kegiatan <span class="text-danger">*</span></label>
                        <input type="date" id="activity_date" name="activity_date" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Kategori Kegiatan <span class="text-danger">*</span></label>
                        <select id="category" name="category" required>
                            <option value="" disabled selected>Pilih Kategori</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row-2">
                    <div class="form-group">
                        <label for="start_time">Jam Mulai <span class="text-muted">(Kosongkan jika sepanjang hari)</span></label>
                        <input type="time" id="start_time" name="start_time" list="time_suggestions">
                    </div>

                    <div class="form-group">
                        <label for="end_time">Jam Selesai <span class="text-muted">(Harus setelah jam mulai)</span></label>
                        <input type="time" id="end_time" name="end_time" list="time_suggestions">
                    </div>

                    <datalist id="time_suggestions">
                        @for($h = 0; $h < 24; $h++)
                            @for($m = 0; $m < 60; $m += 15)
                                @php
                                    $timeVal = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
                                @endphp
                                <option value="{{ $timeVal }}"></option>
                            @endfor
                        @endfor
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="description">Isi Kegiatan <span class="text-danger">*</span></label>
                    <textarea id="description" name="description" rows="3" placeholder="Tulis rincian kegiatan..." required></textarea>
                </div>

                <!-- Autocomplete PIC -->
                <div class="form-group relative">
                    <label for="pic_search">PIC Kegiatan <span class="text-danger">* (minimal 1 orang)</span></label>
                    <div class="input-wrapper">
                        <input type="text" id="pic_search" placeholder="Cari nama PIC & tekan Enter atau pilih..." autocomplete="off">
                    </div>
                    <ul id="pic-suggestions" class="autocomplete-list"></ul>
                    
                    <!-- Selected PIC Tags Wrapper -->
                    <div id="selected-pics" class="selected-tags-container">
                        <!-- Filled by JS -->
                    </div>
                    
                    <!-- A hidden input used to validate PIC count -->
                    <input type="hidden" id="pics-validator" required name="has_pics" value="">
                </div>

                <!-- Autocomplete Participants ("Yang Terlibat") -->
                <div class="form-group relative">
                    <label for="participant_search">Yang Terlibat <span class="text-danger">* (minimal 1 orang)</span></label>
                    <div class="input-wrapper">
                        <input type="text" id="participant_search" placeholder="Cari nama peserta & tekan Enter atau pilih..." autocomplete="off">
                    </div>
                    <ul id="participant-suggestions" class="autocomplete-list"></ul>
                    
                    <!-- Selected Tags Wrapper -->
                    <div id="selected-participants" class="selected-tags-container">
                        <!-- Filled by JS -->
                    </div>
                    
                    <!-- A hidden input used to validate participant count -->
                    <input type="hidden" id="participants-validator" required name="has_participants" value="">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('activityModal').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary" id="btn-save-activity">Simpan Kegiatan</button>
            </div>
        </form>
    </div>
</div>

@endauth

@endsection

@section('scripts')
<script>
    // Show Details
    function showActivityDetail(event, activity, participantNames, picNames, canManage) {
        event.stopPropagation(); // Avoid triggering cell click
        
        document.getElementById('detail-category').innerText = activity.category;
        document.getElementById('detail-category').className = 'modal-badge-category ' + activity.category.toLowerCase();
        
        // Format Date and Time
        const dateObj = new Date(activity.activity_date);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        let dateText = dateObj.toLocaleDateString('id-ID', options);
        
        if (activity.start_time) {
            const st = activity.start_time.substring(0, 5);
            const et = activity.end_time ? activity.end_time.substring(0, 5) : '';
            dateText += ` (${st}${et ? ' - ' + et : ''})`;
        } else {
            dateText += ' (Sepanjang Hari)';
        }
        document.getElementById('detail-date').innerText = dateText;
        
        document.getElementById('detail-description').innerText = activity.description;
        
        // PIC Tags Display
        const picsContainer = document.getElementById('detail-pics');
        picsContainer.innerHTML = '';
        if (picNames && picNames.length > 0) {
            picNames.forEach(name => {
                const span = document.createElement('span');
                span.className = 'participant-tag';
                span.style.color = 'var(--color-seminar)';
                span.style.borderColor = 'rgba(59, 130, 246, 0.3)';
                span.style.backgroundColor = 'rgba(59, 130, 246, 0.08)';
                span.innerHTML = `<i class="fa-solid fa-user-tie"></i> ${name}`;
                picsContainer.appendChild(span);
            });
        } else {
            picsContainer.innerHTML = '<span class="text-muted text-sm">-</span>';
        }
        
        const creatorName = activity.creator ? activity.creator.name : 'Unknown';
        document.getElementById('detail-creator').innerText = creatorName;
        
        // Participants Tags Display
        const tagsContainer = document.getElementById('detail-participants');
        tagsContainer.innerHTML = '';
        if (participantNames && participantNames.length > 0) {
            participantNames.forEach(name => {
                const span = document.createElement('span');
                span.className = 'participant-tag';
                span.innerHTML = `<i class="fa-solid fa-user"></i> ${name}`;
                tagsContainer.appendChild(span);
            });
        } else {
            tagsContainer.innerHTML = '<span class="text-muted text-sm">Tidak ada yang terlibat</span>';
        }
        
        // Actions
        const actionsContainer = document.getElementById('detail-actions');
        actionsContainer.innerHTML = '';
        
        if (canManage) {
            actionsContainer.innerHTML = `
                <button class="btn btn-outline btn-sm text-danger" onclick="deleteActivity(${activity.id})">
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>
                <button class="btn btn-primary btn-sm" onclick="editActivity(${JSON.stringify(activity).replace(/"/g, '&quot;')}, ${JSON.stringify(participantNames).replace(/"/g, '&quot;')}, ${JSON.stringify(picNames).replace(/"/g, '&quot;')})">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </button>
            `;
        }
        
        document.getElementById('detailModal').classList.add('active');
    }

    function closeDetailModal(event) {
        if (event.target.id === 'detailModal') {
            document.getElementById('detailModal').classList.remove('active');
        }
    }

    @auth
    // Add & Edit Handler
    const activityModal = document.getElementById('activityModal');
    const activityForm = document.getElementById('activityForm');
    const modalTitle = document.getElementById('modal-title');
    const formMethod = document.getElementById('form-method');
    const formActivityId = document.getElementById('form-activity-id');
    const btnSaveActivity = document.getElementById('btn-save-activity');
    
    let selectedParticipants = [];
    let selectedPics = [];

    // Toast notification (replaces full-page flash messages for AJAX actions)
    function showToast(message, type = 'success') {
        const existing = document.querySelector('.ajax-toast');
        if (existing) existing.remove();

        const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'error'} ajax-toast`;
        toast.innerHTML = `
            <i class="fa-solid ${icon}"></i>
            <span>${message}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        const container = document.querySelector('.main-container');
        container.insertBefore(toast, container.firstChild);
        setTimeout(() => toast.remove(), 4000);
    }

    // Re-fetch the current calendar page and swap in just the grid/agenda content,
    // so adding/editing/deleting an activity updates the view without a full reload.
    async function refreshCalendarView() {
        const response = await fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const html = await response.text();
        const newDoc = new DOMParser().parseFromString(html, 'text/html');
        const newContent = newDoc.querySelector('.calendar-view-content');
        const currentContent = document.querySelector('.calendar-view-content');
        if (newContent && currentContent) {
            currentContent.innerHTML = newContent.innerHTML;
        }
    }

    // Submit Add/Edit form via AJAX instead of a normal page POST
    activityForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(activityForm);
        const originalBtnText = btnSaveActivity.innerText;
        btnSaveActivity.disabled = true;
        btnSaveActivity.innerText = 'Menyimpan...';

        try {
            const response = await fetch(activityForm.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
            });
            const data = await response.json().catch(() => ({}));

            if (response.ok) {
                activityModal.classList.remove('active');
                await refreshCalendarView();
                showToast(data.message || 'Kegiatan berhasil disimpan!', 'success');
            } else if (response.status === 422 && data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                showToast(firstError, 'error');
            } else {
                showToast(data.message || 'Terjadi kesalahan, silakan coba lagi.', 'error');
            }
        } catch (err) {
            showToast('Gagal terhubung ke server.', 'error');
        } finally {
            btnSaveActivity.disabled = false;
            btnSaveActivity.innerText = originalBtnText;
        }
    });

    function openAddModal() {
        modalTitle.innerText = "Tambah Kegiatan Baru";
        activityForm.action = "{{ route('activities.store') }}";
        formMethod.value = "POST";
        formActivityId.value = "";
        
        // Reset form
        activityForm.reset();
        selectedPics = [];
        selectedParticipants = [];
        renderPics();
        renderParticipants();
        
        // Reset time inputs
        if (document.getElementById('start_time')) document.getElementById('start_time').value = '';
        if (document.getElementById('end_time')) document.getElementById('end_time').value = '';
        
        activityModal.classList.add('active');
    }

    function handleCellClick(event, dateString) {
        // Only trigger if clicking directly on the cell or number, not activity badges
        if (event.target.closest('.activity-badge-item') || event.target.closest('.activity-more-badge') || event.target.closest('.day-number')) {
            return;
        }
        openAddModal();
        document.getElementById('activity_date').value = dateString;
    }

    function handleTimeSlotClick(event, dateString, timeString) {
        // Only trigger if clicking directly on the slot, not on activity cards
        if (event.target.closest('.day-activity-card')) {
            return;
        }
        openAddModal();
        document.getElementById('activity_date').value = dateString;
        document.getElementById('start_time').value = timeString;
        
        // Auto fill end time to 1 hour later
        const [hour, min] = timeString.split(':');
        const endHour = parseInt(hour) + 1;
        const endHourStr = str_pad(endHour, 2, '0');
        document.getElementById('end_time').value = `${endHourStr}:${min}`;
    }

    function str_pad(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }

    function closeActivityModal(event) {
        if (event.target.id === 'activityModal') {
            activityModal.classList.remove('active');
        }
    }

    function editActivity(activity, participants, pics) {
        document.getElementById('detailModal').classList.remove('active');
        
        modalTitle.innerText = "Edit Kegiatan";
        activityForm.action = `/activities/${activity.id}`;
        formMethod.value = "POST";
        formActivityId.value = activity.id;
        
        document.getElementById('activity_date').value = activity.activity_date.split('T')[0];
        document.getElementById('category').value = activity.category;
        document.getElementById('description').value = activity.description;
        
        // Populate times
        if (document.getElementById('start_time')) {
            document.getElementById('start_time').value = activity.start_time ? activity.start_time.substring(0, 5) : '';
        }
        if (document.getElementById('end_time')) {
            document.getElementById('end_time').value = activity.end_time ? activity.end_time.substring(0, 5) : '';
        }
        
        // Populating PICs list
        selectedPics = pics.map(name => ({ id: null, name: name }));
        renderPics();
        
        // Populating participants list
        selectedParticipants = participants.map(name => ({ id: null, name: name }));
        renderParticipants();
        
        activityModal.classList.add('active');
    }

    async function deleteActivity(activityId) {
        if (!confirm("Apakah Anda yakin ingin menghapus kegiatan ini?")) return;

        try {
            const response = await fetch(`/activities/${activityId}/delete`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            const data = await response.json().catch(() => ({}));

            if (response.ok) {
                document.getElementById('detailModal').classList.remove('active');
                await refreshCalendarView();
                showToast(data.message || 'Kegiatan berhasil dihapus!', 'success');
            } else {
                showToast(data.message || 'Gagal menghapus kegiatan.', 'error');
            }
        } catch (err) {
            showToast('Gagal terhubung ke server.', 'error');
        }
    }

    // =============================================
    // AUTOCOMPLETE IMPLEMENTATION
    // =============================================
    
    // Autocomplete for PIC
    const picSearch = document.getElementById('pic_search');
    const picSuggestions = document.getElementById('pic-suggestions');

    picSearch.addEventListener('input', debounce(function() {
        const query = this.value.trim();
        if (query.length < 1) {
            picSuggestions.innerHTML = '';
            picSuggestions.classList.remove('active');
            return;
        }

        fetch(`/api/people/search?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                picSuggestions.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(person => {
                        // Skip if already added
                        if (selectedPics.some(p => p.name.toLowerCase() === person.name.toLowerCase())) {
                            return;
                        }
                        
                        const li = document.createElement('li');
                        li.className = 'autocomplete-item';
                        li.innerHTML = `<i class="fa-solid fa-user-tie text-primary"></i> <span>${person.name}</span> <small>(Tersimpan)</small>`;
                        li.onclick = function() {
                            addPic(person.name);
                            picSearch.value = '';
                            picSuggestions.innerHTML = '';
                            picSuggestions.classList.remove('active');
                        };
                        picSuggestions.appendChild(li);
                    });
                    
                    // Option to add new name anyway
                    const exists = selectedPics.some(p => p.name.toLowerCase() === query.toLowerCase());
                    if (!exists) {
                        const li = document.createElement('li');
                        li.className = 'autocomplete-item';
                        li.innerHTML = `<i class="fa-solid fa-plus text-primary"></i> Gunakan "${query}" sebagai data baru`;
                        li.onclick = function() {
                            addPic(query);
                            picSearch.value = '';
                            picSuggestions.innerHTML = '';
                            picSuggestions.classList.remove('active');
                        };
                        picSuggestions.appendChild(li);
                    }
                    
                    picSuggestions.classList.add('active');
                } else {
                    // Option to add new name
                    const exists = selectedPics.some(p => p.name.toLowerCase() === query.toLowerCase());
                    if (!exists) {
                        const li = document.createElement('li');
                        li.className = 'autocomplete-item';
                        li.innerHTML = `<i class="fa-solid fa-plus text-primary"></i> Gunakan "${query}" sebagai data baru`;
                        li.onclick = function() {
                            addPic(query);
                            picSearch.value = '';
                            picSuggestions.innerHTML = '';
                            picSuggestions.classList.remove('active');
                        };
                        picSuggestions.appendChild(li);
                        picSuggestions.classList.add('active');
                    } else {
                        picSuggestions.classList.remove('active');
                    }
                }
            });
    }, 200));

    // Handle Enter key on PIC search to add custom name
    picSearch.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = this.value.trim();
            if (val.length > 0) {
                const exists = selectedPics.some(p => p.name.toLowerCase() === val.toLowerCase());
                if (!exists) {
                    addPic(val);
                }
                this.value = '';
                picSuggestions.innerHTML = '';
                picSuggestions.classList.remove('active');
            }
        }
    });

    // Autocomplete for Participants
    const partSearch = document.getElementById('participant_search');
    const partSuggestions = document.getElementById('participant-suggestions');

    partSearch.addEventListener('input', debounce(function() {
        const query = this.value.trim();
        if (query.length < 1) {
            partSuggestions.innerHTML = '';
            partSuggestions.classList.remove('active');
            return;
        }

        fetch(`/api/people/search?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                partSuggestions.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(person => {
                        // Skip if already added
                        if (selectedParticipants.some(p => p.name.toLowerCase() === person.name.toLowerCase())) {
                            return;
                        }
                        
                        const li = document.createElement('li');
                        li.className = 'autocomplete-item';
                        li.innerHTML = `<i class="fa-solid fa-user"></i> <span>${person.name}</span> <small>(Tersimpan)</small>`;
                        li.onclick = function() {
                            addParticipant(person.name);
                            partSearch.value = '';
                            partSuggestions.innerHTML = '';
                            partSuggestions.classList.remove('active');
                        };
                        partSuggestions.appendChild(li);
                    });
                    
                    // Option to add new name anyway
                    const exists = selectedParticipants.some(p => p.name.toLowerCase() === query.toLowerCase());
                    if (!exists) {
                        const li = document.createElement('li');
                        li.className = 'autocomplete-item';
                        li.innerHTML = `<i class="fa-solid fa-plus text-primary"></i> Gunakan "${query}" sebagai data baru`;
                        li.onclick = function() {
                            addParticipant(query);
                            partSearch.value = '';
                            partSuggestions.innerHTML = '';
                            partSuggestions.classList.remove('active');
                        };
                        partSuggestions.appendChild(li);
                    }
                    
                    partSuggestions.classList.add('active');
                } else {
                    // Option to add new name
                    const exists = selectedParticipants.some(p => p.name.toLowerCase() === query.toLowerCase());
                    if (!exists) {
                        const li = document.createElement('li');
                        li.className = 'autocomplete-item';
                        li.innerHTML = `<i class="fa-solid fa-plus text-primary"></i> Gunakan "${query}" sebagai data baru`;
                        li.onclick = function() {
                            addParticipant(query);
                            partSearch.value = '';
                            partSuggestions.innerHTML = '';
                            partSuggestions.classList.remove('active');
                        };
                        partSuggestions.appendChild(li);
                        partSuggestions.classList.add('active');
                    } else {
                        partSuggestions.classList.remove('active');
                    }
                }
            });
    }, 200));

    // Handle Enter key on participant search to add custom name
    partSearch.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = this.value.trim();
            if (val.length > 0) {
                // Check if already in selection
                const exists = selectedParticipants.some(p => p.name.toLowerCase() === val.toLowerCase());
                if (!exists) {
                    addParticipant(val);
                }
                this.value = '';
                partSuggestions.innerHTML = '';
                partSuggestions.classList.remove('active');
            }
        }
    });

    // Close autocomplete lists on clicking outside
    document.addEventListener('click', function(e) {
        if (e.target !== picSearch) {
            picSuggestions.classList.remove('active');
        }
        if (e.target !== partSearch) {
            partSuggestions.classList.remove('active');
        }
    });

    function addPic(name) {
        selectedPics.push({ id: null, name: name });
        renderPics();
    }

    function removePic(index) {
        selectedPics.splice(index, 1);
        renderPics();
    }

    function renderPics() {
        const container = document.getElementById('selected-pics');
        const validator = document.getElementById('pics-validator');
        container.innerHTML = '';
        
        if (selectedPics.length > 0) {
            selectedPics.forEach((person, idx) => {
                const chip = document.createElement('div');
                chip.className = 'participant-chip animate-scale-up';
                chip.style.backgroundColor = 'rgba(59, 130, 246, 0.15)';
                chip.style.borderColor = 'rgba(59, 130, 246, 0.3)';
                chip.style.color = 'var(--color-seminar)';
                chip.innerHTML = `
                    <span>${person.name}</span>
                    <input type="hidden" name="pics[]" value="${person.name}">
                    <button type="button" class="chip-remove" onclick="removePic(${idx})">&times;</button>
                `;
                container.appendChild(chip);
            });
            validator.value = "yes";
        } else {
            container.innerHTML = '<span class="text-muted text-sm">Belum ada PIC yang dipilih</span>';
            validator.value = ""; // Form will not validate if this is empty & required
        }
    }

    function addParticipant(name) {
        selectedParticipants.push({ id: null, name: name });
        renderParticipants();
    }

    function removeParticipant(index) {
        selectedParticipants.splice(index, 1);
        renderParticipants();
    }

    function renderParticipants() {
        const container = document.getElementById('selected-participants');
        const validator = document.getElementById('participants-validator');
        container.innerHTML = '';
        
        if (selectedParticipants.length > 0) {
            selectedParticipants.forEach((person, idx) => {
                const chip = document.createElement('div');
                chip.className = 'participant-chip animate-scale-up';
                chip.innerHTML = `
                    <span>${person.name}</span>
                    <input type="hidden" name="participants[]" value="${person.name}">
                    <button type="button" class="chip-remove" onclick="removeParticipant(${idx})">&times;</button>
                `;
                container.appendChild(chip);
            });
            validator.value = "yes";
        } else {
            container.innerHTML = '<span class="text-muted text-sm">Belum ada peserta yang dipilih</span>';
            validator.value = ""; // Form will not validate if this is empty & required
        }
    }

    // Debounce helper
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
    @endauth

    function toggleMobileSidebar() {
        const sidebar = document.querySelector('.calendar-sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar && overlay) {
            sidebar.classList.toggle('active');
            if (sidebar.classList.contains('active')) {
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            } else {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    }
</script>
@endsection
