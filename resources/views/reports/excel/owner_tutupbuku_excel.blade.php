<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Closing Tutup Buku (Excel)</title>
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
  <h2>Tutup Buku (Periodik)</h2>
  <p>Periode: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>

  <h3>Ringkasan Closing</h3>
  <table>
    <tbody>
      <tr>
        <td>Persediaan Awal (BI)</td>
        <td class="kr-right">Rp {{ number_format((float)$preview['begin_inv'],0,',','.') }}</td>
      </tr>
      <tr>
        <td>Pembelian (5100)</td>
        <td class="kr-right">Rp {{ number_format((float)$preview['purchases'],0,',','.') }}</td>
      </tr>
      <tr>
        <td>Persediaan Akhir (EI)</td>
        <td class="kr-right">Rp {{ number_format((float)$preview['end_inv'],0,',','.') }}</td>
      </tr>
      <tr style="font-weight: bold; background-color: #f3f4f6;">
        <td>HPP (BI + Purchases - EI)</td>
        <td class="kr-right">Rp {{ number_format((float)$preview['cogs'],0,',','.') }}</td>
      </tr>
    </tbody>
  </table>

  <h3>Detail Persediaan Akhir (Average Cost)</h3>
  <table>
    <thead>
      <tr>
        <th>Bahan ID</th>
        <th class="kr-right">Qty On Hand</th>
        <th class="kr-right">Avg Cost</th>
        <th class="kr-right">Value</th>
      </tr>
    </thead>
    <tbody>
      @forelse(($preview['detail'] ?? []) as $d)
        <tr>
          <td>{{ $d['bahan_baku_id'] }}</td>
          <td class="kr-right">{{ number_format((float)$d['qty_on_hand'],4,',','.') }}</td>
          <td class="kr-right">Rp {{ number_format((float)$d['avg_cost'],2,',','.') }}</td>
          <td class="kr-right">Rp {{ number_format((float)$d['value'],2,',','.') }}</td>
        </tr>
      @empty
        <tr><td colspan="4">Belum ada data persediaan akhir yang bisa dihitung.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
