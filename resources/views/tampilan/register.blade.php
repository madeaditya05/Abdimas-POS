<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Daftar - Cofit EV Dashboard</title>
  <link rel="stylesheet" href="{{ asset('assets/styles.css') }}" />
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
        <h2>Daftar Akun</h2>
        <p>Buat akun baru untuk mengakses dashboard</p>
      </div>

      {{-- Alert error --}}
      @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom:12px;">
          <ul style="margin-left:18px;">
            @foreach ($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Alert sukses --}}
      @if (session('success'))
        <div class="alert alert-success" style="margin-bottom:12px;">
          {{ session('success') }}
        </div>
      @endif

      <form action="{{ route('register.store') }}" method="POST" id="registerForm" novalidate>
        @csrf

        <div class="form-group">
          <label for="name">Nama</label>
          <div class="input-wrapper">
            <input type="text" id="name" name="name" class="form-input"
                   placeholder="Nama lengkap" value="{{ old('name') }}" required>
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-wrapper">
            <input type="text" id="email" name="email" class="form-input"
                   placeholder="email" value="{{ old('email') }}" required>
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" class="form-input"
                   placeholder="Minimal 6 karakter" minlength="6" required>
          </div>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Konfirmasi Password</label>
          <div class="input-wrapper">
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="form-input" placeholder="Ulangi password" required>
          </div>
        </div>

        <div class="form-group">
          <label for="user_group">User Group</label>
          <div class="input-wrapper">
            <select id="user_group" name="user_group" class="form-input" required>
              <option value="">— Pilih user_group —</option>
              <option value="kasir" {{ old('user_group')==='kasir'?'selected':'' }}>Kasir</option>
              <option value="owner" {{ old('user_group')==='owner'?'selected':'' }}>Owner</option>
            </select>
          </div>
          <small class="text-muted">Owner membutuhkan kode keamanan.</small>
        </div>

        <div class="form-group" id="ownerGuard" style="display:none;">
          <label for="owner_token">Kode Owner (proteksi)</label>
          <div class="input-wrapper">
            <input type="password" id="owner_token" name="owner_token" class="form-input"
                   placeholder="Masukkan kode owner">
          </div>
        </div>

        <button type="submit" class="btn-login">Daftar</button>

        <div class="demo-info" style="margin-top:10px;">
          Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
      </form>

    </div>
  </div>

  <script>
    (function(){
      const user_group = document.getElementById('user_group');
      const ownerGuard = document.getElementById('ownerGuard');
      const ownerToken = document.getElementById('owner_token');

      function toggleOwner() {
        const isOwner = user_group.value === 'owner';
        ownerGuard.style.display = isOwner ? '' : 'none';
        if (ownerToken) ownerToken.required = isOwner;
      }
      user_group.addEventListener('change', toggleOwner);
      toggleOwner(); // set kondisi awal (termasuk saat old('user_group') = owner)
    })();
  </script>
</body>
</html>
