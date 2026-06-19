<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Pembelian Bahan</title>
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

  table.tbl { width:100%; border-collapse:collapse; background:#fff; margin-bottom: 20px; }
  table.tbl, .tbl th, .tbl td { border:1px solid #444; }
  .tbl th, .tbl td { padding:6px 8px; vertical-align:top; }
  .tbl th { background:#f1f5f9; text-align:left; font-weight:700; color:#334155; }
  .tbl tfoot th, .tbl tfoot td { background:#eef2f7; font-weight:700; }
</style>
</head>
<body>
  <div class="title">Laporan Pembelian Bahan</div>
  <div class="subtitle">
    Periode: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
  </div>

  <h3>Ringkasan Statistik</h3>
  <table class="tbl" style="width: 50%;">
    <tbody>
      <tr>
        <td>Grand Total Pembelian</td>
        <td class="right" style="font-weight: bold;">Rp {{ number_format($pembelianDetail['stats']['grand_total'], 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Harga Min</td>
        <td class="right">Rp {{ number_format($pembelianDetail['stats']['min'], 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Harga Max</td>
        <td class="right">Rp {{ number_format($pembelianDetail['stats']['max'], 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Harga Rata-Rata</td>
        <td class="right">Rp {{ number_format($pembelianDetail['stats']['avg'], 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>

  <h3>Detail Pembelian</h3>
  <table class="tbl">
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>Bahan</th>
        <th class="right">Qty</th>
        <th>Satuan</th>
        <th class="right">Avg Harga</th>
        <th class="right">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($pembelianDetail['rows'] as $row)
        <tr>
          <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
          <td>{{ $row->nama_bahan }}</td>
          <td class="right">{{ number_format($row->qty, 2, ',', '.') }}</td>
          <td>{{ $row->satuan_beli }}</td>
          <td class="right">Rp {{ number_format($row->avg_harga, 2, ',', '.') }}</td>
          <td class="right">Rp {{ number_format($row->total, 2, ',', '.') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align: center;">Belum ada data pembelian bahan pada periode ini.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
