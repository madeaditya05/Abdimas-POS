@push('styles')
  {{-- sementara pakai CSS yang sama dengan Bahan Baku biar look & feel konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Penyesuaian Stok')

@section('content')
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Penyesuaian Stok (Per Bahan)</h2>

    <a href="{{ route('penyesuaian-stok.index') }}"
       class="btn btn--outline-coffee">
       Riwayat Penyesuaian
    </a>
  </div>

  {{-- Tab switch: Per Bahan / Per Menu --}}
  <div style="display:flex; gap:12px; margin:16px 20px 0 20px;">
    <a href="{{ route('penyesuaian-stok.create') }}"
       class="btn {{ request()->routeIs('penyesuaian-stok.create') ? 'btn--primary' : 'btn--outline-coffee' }}">
       Per Bahan
    </a>

    <a href="{{ route('penyesuaian-stok.menu') }}"
       class="btn {{ request()->routeIs('penyesuaian-stok.menu') ? 'btn--primary' : 'btn--outline-coffee' }}">
       Per Menu
    </a>
  </div>

  <div class="card-body">
    <form method="POST" action="{{ route('penyesuaian-stok.store') }}" class="form">
      @csrf
      <input type="hidden" name="input_type" value="bahan">
      {{-- Bahan Baku --}}
      <div class="form-group">
        <label for="bahan_baku_id">Bahan Baku</label>
        <select name="bahan_baku_id" id="bahan_baku_id" required>
          <option value="">-- pilih bahan --</option>
          @foreach($bahanList as $bahan)
            <option value="{{ $bahan->id }}" {{ old('bahan_baku_id') == $bahan->id ? 'selected' : '' }}>
              {{ $bahan->kode_bahan }} — {{ $bahan->nama_bahan }}
            </option>
          @endforeach
        </select>
        @error('bahan_baku_id')
          <p class="form-error">{{ $message }}</p>
        @enderror
      </div>

      {{-- Jenis penyesuaian (plus/minus) --}}
      <div class="form-group">
        <label>Jenis Penyesuaian</label>
        <div style="display:flex;gap:12px;align-items:center;">
          <label style="display:flex;align-items:center;gap:6px;">
            <input type="radio" name="mode" value="plus" {{ old('mode','plus') === 'plus' ? 'checked' : '' }}>
            <span>Tambah stok</span>
          </label>
          <label style="display:flex;align-items:center;gap:6px;">
            <input type="radio" name="mode" value="minus" {{ old('mode') === 'minus' ? 'checked' : '' }}>
            <span>Kurangi stok</span>
          </label>
        </div>
        @error('mode')
          <p class="form-error">{{ $message }}</p>
        @enderror
      </div>

      {{-- Qty per Bahan --}}
      <div class="form-group">
        <label for="qty">Jumlah (dalam satuan pakai)</label>
        <input type="number"
               id="qty"
               name="qty"
               step="0.01"
               min="0"
               value="{{ old('qty') }}"
               placeholder="contoh: 60 (ml)"
               required>
        @error('qty')
          <p class="form-error">{{ $message }}</p>
        @enderror
      </div>

      {{-- Tanggal --}}
      <div class="form-group">
        <label for="tanggal">Tanggal</label>
        <input type="date"
               id="tanggal"
               name="tanggal"
               value="{{ old('tanggal', $today) }}"
               required>
        @error('tanggal')
          <p class="form-error">{{ $message }}</p>
        @enderror
      </div>

      {{-- Catatan --}}
      <div class="form-group">
        <label for="note">Catatan (opsional)</label>
        <textarea id="note" name="note" rows="3"
                  placeholder="contoh: penyesuaian karena stok fisik kurang 2 botol">{{ old('note') }}</textarea>
        @error('note')
          <p class="form-error">{{ $message }}</p>
        @enderror
      </div>

      <div style="margin-top:16px;display:flex;gap:8px;">
        <button type="submit" class="btn btn--primary">
          Simpan Penyesuaian
        </button>

        <a href="{{ route('penyesuaian-stok.index') }}" class="btn btn--outline-coffee">
          Batal
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
