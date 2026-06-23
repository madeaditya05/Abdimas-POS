@extends('layouts.main')
@section('title', 'Pengaturan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
  <style>
    .settings-card {
      background: var(--card);
      border-radius: var(--radius);
      box-shadow: var(--shadow-1);
      border: 1px solid var(--line);
      padding: 24px;
      margin-bottom: 24px;
    }
    .settings-title {
      font-size: 22px;
      font-weight: 800;
      color: var(--ink);
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .settings-subtitle {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 24px;
    }
  </style>
@endpush

@section('content')
<div class="card" style="border: 0; background: transparent; box-shadow: none;">
  <div class="settings-card">
    <div class="settings-title">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--bb-accent);">
        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
        <circle cx="12" cy="12" r="3"/>
      </svg>
      <span>Pengaturan Sistem</span>
    </div>
    <div class="settings-subtitle">
      Kelola konfigurasi global aplikasi Pasta Nafisa, termasuk kode proteksi untuk pendaftaran pengguna baru.
    </div>

    @if (session('success'))
      <div class="alert alert--success" style="margin-bottom: 20px;">
        {{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert--error" style="margin-bottom: 20px;">
        <strong>Periksa kembali:</strong>
        <ul style="margin: 6px 0 0 18px; font-size: 14px;">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('pengaturan.update') }}" class="form">
      @csrf
      
      <div class="form-section" style="border: 0; padding: 0; background: transparent; margin-bottom: 24px;">
        <div class="form-title" style="font-size: 16px; margin-bottom: 16px; border-bottom: 1px solid var(--bb-line); padding-bottom: 8px;">
          Kode Proteksi Pendaftaran (Sign-up Code)
        </div>

        <div class="form-grid" style="display: grid; gap: 20px; grid-template-columns: repeat(2, 1fr);">
          <div class="form-field span-2" style="grid-column: span 2;">
            <label style="font-weight: 600; display: block; margin-bottom: 6px;">Kode Proteksi</label>
            <input type="text" 
                   name="signup_code" 
                   value="{{ old('signup_code', $signupCode) }}" 
                   class="form-input" 
                   placeholder="Masukkan kode proteksi"
                   style="width: 100%; height: 42px; padding: 10px 14px; border: 1px solid var(--bb-line); border-radius: 8px;">
            <div class="form-help" style="font-size: 12px; color: var(--bb-muted); margin-top: 6px;">
              Kode keamanan yang harus diinput oleh pendaftar baru agar bisa membuat akun.
            </div>
          </div>
        </div>
      </div>

      <div class="form-section" style="border: 0; padding: 0; background: transparent; margin-bottom: 20px;">
        <div class="form-title" style="font-size: 16px; margin-bottom: 16px; border-bottom: 1px solid var(--bb-line); padding-bottom: 8px;">
          Diskon Loyalitas Pelanggan (Global Loyalty Discount)
        </div>

        <div class="form-grid" style="display: grid; gap: 20px; grid-template-columns: repeat(2, 1fr);">
          <div class="form-field">
            <label style="font-weight: 600; display: block; margin-bottom: 6px;">Minimal Transaksi</label>
            <input type="number" 
                   name="discount_min_transactions" 
                   value="{{ old('discount_min_transactions', $discountMinTransactions) }}" 
                   class="form-input" 
                   required
                   min="1"
                   placeholder="Contoh: 10"
                   style="width: 100%; height: 42px; padding: 10px 14px; border: 1px solid var(--bb-line); border-radius: 8px;">
            <div class="form-help" style="font-size: 12px; color: var(--bb-muted); margin-top: 6px;">
              Pelanggan mendapatkan diskon jika kelipatan jumlah transaksi mereka mencapai angka ini.
            </div>
          </div>
          <div class="form-field">
            <label style="font-weight: 600; display: block; margin-bottom: 6px;">Persentase Diskon (%)</label>
            <input type="number" 
                   name="discount_percent" 
                   value="{{ old('discount_percent', $discountPercent) }}" 
                   class="form-input" 
                   required
                   min="0"
                   max="99.99"
                   step="0.01"
                   placeholder="Contoh: 5"
                   style="width: 100%; height: 42px; padding: 10px 14px; border: 1px solid var(--bb-line); border-radius: 8px;">
            <div class="form-help" style="font-size: 12px; color: var(--bb-muted); margin-top: 6px;">
              Besar persentase diskon yang diberikan (misalnya 5 untuk 5%). Isi 0 untuk menonaktifkan.
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--bb-line); display: flex; justify-content: flex-end; gap: 12px;">
        <button type="submit" class="btn btn--success" style="padding: 10px 24px; border-radius: 8px; font-weight: 700; background: var(--bb-accent); border: 0; color: #fff; cursor: pointer;">
          Simpan Pengaturan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
