@push('styles')
  {{-- sementara pakai CSS yang sama dengan Bahan Baku biar look & feel konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Penyesuaian Stok')

@section('content')
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Penyesuaian Stok (Per Menu)</h2>

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
    <form method="POST" action="{{ route('penyesuaian-stok.menu.store') }}" class="form">
      @csrf
      <input type="hidden" name="input_type" value="menu">
      {{-- Produk / Menu --}}
      <div class="form-group">
        <label for="produk_id">Produk / Menu</label>
        <select name="produk_id" id="produk_id" required>
          <option value="">-- pilih produk --</option>
          @foreach($produkList as $p)
            <option value="{{ $p->id }}" {{ old('produk_id') == $p->id ? 'selected' : '' }}>
              {{ $p->kode_barang }} — {{ $p->nama_barang }}
            </option>
          @endforeach
        </select>
        @error('produk_id')
          <p class="form-error">{{ $message }}</p>
        @enderror
      </div>

      {{-- Qty Menu --}}
      <div class="form-group">
        <label for="qty_menu">Jumlah Menu Terjual</label>
        <input type="number"
               id="qty_menu"
               name="qty_menu"
               step="1" 
               min="1"
               value="{{ old('qty_menu') }}"
               placeholder="contoh: 3 (gelas)"
               required>
        @error('qty_menu')
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
                  placeholder="contoh: penyesuaian karena penjualan Aren Latte 3 gelas">{{ old('note') }}</textarea>
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
