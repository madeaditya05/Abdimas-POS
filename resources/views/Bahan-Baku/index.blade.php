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
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-inline"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
      <span>Buat Baru</span>
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

          {{-- <th>Kategori</th> --}}
          {{-- <th>Satuan</th> --}}

          <th class="num">Stok</th>
          <th>Satuan Pakai</th>

          {{-- <th>Min Stok</th> --}}
          {{-- <th class="num">Harga Beli</th> --}}
          {{-- <th>Aktif</th> --}}
          {{-- <th>Created</th> --}}

          <th style="width:130px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($items as $row)
          @php
            $last  = $row->pembelianDetails->first();
            $harga = $last?->harga_satuan;
            $isUsed = ((int) ($row->resep_details_count ?? 0) > 0)
                || ((int) ($row->pembelian_details_count ?? 0) > 0)
                || ((int) ($row->mutasi_count ?? 0) > 0);
          @endphp

          <tr>
            <td>{{ $row->kode_bahan }}</td>
            <td>{{ $row->nama_bahan }}</td>

            {{-- KATEGORI --}}
            {{-- <td>
              @if ($row->kategori)
                <span class="badge">{{ $row->kategori }}</span>
              @else
                <span class="muted">–</span>
              @endif
            </td> --}}

            {{-- SATUAN BELI --}}
            {{-- <td>{{ $row->satuan_beli ?? '–' }}</td> --}}

            {{-- STOK SAAT INI (dari accessor BahanBaku::getStokAttribute) --}}
            <td class="num">{{ $row->stok }}</td>

            {{-- SATUAN PAKAI (TETAP DITAMPILKAN) --}}
            <td>{{ $row->satuan_pakai }}</td>

            {{-- MIN STOK --}}
            {{-- <td>–</td> --}}

            {{-- HARGA BELI --}}
            {{-- <td class="num">
              {{ $harga ? 'Rp '.number_format((float) $harga, 0, ',', '.') : '–' }}
            </td> --}}

            {{-- STATUS AKTIF --}}
            {{-- <td>
              <span class="bool {{ $row->aktif ? 'bool--yes' : 'bool--no' }}">
                <span class="bool-dot"></span>
                {{ $row->aktif ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td> --}}

            {{-- CREATED --}}
            {{-- <td>{{ optional($row->created_at)->diffForHumans() }}</td> --}}

            <td>
              <div class="actions">
                {{-- EDIT --}}
                <a href="{{ route('bahan-baku.edit', $row) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-aksi"><path d="M4 20h4l10.5-10.5a2.121 2.121 0 1 0-3-3L5 17v3Z" /><path d="m13.5 6.5 4 4" /></svg>
                  <span class="sr-only">Edit</span>
                </a>

                @if (!$isUsed)
                  {{-- HAPUS --}}
                  <form action="{{ route('bahan-baku.destroy', $row) }}"
                        method="POST"
                        onsubmit="return confirm('Hapus item ini?')">
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
            {{-- <td colspan="11" class="muted" style="text-align:center;"> --}}
            <td colspan="5" class="muted" style="text-align:center;">
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
