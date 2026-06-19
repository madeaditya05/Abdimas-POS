<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Piutang Invoice (Excel)</title>
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
  .status-lunas { background-color: #dcfce7; color: #166534; }
  .status-overdue { background-color: #fee2e2; color: #991b1b; }
  .status-belumlunas { background-color: #fef9c3; color: #854d0e; }
</style>
</head>
<body>
  <h2>Laporan Piutang Invoice</h2>
  <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>

  <h3>Ringkasan</h3>
  <table>
    <tbody>
      <tr>
        <td>Total Invoice</td>
        <td class="kr-right">{{ number_format($summary['count'] ?? 0, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Belum Lunas</td>
        <td class="kr-right">{{ number_format($summary['unpaid'] ?? 0, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Lunas</td>
        <td class="kr-right">{{ number_format($summary['paid'] ?? 0, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Overdue</td>
        <td class="kr-right" style="color: #dc2626; font-weight: bold;">{{ number_format($summary['overdue'] ?? 0, 0, ',', '.') }}</td>
      </tr>
      <tr style="font-weight: bold; background-color: #f3f4f6;">
        <td>Total Tagihan</td>
        <td class="kr-right">Rp {{ number_format($summary['total'] ?? 0, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>

  <h3>Daftar Invoice</h3>
  <table>
    <thead>
      <tr>
        <th>No. Invoice</th>
        <th>Nama Toko / Nama Perusahaan</th>
        <th>Tanggal Invoice</th>
        <th>Jatuh Tempo</th>
        <th>Status</th>
        <th class="kr-right">Total Tagihan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($invoices as $inv)
        @php
          $isOverdue = $inv->status === \App\Models\Invoice::STATUS_UNPAID && today()->gt($inv->tanggal_jatuh_tempo);
        @endphp
        <tr>
          <td>{{ $inv->nomor_invoice }}</td>
          <td>{{ $inv->nama_toko }}</td>
          <td>{{ \Carbon\Carbon::parse($inv->tanggal_invoice)->format('d/m/Y') }}</td>
          <td>{{ \Carbon\Carbon::parse($inv->tanggal_jatuh_tempo)->format('d/m/Y') }}</td>
          <td class="{{ $inv->status === \App\Models\Invoice::STATUS_PAID ? 'status-lunas' : ($isOverdue ? 'status-overdue' : 'status-belumlunas') }}">
            @if ($inv->status === \App\Models\Invoice::STATUS_PAID)
              Lunas
            @elseif ($isOverdue)
              Overdue
            @else
              Belum Lunas
            @endif
          </td>
          <td class="kr-right">Rp {{ number_format($inv->total_tagihan, 2, ',', '.') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align: center;">Belum ada data invoice tempo.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
