@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/produk.css') }}">
@endpush

@extends('layouts.main')
@section('title','Produk')

@section('content')
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Produk</h2>

    <a href="{{ route('produk.create') }}"
       class="btn btn--outline-success btn--with-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-inline"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
      <span>Tambah Produk</span>
    </a>
  </div>

  @if (session('success'))
    <div class="alert alert--success" style="margin:12px 0;">
      {{ session('success') }}
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert--error" style="margin:12px 0;">
      {{ session('error') }}
    </div>
  @endif

  <form method="GET"
        action="{{ route('produk.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">

    <input type="text"
           name="search"
           value="{{ $search }}"
           placeholder="Cari kode atau nama..." />

    @php
      $selKat = $selectedKategori ?? '';
      $selKatLabel = $selKat !== '' && isset($kategoriOptions[$selKat])
          ? $kategoriOptions[$selKat]
          : 'Semua kategori';
    @endphp
    <div class="dd" style="min-width:220px">
      <button type="button" class="dd-toggle">
        <span class="dd-label">
          {{ $selKatLabel }}
        </span>
      </button>
      <div class="dd-menu">
        <div class="dd-item {{ $selKat==='' ? 'active':'' }}" data-value="">
          Semua kategori
        </div>
        @foreach($kategoriOptions as $value => $label)
          <div class="dd-item {{ $selKat === $value ? 'active' : '' }}" data-value="{{ $value }}">
            {{ $label }}
          </div>
        @endforeach
      </div>
      <input type="hidden" name="kategori" value="{{ $selKat }}">
    </div>

    <button class="btn btn--outline-coffee">Terapkan</button>

    <a href="{{ route('produk.index') }}"
       class="btn btn--outline-coffee">
      Reset
    </a>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama</th>
          <th class="num">Harga Offline</th>
          <th class="num">Harga Online</th>
          <th>Kategori</th>
          <th>Gambar</th>
          <th style="width:180px;">Status</th>
          <th style="width:130px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse($produks as $produk)
          @php
            $isUsed = ((int) ($produk->reseps_count ?? 0) > 0)
                || ((int) ($produk->penjualan_details_count ?? 0) > 0);

            $kategori = $produk->kategori;
            $badgeClass = 'badge-secondary';
            if ($kategori === 'coffee') {
                $badgeClass = 'badge-success';
            } elseif ($kategori === 'non_coffee') {
                $badgeClass = 'badge-warning';
            } elseif ($kategori === 'snack') {
                $badgeClass = 'badge-danger';
            }

            $kategoriLabel = $produk->kategoriProduk?->nama
                ?? \Illuminate\Support\Str::of((string) $kategori)->replace('_', ' ')->title();
          @endphp

          <tr>
            <td>{{ $produk->kode_barang }}</td>
            <td>{{ $produk->nama_barang }}</td>

            <td class="num">
              Rp {{ number_format((float) $produk->harga, 0, ',', '.') }}
            </td>

            <td class="num">
              Rp {{ number_format((float) $produk->harga_online, 0, ',', '.') }}
            </td>

            <td>
              @if ($kategori)
                <span class="badge {{ $badgeClass }}">
                  {{ $kategoriLabel }}
                </span>
              @else
                <span class="muted">-</span>
              @endif
            </td>

            <td>
              @if ($produk->gambar)
                <img src="{{ asset('storage/' . $produk->gambar) }}"
                     style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
              @else
                <span class="muted">-</span>
              @endif
            </td>

            <td>
              <form action="{{ route('produk.toggle-status', $produk) }}" method="POST" class="status-toggle-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="aktif" value="{{ $produk->aktif ? 0 : 1 }}">
                <label class="form-switch" title="{{ $produk->aktif ? 'Nonaktifkan produk' : 'Aktifkan produk' }}">
                  <input type="checkbox" {{ $produk->aktif ? 'checked' : '' }} onchange="this.form.submit()">
                  <span class="form-switch-track">
                    <span class="form-switch-thumb"></span>
                  </span>
                  <span class="form-switch-label">{{ $produk->aktif ? 'On' : 'Off' }}</span>
                </label>
              </form>
            </td>

            <td>
              <div class="actions">
                <a href="{{ route('produk.edit', $produk) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-aksi"><path d="M4 20h4l10.5-10.5a2.121 2.121 0 1 0-3-3L5 17v3Z" /><path d="m13.5 6.5 4 4" /></svg>
                  <span class="sr-only">Edit</span>
                </a>

                @if (!$isUsed)
                  <form action="{{ route('produk.destroy', $produk) }}"
                        method="POST"
                        onsubmit="return confirm('Hapus produk ini?')">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn--outline-danger btn--sm btn--icon"
                            title="Hapus">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-aksi"><path d="M3 6h18" /><path d="M8 6V4.75A1.75 1.75 0 0 1 9.75 3h4.5A1.75 1.75 0 0 1 16 4.75V6" /><path d="M6.75 6l.7 11.2A2 2 0 0 0 9.44 19h5.12a2 2 0 0 0 1.99-1.8L17.25 6" /><path d="M10 10.25v5.5" /><path d="M14 10.25v5.5" /></svg>
                      <span class="sr-only">Hapus</span>
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="muted" style="text-align:center;">
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
      closeAll();
      if (willOpen) dd.classList.add('open');
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
