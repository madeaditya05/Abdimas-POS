@extends('layouts.main')
@section('title','Pilih Laporan')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/owner_menu.css') }}">
@endpush

@section('content')
@php
  $startDate = $start ?? now()->toDateString();
  $endDate   = $end ?? now()->toDateString();
  $base = ['start_date' => $startDate, 'end_date' => $endDate];

  // helper untuk bikin link mudah
  $toOwner = fn($sec) => route('owner.labarugi', array_merge($base, ['sec' => $sec]));
@endphp

<div class="om-page">
  <div class="om-card">

    <div class="om-header">
      <div>
        <div class="om-title">Pilih Laporan Hari Ini</div>
        <div class="om-subtitle">Atur tanggal dulu, lalu pilih jenis laporan yang mau dilihat.</div>
      </div>
    </div>

    <form class="om-form" method="GET" action="{{ route('owner.reports.menu') }}">
      <div class="om-field">
        <label>Dari</label>
        <input class="om-input" type="date" name="start_date" value="{{ $startDate }}">
      </div>
      <div class="om-field">
        <label>Sampai</label>
        <input class="om-input" type="date" name="end_date" value="{{ $endDate }}">
      </div>
      <div class="om-actions">
        <button class="om-btn om-btn--primary" type="submit">
          <span>Set Tanggal</span>
        </button>
      </div>
    </form>

    <div class="om-divider"></div>

    <div class="om-section">
      <div class="om-section__label">AKUNTANSI</div>
      <div class="om-grid">
        {{-- Jurnal Umum --}}
        <a class="om-tile" href="{{ $toOwner('journal') }}">
          <div class="om-tile__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M4 4h14v16H4z"/><path d="M8 8h6"/><path d="M8 12h8"/><path d="M8 16h8"/>
              <path d="M18 8h2v12h-2"/>
            </svg>
          </div>
          <div class="om-tile__body">
            <div class="om-tile__title">Jurnal Umum</div>
            <div class="om-tile__desc">Daftar transaksi jurnal pada periode dipilih.</div>
          </div>
        </a>

        {{-- Buku Besar --}}
        <a class="om-tile" href="{{ $toOwner('ledger') }}">
          <div class="om-tile__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M6 4h12v16H6z"/><path d="M9 8h6"/><path d="M9 12h6"/><path d="M9 16h6"/>
            </svg>
          </div>
          <div class="om-tile__body">
            <div class="om-tile__title">Buku Besar</div>
            <div class="om-tile__desc">Saldo per akun dari jurnal dengan running balance.</div>
          </div>
        </a>

        {{-- Laba Rugi --}}
        <a class="om-tile" href="{{ $toOwner('labarugi') }}">
          <div class="om-tile__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M4 19V5"/><path d="M4 19h16"/><path d="M7 14l3-3 3 2 5-6"/>
            </svg>
          </div>
          <div class="om-tile__body">
            <div class="om-tile__title">Laba Rugi</div>
            <div class="om-tile__desc">Ringkasan pendapatan, HPP, beban, dan laba bersih.</div>
          </div>
        </a>

        {{-- Tutup Buku --}}
        <a class="om-tile" href="{{ route('owner.tutupbuku', $base) }}">
          <div class="om-tile__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M6 2h10l2 2v18H6z"/>
              <path d="M9 7h6"/><path d="M9 11h6"/><path d="M9 15h6"/>
              <path d="M8 20h8"/>
            </svg>
          </div>
          <div class="om-tile__body">
            <div class="om-tile__title">Tutup Buku</div>
            <div class="om-tile__desc">Closing periodik untuk posting HPP dan penyesuaian persediaan.</div>
          </div>
        </a>
      </div>
    </div>

    <div class="om-section">
      <div class="om-section__label">OPERASIONAL</div>
      <div class="om-grid">
        {{-- Laporan Rekapitulasi Pembayaran --}}
        <a class="om-tile" href="{{ $toOwner('payments') }}">
          <div class="om-tile__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M4 7h16a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2z"/>
              <path d="M6 12h4"/>
            </svg>
          </div>
          <div class="om-tile__body">
            <div class="om-tile__title">Laporan Rekapitulasi Pembayaran</div>
            <div class="om-tile__desc">Ringkasan performa penjualan pada periode yang dipilih.</div>
          </div>
        </a>

        {{-- Laporan Pembelian --}}
        <a class="om-tile"
          href="{{ route('pembelian-bahan-detail.index', [
                'start_date' => $startDate,
                'end_date'   => $endDate,
          ]) }}">
          <div class="om-tile__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M4 4h16v16H4z"/>
              <path d="M7 8h10"/>
              <path d="M7 12h10"/>
              <path d="M7 16h6"/>
              <path d="M16 16h1"/>
            </svg>
          </div>
          <div class="om-tile__body">
            <div class="om-tile__title">Laporan Pembelian</div>
            <div class="om-tile__desc">Rekap pembelian bahan per periode beserta ringkasan nilainya.</div>
          </div>
        </a>

        {{-- Laporan Penjualan (Ringkas) --}}
        <a class="om-tile" href="{{ $toOwner('unified') }}">
          <div class="om-tile__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M12 3v18"/><path d="M7 7h10"/><path d="M7 17h10"/>
              <path d="M9 11h6"/><path d="M9 13h6"/>
            </svg>
          </div>
          <div class="om-tile__body">
            <div class="om-tile__title">Laporan Penjualan</div>
            <div class="om-tile__desc">Ringkasan penjualan per tanggal, kasir, metode, dan produk.</div>
          </div>
        </a>

        {{-- Piutang Invoice --}}
        <a class="om-tile" href="{{ route('invoices.index') }}">
          <div class="om-tile__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M6 2h9l3 3v17H6z"/>
              <path d="M15 2v5h5"/>
              <path d="M9 11h6"/>
              <path d="M9 15h6"/>
              <path d="M9 19h4"/>
            </svg>
          </div>
          <div class="om-tile__body">
            <div class="om-tile__title">Piutang Invoice</div>
            <div class="om-tile__desc">Pantau invoice tempo yang menunggu pembayaran atau sudah overdue.</div>
          </div>
        </a>

        {{-- Beban Operasional --}}
        <a class="om-tile" href="{{ route('beban-operasional.index') }}">
          <div class="om-tile__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M6 2h9l3 3v17H6z"/>
              <path d="M9 9h6"/>
              <path d="M9 13h6"/>
              <path d="M9 17h6"/>
            </svg>
          </div>
          <div class="om-tile__body">
            <div class="om-tile__title">Input Beban Operasional</div>
            <div class="om-tile__desc">Kelola dan pantau beban usaha yang memengaruhi laba.</div>
          </div>
        </a>
      </div>
    </div>

  </div>
</div>
@endsection
