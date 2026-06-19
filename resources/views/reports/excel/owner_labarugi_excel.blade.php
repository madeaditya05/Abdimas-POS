<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Owner (Excel)</title>
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
  .indent {
    padding-left: 20px;
  }
  .muted {
    color: #64748b;
  }
  .total-row {
    background-color: #f3f4f6;
    font-weight: bold;
  }
  .head-row th {
    font-size: 11pt;
    background-color: #e2e8f0;
  }
</style>
</head>
<body>
  <h2>Laporan Owner</h2>
  <p>Periode: {{ \Carbon\Carbon::parse($meta['start'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($meta['end'])->format('d/m/Y') }}</p>

  @php
    $rev   = (float)($lr['revenue'] ?? 0);
    $cogs  = (float)($lr['cogs'] ?? 0);
    $gross = (float)($lr['gross'] ?? ($rev - $cogs));
    $exp   = (float)($lr['expense'] ?? 0);
    $net   = (float)($lr['net_income'] ?? ($gross - $exp));
    $fmt   = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
  @endphp

  @if($sections['labarugi'])
    <h3>Ringkasan Laba Rugi</h3>
    <table>
      <tr class="head-row"><th colspan="2">Pendapatan</th></tr>
      <tr>
        <td class="indent muted">Pendapatan Penjualan</td>
        <td class="kr-right">{{ $fmt($rev) }}</td>
      </tr>
      <tr class="total-row">
        <td>Total Pendapatan</td>
        <td class="kr-right">{{ $fmt($rev) }}</td>
      </tr>

      <tr class="head-row"><th colspan="2">Harga Pokok Penjualan</th></tr>
      <tr>
        <td class="indent muted">Harga Pokok Penjualan (HPP)</td>
        <td class="kr-right">{{ $fmt($cogs) }}</td>
      </tr>
      <tr class="total-row">
        <td>Total HPP</td>
        <td class="kr-right">{{ $fmt($cogs) }}</td>
      </tr>

      <tr class="total-row">
        <td>Laba Kotor</td>
        <td class="kr-right">{{ $fmt($gross) }}</td>
      </tr>

      <tr class="head-row"><th colspan="2">Beban Operasional</th></tr>
      <tr>
        <td class="indent muted">Beban Operasional</td>
        <td class="kr-right">{{ $fmt($exp) }}</td>
      </tr>
      <tr class="total-row">
        <td>Total Beban Operasional</td>
        <td class="kr-right">{{ $fmt($exp) }}</td>
      </tr>

      <tr class="total-row" style="background-color: #bfdbfe;">
        <td>Laba Bersih</td>
        <td class="kr-right">{{ $fmt($net) }}</td>
      </tr>
    </table>
  @endif

  @if($sections['items'])
    @include('reports.partials.kasir_items', ['items' => $items])
  @endif

  @if($sections['payments'])
    @include('reports.partials.kasir_payments', ['payments' => $payments])
  @endif

  @if($sections['unified'])
    @include('reports.partials.owner_sales', ['sales' => $sales])
  @endif

  @if($sections['journal'])
    @include('reports.partials.kasir_jurnal', ['journal' => $journal])
  @endif

  @if($sections['ledger'])
    @include('reports.partials.kasir_ledger', ['ledger' => $ledger])
  @endif

</body>
</html>
