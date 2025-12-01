@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Bahan Baku')


@section('content')
<div class="card">
  {{-- Header --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Bahan Baku</h2>
    <a href="{{ route('bahan-baku.create') }}"
   class="btn btn--outline-success btn--with-icon">
    <x-heroicon-o-plus class="icon-inline" />
    <span>Buat Baru</span>
    </a>
  </div>

  {{-- Filter & Search --}}
  <form method="GET" action="{{ route('bahan-baku.index') }}" class="filter-bar" style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">
    <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode/nama…" />

    {{-- Kategori (custom dropdown .dd) --}}
    @php $selKat = $selectedKategori ?? ''; @endphp
    <div class="dd" style="min-width:220px">
      <button type="button" class="dd-toggle">
        <span class="dd-label">
          {{ $selKat ? ucfirst($selKat) : 'Semua kategori' }}
        </span>
        <span class="dd-caret"></span>
      </button>
      <div class="dd-menu">
        <div class="dd-item {{ $selKat==='' ? 'active':'' }}" data-value="">Semua kategori</div>
        @foreach($opsiKategori as $opt)
          <div class="dd-item {{ $selKat===$opt ? 'active':'' }}" data-value="{{ $opt }}">{{ ucfirst($opt) }}</div>
        @endforeach
      </div>
      <input type="hidden" name="kategori" value="{{ $selKat }}">
    </div>

    {{-- Aktif / Nonaktif (custom dropdown .dd) --}}
    @php $selAkt = $selectedAktif ?? ''; @endphp
    <div class="dd" style="min-width:200px">
      <button type="button" class="dd-toggle">
        <span class="dd-label">
          @switch($selAkt)
            @case('1') Aktif @break
            @case('0') Nonaktif @break
            @default Aktif / Nonaktif
          @endswitch
        </span>
        <span class="dd-caret"></span>
      </button>
      <div class="dd-menu">
        <div class="dd-item {{ $selAkt==='' ? 'active':'' }}" data-value="">Aktif / Nonaktif</div>
        <div class="dd-item {{ $selAkt==='1' ? 'active':'' }}" data-value="1">Aktif</div>
        <div class="dd-item {{ $selAkt==='0' ? 'active':'' }}" data-value="0">Nonaktif</div>
      </div>
      <input type="hidden" name="aktif" value="{{ $selAkt }}">
    </div>

    <input type="hidden" name="sort" value="{{ $sort }}">
    <input type="hidden" name="dir"  value="{{ $dir  }}">

    {{-- Tombol tema kopi: outline → filled on hover --}}
    <button class="btn btn--outline-coffee">Terapkan</button>
    <a class="btn btn--outline-coffee">Reset</a>
    {{-- TOMBOL BARU: LINK KE HALAMAN MONITORING DETAIL --}}

  </form>

  {{-- Tabel --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama</th>
          <th>Kategori</th>
          <th>Satuan</th>
          <th>Satuan Pakai</th>
          <th class="num">Stok</th>
          <th>Min Stok</th>
          <th class="num">Harga Beli</th>
          <th>Aktif</th>
          <th>Created</th>
          <th style="width:130px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($items as $row)
          @php
            $last  = $row->pembelianDetails->first();
            $harga = $last?->harga_satuan;
          @endphp

          <tr>
            <td>{{ $row->kode_bahan }}</td>
            <td>{{ $row->nama_bahan }}</td>

            <td>
              @if ($row->kategori)
                <span class="badge">{{ $row->kategori }}</span>
              @else
                <span class="muted">–</span>
              @endif
            </td>

            <td>{{ $row->satuan_beli ?? '–' }}</td>
            <td>{{ $row->satuan_pakai }}</td>

            {{-- STOK SAAT INI (dari accessor BahanBaku::getStokAttribute) --}}
            <td class="num">{{ $row->stok }}</td>

            <td>–</td>

            <td class="num">
              {{ $harga ? 'Rp '.number_format((float) $harga, 0, ',', '.') : '–' }}
            </td>

            <td>
              <span class="bool {{ $row->aktif ? 'bool--yes' : 'bool--no' }}">
                <span class="bool-dot"></span>
                {{ $row->aktif ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>

            <td>{{ optional($row->created_at)->diffForHumans() }}</td>

            <td>
              <div class="actions">
                {{-- EDIT --}}
                <a href="{{ route('bahan-baku.edit', $row) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <x-heroicon-o-pencil-square class="icon-aksi" />
                  <span class="sr-only">Edit</span>
                </a>

                {{-- HAPUS --}}
                <form action="{{ route('bahan-baku.destroy', $row) }}"
                      method="POST"
                      onsubmit="return confirm('Hapus item ini?')">
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
            <td colspan="11" class="muted" style="text-align:center;">
              Belum ada data.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:12px;">
    {{ $items->links('pagination::cofit') }}
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
