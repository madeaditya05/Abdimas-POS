<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Rekap Kasir (Excel)</title>
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
  <h2>Rekap Kasir</h2>
  <p>Periode: {{ \Carbon\Carbon::parse($meta['start'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($meta['end'])->format('d/m/Y') }}</p>

  @if($sections['items'])
    @include('reports.partials.kasir_items', ['items' => $items])
  @endif

  @if($sections['payments'])
    @include('reports.partials.kasir_payments', ['payments' => $payments])
  @endif

  @if($sections['journal'])
    @include('reports.partials.kasir_jurnal', ['journal' => $journal])
  @endif

  @if($sections['ledger'])
    @include('reports.partials.kasir_ledger', ['ledger' => $ledger])
  @endif

</body>
</html>
