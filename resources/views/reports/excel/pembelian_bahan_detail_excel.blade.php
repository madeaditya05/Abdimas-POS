<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Pembelian Bahan (Excel)</title>
<style>
  body {
    font-family: sans-serif;
  }
  h2, h3 {
    margin: 10px 0;
  }
  table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 20px;
  }
  th, td {
    border: 1px solid #444444;
    padding: 6px 8px;
    vertical-align: top;
  }
  th {
    background-color: #f1f5f9;
    text-align: left;
    font-weight: bold;
  }
  .kr-right {
    text-align: right;
  }
  .kr-money {
    white-space: nowrap;
  }
</style>
</head>
<body>
  <h2>Laporan Pembelian Bahan</h2>
  <p>Periode: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>

  <h3>Ringkasan Statistik</h3>
  <table>
    <tbody>
      <tr>
        <td>Grand Total Pembelian</td>
        <td class="kr-right" style="font-weight: bold;">Rp {{ number_format($pembelianDetail['stats']['grand_total'], 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Harga Min</td>
        <td class="kr-right">Rp {{ number_format($pembelianDetail['stats']['min'], 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Harga Max</td>
        <td class="kr-right">Rp {{ number_format($pembelianDetail['stats']['max'], 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Harga Rata-Rata</td>
        <td class="kr-right">Rp {{ number_format($pembelianDetail['stats']['avg'], 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>

  <h3>Detail Pembelian</h3>
  <table>
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>Bahan</th>
        <th class="kr-right">Qty</th>
        <th>Satuan</th>
        <th class="kr-right">Avg Harga</th>
        <th class="kr-right">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($pembelianDetail['rows'] as $row)
        <tr>
          <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
          <td>{{ $row->nama_bahan }}</td>
          <td class="kr-right">{{ number_format($row->qty, 2, ',', '.') }}</td>
          <td>{{ $row->satuan_beli }}</td>
          <td class="kr-right">Rp {{ number_format($row->avg_harga, 2, ',', '.') }}</td>
          <td class="kr-right">Rp {{ number_format($row->total, 2, ',', '.') }}</td>
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
