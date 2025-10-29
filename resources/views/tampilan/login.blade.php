<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Login - Cofit EV</title>
  <link rel="stylesheet" href="{{ asset('assets/styles.css') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="login-container">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-icon">
        <img src="{{ asset('images/logo.png') }}" alt="Cofit EV">
      </div>
      <h1>Cofit EV</h1>
      <p>Dashboard Admin</p>
    </div>

    <div class="login-header">
      <h2>Masuk</h2>
      <p>Masukkan kredensial Anda</p>
    </div>

    {{-- Flash sukses dari halaman register --}}
    @if (session('success'))
      <div class="alert alert-success" style="margin-bottom:12px;">
        {{ session('success') }}
      </div>
    @endif

    {{-- Error validasi umum --}}
    @if ($errors->any())
      <div class="alert alert-danger" style="margin-bottom:12px;">
        <ul style="margin-left:16px;">
          @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Arahkan ke POST /login (pastikan rutenya ada) --}}
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
        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
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
        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
      </div>

      <div class="form-group" style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="remember" id="remember">
        <label for="remember" style="margin:0;">Ingat saya</label>
      </div>

      <button type="submit" class="btn-login">Masuk</button>

      <div class="demo-info" style="margin-top:10px;">
        Belum punya akun? <a href="{{ route('register.show') }}">Daftar sekarang</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
