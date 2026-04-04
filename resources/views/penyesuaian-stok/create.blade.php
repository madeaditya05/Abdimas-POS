@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
  <style>
    .adjust-mode-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }

    .adjust-mode-option {
      position: relative;
    }

    .adjust-mode-option input[type="radio"] {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .adjust-mode-card {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 16px;
      border: 1px solid var(--bb-line, #e5e7eb);
      border-radius: 14px;
      background: #fff;
      cursor: pointer;
      transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease, background-color .18s ease;
    }

    .adjust-mode-card:hover {
      transform: translateY(-1px);
      border-color: #c8b7a6;
      box-shadow: 0 10px 22px rgba(59, 47, 47, 0.08);
    }

    .adjust-mode-option input[type="radio"]:checked + .adjust-mode-card {
      border-color: #3b2f2f;
      background: #f7f2ee;
      box-shadow: 0 0 0 3px rgba(59, 47, 47, 0.14);
    }

    .adjust-mode-icon {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 42px;
    }

    .adjust-mode-icon svg {
      width: 18px;
      height: 18px;
      stroke: currentColor;
    }

    .adjust-mode-option--plus .adjust-mode-icon {
      color: #15803d;
      background: #dcfce7;
    }

    .adjust-mode-option--minus .adjust-mode-icon {
      color: #b91c1c;
      background: #fee2e2;
    }

    .adjust-mode-title {
      font-weight: 700;
      color: #1f2937;
    }

    .adjust-mode-desc {
      margin-top: 2px;
      font-size: 12px;
      color: #6b7280;
      line-height: 1.4;
    }

    @media (max-width: 640px) {
      .adjust-mode-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
@endpush

@extends('layouts.main')
@section('title','Penyesuaian Stok')

@section('content')
@if ($errors->any())
  <div class="form-section" style="border-color:#fecaca;background:#fff1f2;">
    <strong>Periksa kembali:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('penyesuaian-stok.store') }}" class="form">
  @csrf

  <div class="form-section">
    <div class="form-title">Penyesuaian Stok</div>
    <div class="form-grid">
      <div class="form-field">
        <label for="bahan_baku_id">Bahan Baku <span style="color:#ef4444">*</span></label>
        <div class="field-with-icon">
          <select class="form-input" name="bahan_baku_id" id="bahan_baku_id" required>
            <option value="">Pilih bahan baku...</option>
            @foreach($bahanList as $bahan)
              <option value="{{ $bahan->id }}" {{ old('bahan_baku_id') == $bahan->id ? 'selected' : '' }}>
                {{ $bahan->kode_bahan }} - {{ $bahan->nama_bahan }}
              </option>
            @endforeach
          </select>
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M4 7h16" /><path d="M7 12h10" /><path d="M10 17h4" /></svg>
          </span>
        </div>
        <div class="form-help">Pilih bahan yang stok fisiknya ingin disesuaikan.</div>
      </div>

      <div class="form-field">
        <label for="qty">Jumlah Penyesuaian <span style="color:#ef4444">*</span></label>
        <div class="field-with-icon">
          <input class="form-input"
                 type="number"
                 id="qty"
                 name="qty"
                 step="0.01"
                 min="0"
                 value="{{ old('qty') }}"
                 placeholder="Contoh: 60"
                 required>
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          </span>
        </div>
        <div class="form-help">Masukkan jumlah dalam satuan pakai bahan yang dipilih.</div>
      </div>

      <div class="form-field">
        <label for="tanggal">Tanggal <span style="color:#ef4444">*</span></label>
        <div class="field-with-icon">
          <input class="form-input"
                 type="date"
                 id="tanggal"
                 name="tanggal"
                 value="{{ old('tanggal', $today) }}"
                 required>
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M7 3v3" /><path d="M17 3v3" /><path d="M4 9h16" /><path d="M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
          </span>
        </div>
        <div class="form-help">Gunakan tanggal saat penyesuaian stok dilakukan.</div>
      </div>

      <div class="form-field">
        <label for="note">Catatan</label>
        <div class="field-with-icon">
          <input class="form-input"
                 type="text"
                 id="note"
                 name="note"
                 value="{{ old('note') }}"
                 placeholder="Contoh: stok fisik kurang 2 botol saat opname">
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M7 6h10" /><path d="M7 12h10" /><path d="M7 18h6" /><path d="M5 3h14a1 1 0 0 1 1 1v16l-4-2-4 2-4-2-4 2V4a1 1 0 0 1 1-1Z" /></svg>
          </span>
        </div>
        <div class="form-help">Opsional. Isi alasan penyesuaian agar riwayat lebih jelas.</div>
      </div>
    </div>
  </div>

  <div class="form-section">
    <div class="form-title">Jenis Penyesuaian</div>
    <div class="adjust-mode-grid">
      <label class="adjust-mode-option adjust-mode-option--plus">
        <input type="radio" name="mode" value="plus" {{ old('mode', 'plus') === 'plus' ? 'checked' : '' }}>
        <span class="adjust-mode-card">
          <span class="adjust-mode-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          </span>
          <span>
            <span class="adjust-mode-title">Tambah Stok</span>
            <span class="adjust-mode-desc">Gunakan saat stok fisik lebih banyak dari catatan sistem.</span>
          </span>
        </span>
      </label>

      <label class="adjust-mode-option adjust-mode-option--minus">
        <input type="radio" name="mode" value="minus" {{ old('mode') === 'minus' ? 'checked' : '' }}>
        <span class="adjust-mode-card">
          <span class="adjust-mode-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14" /></svg>
          </span>
          <span>
            <span class="adjust-mode-title">Kurangi Stok</span>
            <span class="adjust-mode-desc">Gunakan saat stok fisik lebih sedikit dari catatan sistem.</span>
          </span>
        </span>
      </label>
    </div>
    <div class="form-help" style="margin-top:10px;">Pilih arah penyesuaian agar mutasi stok tercatat dengan benar.</div>
  </div>

  <div class="form-section" style="padding-bottom:0;">
    <div class="form-actions">
      <a href="{{ route('penyesuaian-stok.index') }}" class="btn btn--danger">
        Batal
      </a>
      <button type="submit" class="btn btn--success">
        Simpan Penyesuaian
      </button>
    </div>
  </div>
</form>
@endsection
