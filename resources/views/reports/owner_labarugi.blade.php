@extends('layouts.main')
@section('title','Laporan Owner')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/jurnal_kasir.css') }}">
<link rel="stylesheet" href="{{ asset('assets/owner_report.css') }}">
@endpush


@section('content')
@php
  // default: semua seksi aktif
  $secDefault = ['labarugi','items','payments','unified','journal','ledger'];
  $secSel = request('sec', $secDefault);
  $secSel = is_array($secSel) ? $secSel : [$secSel];

  // buat judul otomatis
  $secTitle = [
    'labarugi' => 'Laba Rugi',
    'items'    => 'Rekap Per Produk',
    'payments' => 'Rekap Metode Pencatatan',
    'unified'  => 'Laporan Penjualan',
    'journal'  => 'Jurnal Umum',
    'ledger'   => 'Buku Besar',
  ];

  $pageTitle = (count($secSel) === 1)
      ? ($secTitle[$secSel[0]] ?? 'Laporan Owner')
      : 'Laporan Owner';

  $hidePicker = request()->has('sec'); // kalau klik dari sidebar, biasanya ada sec[]
@endphp

<div class="kr-page">
  <div class="kr-card">

    {{-- Header --}}
    <div class="kr-header">
      <div class="kr-title">{{ $pageTitle }}</div>
      <div class="kr-chipbar">
        <a class="kr-btn kr-btn-ghost"
   href="{{ route('owner.reports.menu', ['start_date'=>request('start_date', now()->toDateString()), 'end_date'=>request('end_date', now()->toDateString())]) }}">
  Ganti Laporan
