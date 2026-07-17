@extends('layouts.layout')

@section('title', 'Masuk - Jadwal Kegiatan')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fa-regular fa-calendar-days"></i>
            </div>
            <h2>Selamat Datang Kembali</h2>
            <p>Silakan masuk ke akun Anda untuk mulai menginput kegiatan.</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="auth-form">
            @csrf
            
            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Alamat Email</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autocomplete="email" autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Kata Sandi</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <div class="auth-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat Saya</label>
                </div>
                <a href="#" class="forgot-password">Lupa Kata Sandi?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
            </button>
        </form>


    </div>
</div>
@endsection
