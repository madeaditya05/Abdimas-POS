<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Login - Cofit EV</title>

  {{-- Pakai CSS khusus auth/login --}}
  <link rel="stylesheet" href="{{ asset('assets/login.css') }}">

  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<div class="auth-page" style="--auth-mobile-bg: url('{{ asset('images/login.png') }}')">

  {{-- PANEL KIRI: ILUSTRASI --}}
  <div class="auth-hero">
    <div class="auth-hero-inner">
      <img
        src="{{ asset('images/login.png') }}"
        alt="Ilustrasi login Cofit EV"
        class="auth-hero-image"
      >
    </div>
  </div>

  {{-- PANEL KANAN: FORM LOGIN --}}
  <div class="auth-panel">
    <div class="login-container">
      <div class="login-card">

        {{-- Brand / logo --}}
        <div class="login-logo">
          <div class="logo-icon">
            <img src="{{ asset('images/logo.png') }}" alt="Cofit EV">
          </div>
          <div class="login-logo-text">
            <h1>Cofit EV</h1>
            <p>Dashboard Admin</p>
          </div>
        </div>

        {{-- Header form --}}
        <div class="login-header">
          <h2>Masuk</h2>
          <p>Masukkan kredensial Anda untuk mengakses dashboard</p>
        </div>

        {{-- Flash sukses dari halaman register --}}
        @if (session('success'))
          <div class="alert alert-success">
            {{ session('success') }}
          </div>
        @endif

        {{-- Error validasi umum --}}
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- FORM LOGIN (logic sama persis) --}}
        <form action="{{ url('/login') }}" method="POST" novalidate>
          @csrf

          <div class="form-group">
            <label for="email">Email</label>
            <div class="input-wrapper">
              <input
                type="email"
                id="email"
                name="email"
                class="form-input"
                placeholder="nama@contoh.com"
                value="{{ old('email', session('prefill_email')) }}"
                required
                autofocus
              >
            </div>
            @error('email')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <input
                type="password"
                id="password"
                name="password"
                class="form-input"
                placeholder="••••••••"
                required
              >
            </div>
            @error('password')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group form-remember">
            <label class="remember-checkbox">
              <input type="checkbox" name="remember" id="remember">
              <span>Ingat saya</span>
            </label>
          </div>

          <button type="submit" class="btn-login">
            Masuk
          </button>

          <div class="auth-footer-text">
            Belum punya akun?
            <a href="{{ route('register.show') }}">Daftar sekarang</a>
          </div>
        </form>

      </div>
    </div>
  </div>

</div>

</body>
</html>
