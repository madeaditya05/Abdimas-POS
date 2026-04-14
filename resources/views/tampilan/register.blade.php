<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Daftar - Cofit EV Dashboard</title>

  {{-- Pakai CSS khusus auth/login --}}
  <link rel="stylesheet" href="{{ asset('assets/login.css') }}" />

  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<div class="auth-page">

  {{-- PANEL KIRI: ILUSTRASI REGISTER --}}
  <div class="auth-hero">
    <div class="auth-hero-inner">
      <img
        src="{{ asset('images/register.png') }}"
        alt="Ilustrasi register Cofit EV"
        class="auth-hero-image"
      >
    </div>
  </div>

  {{-- PANEL KANAN: FORM REGISTER --}}
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
          <h2>Daftar Akun</h2>
          <p>Buat akun baru untuk mengakses dashboard</p>
        </div>

        {{-- Alert error --}}
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Alert sukses --}}
        @if (session('success'))
          <div class="alert alert-success">
            {{ session('success') }}
          </div>
        @endif

        {{-- FORM REGISTER (logic sama persis) --}}
        <form action="{{ route('register.store') }}" method="POST" id="registerForm" novalidate>
          @csrf

          <div class="form-group" style="display:none;">
            <label for="name">Nama</label>
            <div class="input-wrapper">
              <input
                type="text"
                id="name"
                name="name"
                class="form-input"
                placeholder="Nama lengkap"
                value="{{ old('name') }}"
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <div class="input-wrapper">
              <input
                type="text"
                id="email"
                name="email"
                class="form-input"
                placeholder="email"
                value="{{ old('email') }}"
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
                required
              >
            </div>
          </div>

          <div class="form-group">
    <label for="user_group_display">User Group</label>

    <div class="custom-select" id="userGroupSelect">
      {{-- field yang dikirim ke controller --}}
      <input
        type="hidden"
        name="user_group"
        id="user_group"
        value="{{ old('user_group', 'owner') }}"
      >

      {{-- tombol utama (seperti input) --}}
      <button type="button" class="cs-trigger" id="user_group_display">
        <span class="cs-trigger-label" id="userGroupLabel">
          @php
            $ug = old('user_group');
            $label = $ug === 'kasir'
              ? 'Kasir'
              : ($ug === 'owner' ? 'Owner' : '— Pilih user_group —');
          @endphp
          {{ $label }}
        </span>

        {{-- icon chevron dari heroicons (sesuaikan kalau prefix-mu beda) --}}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="cs-trigger-icon"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
      </button>

      {{-- menu dropdown --}}
      <div class="cs-menu">
        <div class="cs-option" data-value="" data-label="— Pilih user_group —">
          <span class="cs-option-text">— Pilih user_group —</span>
        </div>

        <div class="cs-option" data-value="kasir" data-label="Kasir">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="cs-option-icon"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          <span class="cs-option-text">Kasir</span>
        </div>

        <div class="cs-option" data-value="owner" data-label="Owner">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="cs-option-icon"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          <span class="cs-option-text">Owner</span>
        </div>
      </div>
    </div>

    <small class="text-muted">Owner membutuhkan kode keamanan.</small>
</div>


          <div class="form-group" id="ownerGuard">
            <label for="owner_token">Kode Owner (proteksi)</label>
            <div class="input-wrapper">
              <input
                type="password"
                id="owner_token"
                name="owner_token"
                class="form-input"
                placeholder="Masukkan kode owner"
                required
              >
            </div>
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

<script>
  (function () {
    const ownerGuard = document.getElementById('ownerGuard');
    const ownerToken = document.getElementById('owner_token');
    const userInput = document.getElementById('user_group'); // hidden input
    const cs = document.getElementById('userGroupSelect');
    const trigger = document.getElementById('user_group_display');
    const labelSpan = document.getElementById('userGroupLabel');
    const options = cs ? cs.querySelectorAll('.cs-option') : [];

    function syncOwnerGuard(value) {
      const isOwner = value === 'owner';
      if (ownerGuard) ownerGuard.style.display = isOwner ? '' : 'none';
      if (ownerToken) ownerToken.required = isOwner;
    }

    function selectOption(opt) {
      const value = opt.getAttribute('data-value');
      const label = opt.getAttribute('data-label');

      if (userInput) userInput.value = value || '';
      if (labelSpan) labelSpan.textContent = label || '— Pilih user_group —';

      options.forEach(o => o.classList.toggle('is-active', o === opt));

      if (value) {
        cs.classList.add('has-value');
      } else {
        cs.classList.remove('has-value');
      }

      syncOwnerGuard(value);
    }

    if (trigger) {
      trigger.addEventListener('click', function () {
        cs.classList.toggle('open');
      });
    }

    options.forEach(function (opt) {
      opt.addEventListener('click', function () {
        selectOption(opt);
        cs.classList.remove('open');
      });
    });

    // Klik di luar → tutup menu
    document.addEventListener('click', function (e) {
      if (!cs.contains(e.target)) {
        cs.classList.remove('open');
      }
    });

    // Set kondisi awal (old('user_group'))
    if (userInput && userInput.value) {
      const initial = Array.from(options).find(
        o => o.getAttribute('data-value') === userInput.value
      );
      if (initial) {
        selectOption(initial);
      } else {
        syncOwnerGuard(userInput.value);
      }
    } else {
      syncOwnerGuard('');
    }
  })();
</script>


</body>
</html>
