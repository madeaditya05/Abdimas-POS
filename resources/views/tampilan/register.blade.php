<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Daftar - Cofit EV Dashboard</title>

  <link rel="stylesheet" href="{{ asset('assets/login.css') }}" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<div class="auth-page" style="--auth-mobile-bg: url('{{ asset('images/register.png') }}')">
  <div class="auth-hero">
    <div class="auth-hero-inner">
      <img
        src="{{ asset('images/register.png') }}"
        alt="Ilustrasi register Cofit EV"
        class="auth-hero-image"
      >
    </div>
  </div>

  <div class="auth-panel">
    <div class="login-container">
      <div class="login-card">
        <div class="login-logo">
          <div class="logo-icon">
            <img src="{{ asset('images/logo.png') }}" alt="Cofit EV">
          </div>
          <div class="login-logo-text">
            <h1>Cofit EV</h1>
            <p>Dashboard Admin</p>
          </div>
        </div>

        <div class="login-header">
          <h2>Daftar Akun</h2>
          <p>Buat akun baru untuk mengakses dashboard</p>
        </div>

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @if (session('success'))
          <div class="alert alert-success">
            {{ session('success') }}
          </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" id="registerForm" novalidate>
          @csrf

          <div class="form-group">
            <label for="name">Nama</label>
            <div class="input-wrapper">
              <input
                type="text"
                id="name"
                name="name"
                class="form-input"
                placeholder="Nama lengkap"
                value="{{ old('name') }}"
                autocomplete="name"
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <div class="input-wrapper">
              <input
                type="email"
                id="email"
                name="email"
                class="form-input"
                placeholder="nama@email.com"
                value="{{ old('email') }}"
                autocomplete="email"
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <input
                type="password"
                id="password"
                name="password"
                class="form-input"
                placeholder="Minimal 6 karakter"
                minlength="6"
                autocomplete="new-password"
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password</label>
            <div class="input-wrapper">
              <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="form-input"
                placeholder="Ulangi password"
                autocomplete="new-password"
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label for="owner_token">Kode Proteksi</label>
            <div class="input-wrapper">
              <input
                type="password"
                id="owner_token"
                name="owner_token"
                class="form-input"
                placeholder="Masukkan kode proteksi"
                autocomplete="off"
                required
              >
            </div>
            <small class="text-muted">Kode ini menentukan akses akun secara otomatis.</small>
          </div>

          <button type="submit" class="btn-login">
            Daftar
          </button>

          <div class="auth-footer-text">
            Sudah punya akun?
            <a href="{{ route('login') }}">Masuk di sini</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>
