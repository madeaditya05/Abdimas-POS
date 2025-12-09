@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Produk')

@section('content')
<div class="card">
  {{-- Header --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Produk</h2>

    <a href="{{ route('produk.create') }}"
       class="btn btn--outline-success btn--with-icon">
      <x-heroicon-o-plus class="icon-inline" />
      <span>Tambah Produk</span>
    </a>
  </div>

  {{-- Filter & Search --}}
  <form method="GET"
        action="{{ route('produk.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">

    {{-- Search kode / nama --}}
    <input type="text"
           name="search"
           value="{{ $search }}"
           placeholder="Cari kode atau nama…" />

    {{-- Kategori (custom dropdown .dd) --}}
    @php $selKat = $selectedKategori ?? ''; @endphp
    <div class="dd" style="min-width:220px">
      <button type="button" class="dd-toggle">
        <span class="dd-label">
          @switch($selKat)
            @case('coffee') Coffee @break
            @case('non_coffee') Non Coffee @break
            @case('snack') Snack @break
            @default Semua kategori
          @endswitch
        </span>
        <span class="dd-caret"></span>
      </button>
      <div class="dd-menu">
        <div class="dd-item {{ $selKat==='' ? 'active':'' }}" data-value="">
          Semua kategori
        </div>
        <div class="dd-item {{ $selKat==='coffee' ? 'active':'' }}" data-value="coffee">
          Coffee
        </div>
        <div class="dd-item {{ $selKat==='non_coffee' ? 'active':'' }}" data-value="non_coffee">
          Non Coffee
        </div>
        <div class="dd-item {{ $selKat==='snack' ? 'active':'' }}" data-value="snack">
          Snack
        </div>
      </div>
      <input type="hidden" name="kategori" value="{{ $selKat }}">
    </div>

    {{-- Tombol --}}
    <button class="btn btn--outline-coffee">Terapkan</button>

    <a href="{{ route('produk.index') }}"
       class="btn btn--outline-coffee">
      Reset
    </a>
  </form>

  {{-- Tabel --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama</th>
          <th class="num">Harga</th>
          <th>Kategori</th>
          <th style="width:130px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse($produks as $produk)
          @php
            $kategori = $produk->kategori;
            $badgeClass = 'badge-secondary';
            if ($kategori === 'coffee') {
                $badgeClass = 'badge-success';
            } elseif ($kategori === 'non_coffee') {
                $badgeClass = 'badge-warning';
            } elseif ($kategori === 'snack') {
                $badgeClass = 'badge-danger';
            }

            $kategoriLabel = \Illuminate\Support\Str::of((string) $kategori)
                ->replace('_', ' ')
                ->title();
          @endphp

          <tr>
            <td>{{ $produk->kode_barang }}</td>
            <td>{{ $produk->nama_barang }}</td>

            <td class="num">
              Rp {{ number_format((float) $produk->harga, 0, ',', '.') }}
            </td>

            <td>
              @if ($kategori)
                <span class="badge {{ $badgeClass }}">
                  {{ $kategoriLabel }}
                </span>
              @else
                <span class="muted">–</span>
              @endif
            </td>

            <td>
              <div class="actions">
                {{-- EDIT --}}
                <a href="{{ route('produk.edit', $produk) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <x-heroicon-o-pencil-square class="icon-aksi" />
                  <span class="sr-only">Edit</span>
                </a>

                {{-- HAPUS --}}
                <form action="{{ route('produk.destroy', $produk) }}"
                      method="POST"
                      onsubmit="return confirm('Hapus produk ini?')">
                  @csrf
                  @method('DELETE')

                  <button type="submit"
                          class="btn btn--outline-danger btn--sm btn--icon"
                          title="Hapus">
                    <x-heroicon-o-trash class="icon-aksi" />
                    <span class="sr-only">Hapus</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="muted" style="text-align:center;">
              Belum ada data produk.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:12px;">
    {{ $produks->links('pagination::cofit') }}
  </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const closeAll = () => document.querySelectorAll('.dd.open').forEach(dd => dd.classList.remove('open'));
  document.addEventListener('click', e => { if (!e.target.closest('.dd')) closeAll(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });

  document.querySelectorAll('.dd').forEach(dd => {
    const btn   = dd.querySelector('.dd-toggle');
    const menu  = dd.querySelector('.dd-menu');
    const label = dd.querySelector('.dd-label');
    const input = dd.querySelector('input[type="hidden"]');

    btn?.addEventListener('click', e => {
      e.stopPropagation();
      const willOpen = !dd.classList.contains('open');
      closeAll(); if (willOpen) dd.classList.add('open');
    });

    menu?.querySelectorAll('.dd-item').forEach(item => {
      item.addEventListener('click', () => {
        menu.querySelectorAll('.dd-item.active').forEach(x => x.classList.remove('active'));
        item.classList.add('active');
        input.value = item.dataset.value ?? '';
        label.textContent = item.textContent.trim();
        dd.classList.remove('open');
      });
    });
  });
})();
</script>
@endpush
