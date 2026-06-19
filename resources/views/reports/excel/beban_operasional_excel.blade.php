<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Beban Operasional (Excel)</title>
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
  <h2>Laporan Beban Operasional</h2>
  <p>Periode: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>No. Transaksi</th>
        <th>No. Ref</th>
        <th>Memo / Deskripsi</th>
        <th class="kr-right">Nominal</th>
      </tr>
    </thead>
    <tbody>
      @php $totalBeban = 0; @endphp
      @forelse ($entries as $index => $e)
        @php
          $nominal = (float)($totals[$e->id] ?? 0);
          $totalBeban += $nominal;
        @endphp
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>
          <td>{{ $e->entry_no }}</td>
          <td>{{ $e->ref_no ?? '-' }}</td>
          <td>{{ $e->memo ?? '-' }}</td>
          <td class="kr-right">Rp {{ number_format($nominal, 2, ',', '.') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align: center;">Belum ada data beban operasional pada periode ini.</td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr style="font-weight: bold; background-color: #f3f4f6;">
        <th colspan="5" class="kr-right">Total Beban</th>
        <th class="kr-right">Rp {{ number_format($totalBeban, 2, ',', '.') }}</th>
      </tr>
    </tfoot>
  </table>
</body>
</html>
