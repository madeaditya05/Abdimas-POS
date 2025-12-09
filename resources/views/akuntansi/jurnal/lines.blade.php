@push('styles')
  {{-- sementara pakai CSS yang sama dengan Bahan Baku biar look & feel konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Detail Baris Jurnal')

@section('content')
<div class="card">
  {{-- HEADER --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;">Detail Baris Jurnal</h2>
      <p class="muted" style="margin:4px 0 0;font-size:0.9rem;">
        Daftar seluruh baris jurnal (Journal Lines) untuk keperluan tracing.
      </p>
    </div>

    <a href="{{ route('laporan.jurnal.index') }}"
       class="btn btn--outline-coffee btn--with-icon">
      <x-heroicon-o-book-open class="icon-inline" />
      <span>Laporan Jurnal</span>
    </a>
  </div>

  {{-- FILTER BAR --}}
  <form method="GET"
        action="{{ route('akuntansi.jurnal.lines') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:flex-end;flex-wrap:wrap;">

    {{-- Search global --}}
    <div class="form-group" style="min-width:220px;">
      <label for="q" class="muted" style="font-size:0.8rem;display:block;margin-bottom:4px;">
        Cari (No. Jurnal / Referensi / Nama Akun)
      </label>
      <input type="text"
             id="q"
             name="q"
             value="{{ $search ?? '' }}"
             placeholder="Misal: PJL-0001 atau Kas"
             style="width:100%;">
    </div>

    {{-- Akun (dropdown .dd) --}}
    <div class="form-group" style="min-width:230px;">
      <label class="muted" style="font-size:0.8rem;display:block;margin-bottom:4px;">
        Filter Akun
      </label>

      @php
        $selAkun = $accountId ? (string)$accountId : '';
        $labelAkun = 'Semua akun';
        if ($selAkun && isset($opsiAkun[$accountId])) {
            $labelAkun = $opsiAkun[$accountId];
        }
      @endphp

      <div class="dd" style="width:100%;">
        <button type="button" class="dd-toggle">
          <span class="dd-label" style="max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            {{ $labelAkun }}
          </span>
          <span class="dd-caret"></span>
        </button>
        <div class="dd-menu">
          <div class="dd-item {{ $selAkun === '' ? 'active' : '' }}" data-value="">
            Semua akun
          </div>
          @foreach($opsiAkun as $id => $nama)
            <div class="dd-item {{ (string)$id === $selAkun ? 'active' : '' }}" data-value="{{ $id }}">
              {{ $nama }}
            </div>
          @endforeach
        </div>
        <input type="hidden" name="account_id" value="{{ $selAkun }}">
      </div>
    </div>

    {{-- Periode: dari / sampai --}}
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

    {{-- Tombol aksi --}}
    <div class="form-group" style="display:flex;gap:8px;">
      <button class="btn btn--outline-coffee" type="submit">
        Terapkan
      </button>
      <a href="{{ route('akuntansi.jurnal.lines') }}" class="btn btn--outline-coffee">
        Reset
      </a>
    </div>
  </form>

  {{-- INFO PERIODE --}}
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

    @if($selAkun !== '')
      &nbsp;|&nbsp; Akun:
      <strong>{{ $labelAkun }}</strong>
    @endif
  </div>

  {{-- TABEL LINES --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="width:110px;">Tanggal</th>
          <th style="width:140px;">No. Jurnal</th>
          <th style="width:140px;">Referensi</th>
          <th style="width:120px;">Kode Akun</th>
          <th>Nama Akun</th>
          <th class="num" style="width:130px;">Debit</th>
          <th class="num" style="width:130px;">Kredit</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          @php
            $entry = $row->entry;
            $coa   = $row->coa;
          @endphp
          <tr>
            <td>
              @if($entry?->date)
                {{ \Illuminate\Support\Carbon::parse($entry->date)->format('d M Y') }}
              @else
                —
              @endif
            </td>
            <td>
              @if($entry)
                <a href="{{ route('laporan.jurnal.show', $entry->id) }}"
                   class="link-muted">
                  {{ $entry->entry_no ?? '-' }}
                </a>
              @else
                —
              @endif
            </td>
            <td>
              @if($entry?->ref_no)
                <span class="badge">{{ $entry->ref_no }}</span>
              @else
                <span class="muted">—</span>
              @endif
            </td>
            <td>
              <strong>{{ $coa->code ?? '-' }}</strong>
            </td>
            <td>
              {{ $coa->name ?? '—' }}
            </td>
            <td class="num">
              {{ $row->debit > 0 ? 'Rp '.number_format((float)$row->debit, 0, ',', '.') : '—' }}
            </td>
            <td class="num">
              {{ $row->credit > 0 ? 'Rp '.number_format((float)$row->credit, 0, ',', '.') : '—' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="muted" style="text-align:center;">
              Belum ada data baris jurnal untuk filter yang dipilih.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
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
