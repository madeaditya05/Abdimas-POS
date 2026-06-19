@extends('layouts.main')
@section('title','Rekap Kasir')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/jurnal_kasir.css') }}">

{{-- ====== Add-on styling khusus tombol & dropdown di halaman ini ====== --}}
<style>
/* Variables */
.kr-page{
  --bg:#fff; --soft:#f8fafc; --text:#0f172a; --muted:#64748b; --line:#e5e7eb; --accent:#10b981;
  --btn-h:44px; --btn-r:12px;
}

/* Header */
.kr-header{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px; }
.kr-title{ font-size:22px; font-weight:700; color:var(--text); }

/* Card */
.kr-card{
  background:var(--bg); color:var(--text);
  border:1px solid var(--line); border-radius:16px; padding:20px;
  box-shadow:0 10px 30px rgba(0,0,0,.06);
}

/* Filter area */
.kr-filter{
  display:grid; grid-template-columns: repeat(12, minmax(0,1fr));
  gap:12px; margin-bottom:12px;
}
.kr-field{ grid-column: span 3 / span 3; }
.kr-field label{ display:block; font-size:13px; color:var(--muted); margin-bottom:6px; }
.kr-field input[type="date"]{
  width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 12px;
  background:#fff; color:var(--text); height:var(--btn-h);
}

/* Actions row (buttons) */
.kr-actions{
  grid-column: 1 / -1;
  display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:4px;
}

/* ===== Buttons (seragam untuk <button>, <a>, dan <summary>) ===== */
.kr-btn{
  display:inline-flex; align-items:center; justify-content:center; gap:8px;
  height:var(--btn-h); padding:0 14px; border-radius:var(--btn-r);
  font-weight:600; border:1px solid transparent; cursor:pointer; line-height:1; user-select:none;
  text-decoration:none; -webkit-appearance:none; appearance:none;
}
.kr-btn:focus-visible{ outline:2px solid color-mix(in srgb, var(--accent) 35%, transparent); outline-offset:2px; }
.kr-btn:hover{ filter:brightness(.98); }

/* Primary */
.kr-btn-primary{ background:var(--accent); border-color:var(--accent); color:#fff;
  box-shadow:0 6px 18px rgba(16,185,129,.18);
}

