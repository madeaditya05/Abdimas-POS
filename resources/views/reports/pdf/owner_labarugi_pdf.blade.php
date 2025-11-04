<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Laba Rugi</title>
@php
  // pastikan file ini ada: public/fonts/DejaVuSans.ttf & DejaVuSans-Bold.ttf
  $fontRegular = str_replace('\\','/', public_path('fonts/DejaVuSans.ttf'));
  $fontBold    = str_replace('\\','/', public_path('fonts/DejaVuSans-Bold.ttf'));
@endphp
<style>
  /* ====== Font DejaVuSans dari public/fonts ====== */
  @font-face{
    font-family:'DejaVuSans';
    src: url('{{ $fontRegular }}') format('truetype');
    font-weight: 400; font-style: normal;
  }
  @font-face{
    font-family:'DejaVuSans';
    src: url('{{ $fontBold }}') format('truetype');
    font-weight: 700; font-style: normal;
  }

  /* ====== PDF-safe styles ====== */
  @page { margin: 24px 28px; }
  body{ font-family:'DejaVuSans'; font-size:12px; color:#0f172a; }
  .title{ font-size:22px; font-weight:700; margin:0 0 2px 0; }
  .title .accent{ color:#1d4ed8; } /* biru aksen */
  .subtitle{ font-size:12px; color:#64748b; margin:0 0 14px 0; }

  .right{ text-align:right; }
  .money{ font-variant-numeric: tabular-nums; letter-spacing:.2px; }
  .muted{ color:#64748b; }

  /* tabel gaya laporan (garis antar kelompok, bukan kotak penuh) */
  table.pl { width:100%; border-collapse:collapse; }
  .pl th, .pl td { padding:8px 10px; }
  .pl .head-row th{ font-size:13px; color:#0f172a; padding-top:14px; padding-bottom:6px; }
  .pl .sep{ height:1px; background:#e5e7eb; }
  .pl .total-row{ background:#f3f4f6; font-weight:700; }
  .pl .emph{ font-weight:700; }
  .pl .indent{ padding-left:22px; }
</style>
</head>
<body>

  {{-- Header --}}
  <div class="title">Laporan <span class="accent">Laba &amp; Rugi</span></div>
  @php $company = "COFIT-EV"; @endphp
  <div class="subtitle">
    {{ $company }} •
    {{ \Carbon\Carbon::parse($meta['start'])->format('d/m/Y') }}
    – {{ \Carbon\Carbon::parse($meta['end'])->format('d/m/Y') }}
  </div>

  @php
    $rev   = (float)($lr['revenue'] ?? 0);
    $cogs  = (float)($lr['cogs'] ?? 0);
    $gross = (float)($lr['gross'] ?? ($rev - $cogs));
    $exp   = (float)($lr['expense'] ?? 0);
    $net   = (float)($lr['net_income'] ?? ($gross - $exp));
    $fmt = fn($n) => 'Rp '.number_format($n,0,',','.');
  @endphp

  <table class="pl">
    {{-- PENDAPATAN --}}
    <tr class="head-row"><th colspan="2">Pendapatan</th></tr>
    <tr><td class="indent muted">Pendapatan Penjualan</td><td class="right money">{{ $fmt($rev) }}</td></tr>
    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row"><td>Total Pendapatan</td><td class="right money">{{ $fmt($rev) }}</td></tr>

    {{-- HPP --}}
    <tr class="head-row"><th colspan="2">Harga Pokok Penjualan</th></tr>
    <tr><td class="indent muted">HPP</td><td class="right money">{{ $fmt($cogs) }}</td></tr>
    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row"><td>Total Harga Pokok Penjualan</td><td class="right money">{{ $fmt($cogs) }}</td></tr>

    {{-- LABA KOTOR --}}
    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row"><td class="emph">TOTAL LABA KOTOR</td><td class="right money emph">{{ $fmt($gross) }}</td></tr>

    {{-- BEBAN OPERASIONAL --}}
    <tr class="head-row"><th colspan="2">Beban Operasional</th></tr>
    <tr><td class="indent muted">Beban Operasional</td><td class="right money">{{ $fmt($exp) }}</td></tr>
    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row"><td>Total Beban Operasional</td><td class="right money">{{ $fmt($exp) }}</td></tr>

    {{-- LABA BERSIH --}}
    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row">
      <td class="emph">LABA/(RUGI) BERSIH</td>
      <td class="right money emph">{{ $fmt($net) }}</td>
    </tr>
  </table>
</body>
</html>
