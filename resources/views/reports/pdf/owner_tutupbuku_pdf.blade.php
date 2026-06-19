<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Closing Tutup Buku</title>
@php
  $fontRegular = str_replace('\\','/', public_path('fonts/DejaVuSans.ttf'));
  $fontBold    = str_replace('\\','/', public_path('fonts/DejaVuSans-Bold.ttf'));
@endphp
<style>
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

  @page { margin: 24px 28px; }
  body{ font-family:'DejaVuSans'; font-size:12px; color:#0f172a; line-height:1.35; }

  .title{ font-size:18px; font-weight:700; margin:0 0 2px 0; }
  .subtitle{ font-size:12px; color:#64748b; margin:0 0 10px 0; }
  .right{ text-align:right; }
  .money{ font-variant-numeric: tabular-nums; letter-spacing:.2px; }

  table.tbl { width:100%; border-collapse:collapse; background:#fff; margin-bottom: 20px; }
  table.tbl, .tbl th, .tbl td { border:1px solid #444; }
  .tbl th, .tbl td { padding:6px 8px; vertical-align:top; }
  .tbl th { background:#f1f5f9; text-align:left; font-weight:700; color:#334155; }
  .tbl tfoot th, .tbl tfoot td { background:#eef2f7; font-weight:700; }
</style>
</head>
<body>
  <div class="title">Tutup Buku (Periodik)</div>
  <div class="subtitle">
    Periode: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
  </div>

  <h3>Ringkasan Closing</h3>
  <table class="tbl">
    <tbody>
      <tr>
        <td>Persediaan Awal (BI)</td>
        <td class="right">Rp {{ number_format((float)$preview['begin_inv'],0,',','.') }}</td>
      </tr>
      <tr>
        <td>Pembelian (5100)</td>
        <td class="right">Rp {{ number_format((float)$preview['purchases'],0,',','.') }}</td>
      </tr>
      <tr>
        <td>Persediaan Akhir (EI)</td>
        <td class="right">Rp {{ number_format((float)$preview['end_inv'],0,',','.') }}</td>
      </tr>
      <tr style="font-weight: bold; background: #f3f4f6;">
        <td><b>HPP (BI + Purchases - EI)</b></td>
        <td class="right"><b>Rp {{ number_format((float)$preview['cogs'],0,',','.') }}</b></td>
      </tr>
    </tbody>
  </table>

  <h3>Detail Persediaan Akhir (Average Cost)</h3>
  <table class="tbl">
    <thead>
      <tr>
        <th>Bahan ID</th>
        <th class="right">Qty On Hand</th>
        <th class="right">Avg Cost</th>
        <th class="right">Value</th>
      </tr>
    </thead>
    <tbody>
      @forelse(($preview['detail'] ?? []) as $d)
        <tr>
          <td>{{ $d['bahan_baku_id'] }}</td>
          <td class="right">{{ number_format((float)$d['qty_on_hand'],4,',','.') }}</td>
          <td class="right">Rp {{ number_format((float)$d['avg_cost'],2,',','.') }}</td>
          <td class="right">Rp {{ number_format((float)$d['value'],2,',','.') }}</td>
        </tr>
      @empty
        <tr><td colspan="4">Belum ada data persediaan akhir yang bisa dihitung.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