</a>

        <div class="kr-chip">
          Periode:
          {{ \Carbon\Carbon::parse($meta['start'])->format('d/m/Y') }}
          – {{ \Carbon\Carbon::parse($meta['end'])->format('d/m/Y') }}
        </div>
      </div>
    </div>

    {{-- Filter --}}
    <form class="kr-filter" method="GET" action="{{ route('owner.labarugi') }}">
      <div class="kr-field">
        <label>Dari</label>
        <input type="date" name="start_date" value="{{ request('start_date', now()->toDateString()) }}">
      </div>
      <div class="kr-field">
        <label>Sampai</label>
        <input type="date" name="end_date" value="{{ request('end_date', now()->toDateString()) }}">
      </div>

      @if(in_array('ledger', $secSel))
      <div class="kr-field">
        <label>Akun</label>
        <select name="account_code" style="width: 100%; height: 42px; border: 1px solid #c7cdd4; border-radius: 12px; padding: 0 10px; background-color: #fff; color: #0f172a; font-size: 13px; outline: none;">
          <option value="">Semua Akun</option>
          @foreach($accounts as $acc)
            <option value="{{ $acc->code }}" {{ request('account_code') == $acc->code ? 'selected' : '' }}>
              {{ $acc->code }} - {{ $acc->name }}
            </option>
          @endforeach
        </select>
      </div>
      @endif

      {{-- Dropdown hanya tampil kalau tidak dari sidebar --}}
      @if(!$hidePicker)
      <details class="kr-dd">
        <summary class="kr-btn kr-btn-ghost">
          Pilih Laporan
          <svg width="16" height="16">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </summary>
        <div class="kr-dd-menu">
          <label><input class="sec-check" type="checkbox" name="sec[]" value="labarugi" {{ in_array('labarugi',$secSel)?'checked':'' }}> Laba Rugi</label>
          <label><input class="sec-check" type="checkbox" name="sec[]" value="items"    {{ in_array('items',$secSel)?'checked':'' }}> Rekap Per Produk</label>
          <label><input class="sec-check" type="checkbox" name="sec[]" value="payments" {{ in_array('payments',$secSel)?'checked':'' }}> Rekap Per Metode Pencatatan</label>
          <label><input class="sec-check" type="checkbox" name="sec[]" value="unified"  {{ in_array('unified',$secSel)?'checked':'' }}> Laporan Penjualan</label>
          <label><input class="sec-check" type="checkbox" name="sec[]" value="journal"  {{ in_array('journal',$secSel)?'checked':'' }}> Jurnal Umum</label>
          <label><input class="sec-check" type="checkbox" name="sec[]" value="ledger"   {{ in_array('ledger',$secSel)?'checked':'' }}> Buku Besar</label>
          <hr>
          <label>
            <input id="sec-all" type="checkbox"
                   onclick="document.querySelectorAll('.sec-check').forEach(cb=>cb.checked=this.checked)">
            Pilih semua
          </label>
        </div>
      </details>
      @else
        {{-- kalau dari sidebar: bawa sec[] yang sudah dipilih agar tidak hilang pas filter tanggal --}}
        @foreach($secSel as $s)
          <input type="hidden" name="sec[]" value="{{ $s }}">
        @endforeach
      @endif

      {{-- Tombol --}}
      <div class="kr-actions">
      <button class="kr-btn kr-btn-primary" type="submit">Tampilkan</button>

      @php
        $pdfSel = [
          'start_date'=>request('start_date', now()->toDateString()),
          'end_date'  =>request('end_date', now()->toDateString()),
          'sec'       =>$secSel,
        ];
      @endphp

      <a class="kr-btn kr-btn-ghost" target="_blank" href="{{ route('owner.labarugi.pdf', $pdfSel) }}">
        PDF (sesuai pilihan)
      </a>
      <a class="kr-btn kr-btn-ghost" target="_blank" href="{{ route('owner.labarugi.excel', $pdfSel) }}">
        Excel (sesuai pilihan)
      </a>
    </div>
    </form>

    <div class="kr-sep"></div>

    {{-- Ringkasan Laba Rugi --}}
    @if(in_array('labarugi',$secSel))
      <div class="kr-section">
        <div class="kr-section-title">Ringkasan Laba Rugi</div>
        @php
          $rev   = (float)($lr['revenue'] ?? 0);
          $cogs  = (float)($lr['cogs'] ?? 0);
          $gross = (float)($rev - $cogs);
          $exp   = (float)($lr['expense'] ?? 0);
          $net   = (float)($gross - $exp);
        @endphp
        <table class="kr-table">
          <tbody>
            <tr><td>Pendapatan</td><td class="kr-right kr-money">Rp {{ number_format($rev,0,',','.') }}</td></tr>
            <tr><td>HPP</td><td class="kr-right kr-money">Rp {{ number_format($cogs,0,',','.') }}</td></tr>
            <tr><td>Laba Kotor</td><td class="kr-right kr-money">Rp {{ number_format($gross,0,',','.') }}</td></tr>
            <tr><td>Beban</td><td class="kr-right kr-money">Rp {{ number_format($exp,0,',','.') }}</td></tr>
            <tr><td>Laba Bersih</td><td class="kr-right kr-money">Rp {{ number_format($net,0,',','.') }}</td></tr>
          </tbody>
        </table>

        @if(!($lr['is_closed'] ?? false))
          <div style="margin-top:12px;padding:12px 14px;border:1px solid #fed7aa;background:#fff7ed;border-radius:12px;color:#9a3412;">
            HPP pada laporan ini mengikuti metode periodik dan baru terisi setelah proses tutup buku.
            Untuk periode {{ \Carbon\Carbon::parse($meta['start'])->format('d/m/Y') }} sampai {{ \Carbon\Carbon::parse($meta['end'])->format('d/m/Y') }}, closing belum ditemukan, jadi HPP masih ditampilkan `0`.
          </div>
        @else
          <div style="margin-top:12px;padding:12px 14px;border:1px solid #bfdbfe;background:#eff6ff;border-radius:12px;color:#1d4ed8;">
            HPP pada laporan ini diambil dari hasil tutup buku periode yang sudah di-closing.
          </div>
        @endif
      </div>
    @endif

    {{-- Seksi lain pakai partial kasir --}}
    @if(in_array('items',$secSel))
      @include('reports.partials.kasir_items',['items'=>$items])
    @endif

    @if(in_array('payments',$secSel))
      @include('reports.partials.kasir_payments',['payments'=>$payments])
    @endif

    @if(in_array('unified',$secSel))
      @include('reports.partials.owner_sales',['sales'=>$sales])
    @endif

    @if(in_array('journal',$secSel))
      @include('reports.partials.kasir_jurnal',['journal'=>$journal])
    @endif

    @if(in_array('ledger',$secSel))
      @include('reports.partials.kasir_ledger',['ledger'=>$ledger])
    @endif

  </div>
</div>
@endsection
