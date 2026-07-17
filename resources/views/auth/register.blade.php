@extends('layouts.layout')

@section('title', 'Daftar Akun - Jadwal Kegiatan')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h2>Buat Akun Baru</h2>
            <p>Daftar sekarang untuk dapat menambahkan dan berkolaborasi dalam jadwal kegiatan.</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="auth-form">
            @csrf
            
            <div class="form-group">
                <label for="name"><i class="fa-solid fa-user"></i> Nama Lengkap</label>
                <div class="input-wrapper">
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="John Doe" required autocomplete="name" autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Alamat Email</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Kata Sandi</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation"><i class="fa-solid fa-shield"></i> Konfirmasi Kata Sandi</label>
                <div class="input-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa-solid fa-user-check"></i> Registrasi Akun
            </button>
        </form>

        <div class="auth-footer">
            <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk Disini</a></p>
        </div>
    </div>
</div>
@endsection
