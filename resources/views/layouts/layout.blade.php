<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Jadwal Kegiatan - Google Calendar Clone')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Style Sheet -->
    @vite(['resources/css/app.css'])

    @yield('styles')
</head>
<body>
    <!-- Background Glow Effects -->
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>
    
    <!-- Header Navigation -->
    <header class="app-header">
        <div class="header-container">
            <a href="{{ route('calendar') }}" class="app-logo">
                <i class="fa-regular fa-calendar-days logo-icon"></i>
                <span class="logo-text">Jadwal<span>Kegiatan</span> Konekin</span>
            </a>
            
            <nav class="nav-menu">
                <a href="{{ route('calendar') }}" class="nav-link {{ Route::currentRouteName() === 'calendar' ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-alt"></i> Kalender
                </a>
                @auth
                    @if(Auth::user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Route::currentRouteName() === 'admin.dashboard' ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i> Dashboard Admin
                        </a>
                    @endif
                @endauth
            </nav>
            
            <div class="header-actions">
                @auth
                    <div class="user-profile">
                        <div class="user-info">
                            <span class="user-name">{{ Auth::user()->name }}</span>
                            <span class="user-role">{{ Auth::user()->is_admin ? 'Administrator' : 'User' }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-logout" title="Logout">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Logout</span>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="app-main">
        <div class="main-container">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div class="alert-content">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <div class="footer-container">
            <p>&copy; 2026 JadwalKegiatan Inc. Dibuat dengan <i class="fa-solid fa-heart text-danger"></i> oleh prbw.</p>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