/* Ghost (outline subtle) */
.kr-btn-ghost{ background:#fff; color:#0f172a; border-color:var(--line); }
.kr-btn-ghost:hover{ background:#f9fafb; }

/* Pastikan <a> bertingkah seperti tombol */
a.kr-btn{ color:inherit; text-decoration:none; }

/* ===== Dropdown “Pilih Laporan” pakai <details> ===== */
.kr-dd{ grid-column: span 3 / span 3; align-self:end; position:relative; }
.kr-dd summary{ list-style:none; }
.kr-dd summary::-webkit-details-marker{ display:none; }

/* jadikan summary terlihat seperti tombol */
.kr-dd summary.kr-btn{ display:inline-flex; height:var(--btn-h); }

/* menu */
.kr-dd-menu{
  position:absolute; z-index:30; top:calc(100% + 8px); left:0;
  min-width:260px; padding:10px; background:#fff;
  border:1px solid var(--line); border-radius:12px;
  box-shadow:0 12px 30px rgba(2,6,23,.12);
}
.kr-dd:not([open]) .kr-dd-menu{ display:none; }
.kr-dd[open] summary.kr-btn{ box-shadow:0 0 0 3px rgba(16,185,129,.15); }
.kr-dd-menu label{ display:flex; gap:8px; padding:6px 4px; font-size:13px; }
.kr-dd-menu hr{ border:none; height:1px; background:var(--line); margin:8px 0; }

/* Chips */
.kr-chipbar{ display:flex; gap:8px; flex-wrap:wrap; }
.kr-chip{ background:var(--soft); color:#0f172a; padding:6px 10px; border-radius:999px; font-size:12px; border:1px solid var(--line); }

/* Table & helpers (biar konsisten dengan file CSS utama) */
.kr-sep{ height:1px; background:var(--line); margin:10px 0 16px; }
.kr-section{ margin-top:12px; }
.kr-section-title{ font-weight:700; font-size:14px; color:#0f172a; margin-bottom:8px; }

.kr-table{ width:100%; border-collapse:collapse; font-size:14px; border:1px solid var(--line); background:#fff; }
.kr-table th, .kr-table td{ padding:10px 12px; border:1px solid var(--line); }
.kr-table th{ text-align:left; color:var(--muted); font-weight:600; background:var(--soft); }
.kr-table tbody tr:hover{ background:#f9fbfd; }

.kr-right{ text-align:right }
.kr-money{ font-variant-numeric: tabular-nums; letter-spacing:.3px; }

.kr-empty{ text-align:center; color:var(--muted); padding:24px 8px; background:var(--soft); border-radius:12px; }

/* Responsive */
@media (max-width: 920px){
  .kr-field{ grid-column: span 6 / span 6; }
  .kr-dd{ grid-column: span 6 / span 6; }
}
@media (max-width: 560px){
  .kr-actions .kr-btn{ flex:1; min-width:140px; }
}
</style>
@endpush

@section('content')
@php
  // default: semua seksi aktif
  $secDefault = ['items','payments','unified','journal','ledger'];
  $secSel = request('sec', $secDefault);
@endphp

<div class="kr-page">
  <div class="kr-card">

    {{-- Header --}}
    <div class="kr-header">
      <div class="kr-title">Rekap Kasir</div>
      <div class="kr-chipbar">
        <div class="kr-chip">Periode:
          {{ \Carbon\Carbon::parse($meta['start'])->format('d/m/Y') }}
          – {{ \Carbon\Carbon::parse($meta['end'])->format('d/m/Y') }}
        </div>
      </div>
    </div>

    {{-- Filter --}}
    <form class="kr-filter" method="GET" action="{{ route('kasir.rekap') }}">
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

      {{-- Dropdown pilih seksi laporan --}}
      <details class="kr-dd">
        <summary class="kr-btn kr-btn-ghost">
          Pilih Laporan
          {{-- caret kecil --}}
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
               stroke-linecap="round" stroke-linejoin="round" style="margin-left:4px;">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </summary>
        <div class="kr-dd-menu">
          <label><input class="sec-check" type="checkbox" name="sec[]" value="items"    {{ in_array('items',$secSel)?'checked':'' }}> Rekap Per Produk</label>
          <label><input class="sec-check" type="checkbox" name="sec[]" value="payments" {{ in_array('payments',$secSel)?'checked':'' }}> Rekap Per Metode Pencatatan</label>
          <label><input class="sec-check" type="checkbox" name="sec[]" value="unified"  {{ in_array('unified',$secSel)?'checked':'' }}> Rekapitulasi Tunai vs Non-Tunai</label>
          <label><input class="sec-check" type="checkbox" name="sec[]" value="journal"  {{ in_array('journal',$secSel)?'checked':'' }}> Jurnal Umum</label>
          <label><input class="sec-check" type="checkbox" name="sec[]" value="ledger"   {{ in_array('ledger',$secSel)?'checked':'' }}> Buku Besar</label>
          <hr>
          <label><input id="sec-all" type="checkbox" onclick="document.querySelectorAll('.sec-check').forEach(cb=>cb.checked=this.checked)"> Pilih semua</label>
        </div>
      </details>

      {{-- Buttons --}}
      <div class="kr-actions">
        <button class="kr-btn kr-btn-primary" type="submit">Tampilkan</button>

        @php
          $pdfSel = [
            'start_date'=>request('start_date', now()->toDateString()),
            'end_date'  =>request('end_date', now()->toDateString()),
            'sec'       =>$secSel,
          ];
          $pdfAll = [
            'start_date'=>request('start_date', now()->toDateString()),
            'end_date'  =>request('end_date', now()->toDateString()),
          ];
        @endphp

        <a class="kr-btn kr-btn-ghost" target="_blank" href="{{ route('kasir.rekap.pdf', $pdfSel) }}">PDF (sesuai pilihan)</a>
        <a class="kr-btn kr-btn-ghost" target="_blank" href="{{ route('kasir.rekap.excel', $pdfSel) }}">Excel (sesuai pilihan)</a>
        <a class="kr-btn kr-btn-ghost" target="_blank" href="{{ route('kasir.rekap.pdf', $pdfAll) }}">PDF (semua)</a>
      </div>
    </form>

    <div class="kr-sep"></div>

    {{-- Sections --}}
    @if(in_array('items',$secSel))
      @include('reports.partials.kasir_items',['items'=>$items])
    @endif

    @if(in_array('payments',$secSel))
      @include('reports.partials.kasir_payments',['payments'=>$payments])
    @endif

    @if(in_array('unified',$secSel))
      @include('reports.partials.kasir_unified',['payUnified'=>$payUnified])
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
