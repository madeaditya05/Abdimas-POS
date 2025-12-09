@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Resep (Bill of Material)')

@section('content')
<div class="card">
  {{-- Header --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Resep (Bill of Material)</h2>

    <a href="{{ route('resep.create') }}"
       class="btn btn--outline-success btn--with-icon">
      <x-heroicon-o-plus class="icon-inline" />
      <span>Buat Resep</span>
    </a>
  </div>

  {{-- Filter & Search --}}
  <form method="GET" action="{{ route('resep.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">

    {{-- Search produk --}}
    <input type="text"
           name="q"
           value="{{ $search }}"
           placeholder="Cari produk…"
    />

    {{-- Filter Produk (dropdown .dd) --}}
    @php $selProd = $selectedProduk ?? ''; @endphp
    <div class="dd" style="min-width:240px" data-select="produk">
      <button type="button" class="dd-toggle">
        <span class="dd-label">
          @if($selProd && isset($opsiProduk[$selProd]))
            {{ $opsiProduk[$selProd] }}
          @else
            Semua produk
          @endif
        </span>
        <span class="dd-caret"></span>
      </button>
      <div class="dd-menu">
        <div class="dd-item {{ $selProd==='' ? 'active':'' }}" data-value="">Semua produk</div>
        @foreach($opsiProduk as $id => $nama)
          <div class="dd-item {{ (string)$selProd===(string)$id ? 'active':'' }}"
               data-value="{{ $id }}">
            {{ $nama }}
          </div>
        @endforeach
      </div>
      <input type="hidden" name="produk_id" value="{{ $selProd }}">
    </div>

    {{-- Filter Aktif / Nonaktif --}}
    @php $selAkt = $selectedAktif ?? ''; @endphp
    <div class="dd" style="min-width:190px" data-select="aktif">
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

    <button class="btn btn--outline-coffee">Terapkan</button>
    <a href="{{ route('resep.index') }}" class="btn btn--outline-coffee">Reset</a>
    <a href="{{ route('resep-detail.index') }}" class="btn btn--outline-coffee">
      Resep Detail
    </a>
  </form>

  {{-- Tabel --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Produk</th>
          <th>Kategori</th>
          <th>Status</th>

          {{-- <th># Bahan</th> --}}
          {{-- <th>Catatan</th> --}}
          {{-- <th>Diupdate</th> --}}

          <th style="width:130px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          <tr>
            {{-- Produk --}}
            <td>{{ $row->produk->nama_barang ?? '-' }}</td>

            {{-- Kategori produk (badge kecil) --}}
            <td>
              @if(!empty($row->produk?->kategori))
                <span class="badge">{{ $row->produk->kategori }}</span>
              @else
                <span class="muted">–</span>
              @endif
            </td>

            {{-- Status (sebelumnya "Aktif") --}}
            <td>
              <span class="bool {{ $row->is_active ? 'bool--yes' : 'bool--no' }}">
                <span class="bool-dot"></span>
                {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>

            {{-- Jumlah bahan (from withCount details) --}}
            {{-- <td style="text-align:center;">
              {{ $row->details_count ?? 0 }}
            </td> --}}

            {{-- Catatan --}}
            {{-- <td>
              @if($row->catatan)
                {{ \Illuminate\Support\Str::limit($row->catatan, 60) }}
              @else
                <span class="muted">–</span>
              @endif
            </td> --}}

            {{-- Diupdate --}}
            {{-- <td>
              {{ optional($row->updated_at)->format('d M Y H:i') }}
            </td> --}}

            {{-- Aksi --}}
            <td>
              <div class="actions">
                {{-- EDIT --}}
                <a href="{{ route('resep.edit', $row) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <x-heroicon-o-pencil-square class="icon-aksi" />
                  <span class="sr-only">Edit</span>
                </a>

                {{-- HAPUS --}}
                <form action="{{ route('resep.destroy', $row) }}"
                      method="POST"
                      onsubmit="return confirm('Hapus resep ini?')">
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
            {{-- <td colspan="7" class="muted" style="text-align:center;"> --}}
            <td colspan="4" class="muted" style="text-align:center;">
              Belum ada resep.
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
  const closeAll = () => document.querySelectorAll('.dd.open')
    .forEach(dd => dd.classList.remove('open'));

  document.addEventListener('click', e => {
    if (!e.target.closest('.dd')) closeAll();
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeAll();
  });

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
        menu.querySelectorAll('.dd-item.active')
          .forEach(x => x.classList.remove('active'));

        item.classList.add('active');
        if (input) input.value = item.dataset.value ?? '';
        if (label) label.textContent = item.textContent.trim();
        dd.classList.remove('open');
      });
    });
  });
})();
</script>
@endpush
