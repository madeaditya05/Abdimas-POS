@push('styles')
  {{-- Reuse CSS bahanbaku biar tampilannya konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Mutasi Stok')

@section('content')
<div class="card">
  {{-- Header --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Mutasi Stok</h2>
  </div>

  {{-- Filter & Search --}}
  <form method="GET"
        action="{{ route('mutasi-stok.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">

    {{-- Search bahan --}}
    <input type="text"
           name="q"
           value="{{ $search }}"
           placeholder="Cari kode/nama bahan…" />

    {{-- Filter tipe --}}
    @php $selTipe = $selectedTipe ?? ''; @endphp
    <div class="dd" style="min-width:200px">
      <button type="button" class="dd-toggle">
        <span class="dd-label">
          @switch($selTipe)
            @case('IN')  IN (Masuk) @break
            @case('OUT') OUT (Keluar) @break
            @case('ADJ') ADJ (Penyesuaian) @break
            @default Semua tipe
          @endswitch
        </span>
        <span class="dd-caret"></span>
      </button>
      <div class="dd-menu">
        <div class="dd-item {{ $selTipe==='' ? 'active':'' }}" data-value="">Semua tipe</div>
        <div class="dd-item {{ $selTipe==='IN'  ? 'active':'' }}" data-value="IN">IN (Masuk)</div>
        <div class="dd-item {{ $selTipe==='OUT' ? 'active':'' }}" data-value="OUT">OUT (Keluar)</div>
        <div class="dd-item {{ $selTipe==='ADJ' ? 'active':'' }}" data-value="ADJ">ADJ (Penyesuaian)</div>
      </div>
      <input type="hidden" name="tipe" value="{{ $selTipe }}">
    </div>

    {{-- Filter tanggal --}}
    <div class="form-field" style="margin:0;">
      <label style="font-size:12px;display:block;margin-bottom:2px;">Dari</label>
      <input type="date" name="from" value="{{ $from }}" />
    </div>

    <div class="form-field" style="margin:0;">
      <label style="font-size:12px;display:block;margin-bottom:2px;">Sampai</label>
      <input type="date" name="to" value="{{ $to }}" />
    </div>

    <input type="hidden" name="sort" value="{{ $sort }}">
    <input type="hidden" name="dir"  value="{{ $dir  }}">

    <button class="btn btn--outline-coffee">Terapkan</button>
    <a href="{{ route('mutasi-stok.index') }}" class="btn btn--outline-coffee">Reset</a>
  </form>

  {{-- Tabel --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Bahan Baku</th>
          <th>Tipe</th>
          <th class="num">Jumlah</th>
          <th>Tanggal</th>
          <th>Sumber</th>
          <th>ID Sumber</th>
          <th>Catatan</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($items as $row)
          @php
            $bahan  = $row->bahan;
            $tipe   = $row->tipe;
            $badgeClass = match ($tipe) {
              'IN'  => 'badge--success',
              'OUT' => 'badge--danger',
              'ADJ' => 'badge--warning',
              default => 'badge--muted',
            };

            $srcName = $row->sumber_type ? class_basename($row->sumber_type) : null;
          @endphp

          <tr>
            {{-- Bahan --}}
            <td>
              @if($bahan)
                <div>{{ $bahan->nama_bahan }}</div>
                <div class="muted" style="font-size:12px;">
                  {{ $bahan->kode_bahan ?? '' }}
                </div>
              @else
                <span class="muted">(bahan tidak ada)</span>
              @endif
            </td>

            {{-- Tipe --}}
            <td>
              <span class="badge {{ $badgeClass }}">
                {{ $tipe }}
              </span>
            </td>

            {{-- Qty --}}
            <td class="num">
              {{ number_format((float) $row->qty, 2, ',', '.') }}
            </td>

            {{-- Tanggal --}}
            <td>
              {{ optional($row->tanggal)->format('d M Y H:i') }}
            </td>

            {{-- Sumber --}}
            <td>
              @if($srcName)
                <span class="badge badge--soft">
                  {{ $srcName }}
                </span>
              @else
                <span class="muted">-</span>
              @endif
            </td>

            {{-- ID sumber --}}
            <td>
              {{ $row->sumber_id ?? '-' }}
            </td>

            {{-- Catatan --}}
            <td>
              {{ $row->note ?? '-' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="muted" style="text-align:center;">
              Belum ada mutasi stok.
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
