@push('styles')
  {{-- pakai tema yang sama dengan Bahan Baku biar konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Laporan Jurnal')

@section('content')
<div class="card">

  {{-- HEADER --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;">Laporan Jurnal</h2>
      <p class="muted" style="margin:4px 0 0;font-size:0.9rem;">
        Rekap seluruh jurnal umum (penjualan &amp; pembelian) berdasarkan tanggal.
      </p>
    </div>

    {{-- (opsional) tombol export nanti aja --}}
    {{-- <a href="#" class="btn btn--outline-coffee btn--with-icon">
      <x-heroicon-o-arrow-down-tray class="icon-inline" />
      <span>Export</span>
    </a> --}}
  </div>

  {{-- FILTER BAR --}}
  <form method="GET"
        action="{{ route('laporan.jurnal.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:flex-end;flex-wrap:wrap;">

    {{-- Periode: Dari --}}
    <div class="form-group" style="min-width:170px;">
      <label for="from" class="muted" style="font-size:0.8rem;display:block;margin-bottom:4px;">
        Dari Tanggal
      </label>
      <input type="date"
             id="from"
             name="from"
             value="{{ $from ?? '' }}"
             style="width:100%;">
    </div>

    {{-- Periode: Sampai --}}
    <div class="form-group" style="min-width:170px;">
      <label for="to" class="muted" style="font-size:0.8rem;display:block;margin-bottom:4px;">
        Sampai Tanggal
      </label>
      <input type="date"
             id="to"
             name="to"
             value="{{ $to ?? '' }}"
             style="width:100%;">
    </div>

    {{-- Filter sumber transaksi (optional, sementara cuma kirim apa adanya) --}}
    <div class="dd" style="min-width:220px;">
      @php
        $selectedSource = $source ?? 'all';
        $labelSource = match($selectedSource) {
          'sales'    => 'Penjualan',
          'purchase' => 'Pembelian',
          default    => 'Semua Sumber',
        };
      @endphp

      <label class="muted" style="font-size:0.8rem;display:block;margin-bottom:4px;">
        Sumber Transaksi
      </label>

      <button type="button" class="dd-toggle">
        <span class="dd-label">{{ $labelSource }}</span>
        <span class="dd-caret"></span>
      </button>
      <div class="dd-menu">
        <div class="dd-item {{ $selectedSource === 'all' ? 'active' : '' }}" data-value="all">
          Semua Sumber
        </div>
        <div class="dd-item {{ $selectedSource === 'sales' ? 'active' : '' }}" data-value="sales">
          Penjualan
        </div>
        <div class="dd-item {{ $selectedSource === 'purchase' ? 'active' : '' }}" data-value="purchase">
          Pembelian
        </div>
      </div>
      <input type="hidden" name="source" value="{{ $selectedSource }}">
    </div>

    {{-- TOMBOL AKSI --}}
    <div style="display:flex;gap:8px;align-items:center;">
      <button class="btn btn--outline-coffee" type="submit">
        Terapkan
      </button>

      <a href="{{ route('laporan.jurnal.index') }}" class="btn btn--outline-coffee">
        Reset
      </a>
    </div>
  </form>

  {{-- INFO RINGKAS PERIODE --}}
  <div style="margin-bottom:8px;font-size:0.85rem;" class="muted">
    Periode:
    @if(!empty($from) || !empty($to))
      <strong>
        {{ $from ? \Illuminate\Support\Carbon::parse($from)->format('d M Y') : 'awal' }}
        &mdash;
        {{ $to ? \Illuminate\Support\Carbon::parse($to)->format('d M Y') : 'sekarang' }}
      </strong>
    @else
      <strong>Semua tanggal</strong>
    @endif

    @if(($selectedSource ?? 'all') !== 'all')
      &nbsp;|&nbsp;
      Sumber:
      <strong>
        {{ $selectedSource === 'sales' ? 'Penjualan' : 'Pembelian' }}
      </strong>
    @endif
  </div>

  {{-- TABEL JURNAL --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="width:110px;">Tanggal</th>
          <th style="width:140px;">No. Jurnal</th>
          <th style="width:160px;">Referensi</th>
          <th>Akun</th>
          <th class="num" style="width:140px;">Debit</th>
          <th class="num" style="width:140px;">Kredit</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($rows as $row)
          <tr>
            {{-- Tanggal --}}
            <td>
              {{ $row->date
                  ? \Illuminate\Support\Carbon::parse($row->date)->format('d M Y')
                  : '—' }}
            </td>

            {{-- No. Jurnal --}}
            <td>
              {{ $row->entry_no ?? '-' }}
            </td>

            {{-- Referensi --}}
            <td>
              @if(!empty($row->ref_no))
                <span class="badge">{{ $row->ref_no }}</span>
              @else
                <span class="muted">—</span>
              @endif
            </td>

            {{-- Akun (ringkasan akun dari controller: $row->akun_ringkas) --}}
            <td>
              @if(!empty($row->akun_ringkas) || !empty($row->memo))
                <div style="display:flex;flex-direction:column;gap:2px;">
                  <span>{{ $row->akun_ringkas ?? '—' }}</span>
                  @if(!empty($row->memo))
                    <span class="muted" style="font-size:0.8rem;">
                      {{ $row->memo }}
                    </span>
                  @endif
                </div>
              @else
                <span class="muted">—</span>
              @endif
            </td>

            {{-- Debit --}}
            <td class="num">
              @if(isset($row->total_debit) && $row->total_debit > 0)
                Rp {{ number_format((float) $row->total_debit, 0, ',', '.') }}
              @else
                —
              @endif
            </td>

            {{-- Kredit --}}
            <td class="num">
              @if(isset($row->total_credit) && $row->total_credit > 0)
                Rp {{ number_format((float) $row->total_credit, 0, ',', '.') }}
              @else
                —
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="muted" style="text-align:center;">
              Belum ada data jurnal untuk filter yang dipilih.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div style="margin-top:12px;">
    {{ $rows->links('pagination::cofit') }}
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
