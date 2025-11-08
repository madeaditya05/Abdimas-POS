<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Rekap Kasir (PDF)</title>
<style>
/* PDF-safe grid untuk kr-table */
.kr-table { width:100%; border-collapse:collapse; }
.kr-table, .kr-table th, .kr-table td { border:1px solid #444; }
.kr-table th, .kr-table td { padding:6px 8px; }
.kr-table th { background:#f1f5f9; text-align:left; font-weight:700; color:#334155; }

/* jaga header/footer tabel saat page break */
thead { display: table-header-group; }
tfoot { display: table-row-group; }
tr { page-break-inside: avoid; break-inside: avoid; }
.right { text-align:right; }
.money { font-variant-numeric: tabular-nums; letter-spacing:.2px; }
</style>
</head>
<body>


<div class="kr-section">
  <div class="kr-section-title">Jurnal Umum</div>
  @if($journal->isEmpty())
    <div class="kr-empty">Tidak ada jurnal pada periode ini.</div>
  @else
    @php
      $tDebit  = $journal->sum(fn($r) => (float)$r->debit);
      $tCredit = $journal->sum(fn($r) => (float)$r->credit);
    @endphp
    <table class="kr-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>No. Jurnal</th>
          <th>Ref</th>
          <th>Akun</th>
          <th class="kr-right">Debit</th>
          <th class="kr-right">Credit</th>
        </tr>
      </thead>
      <tbody>
        @foreach($journal as $j)
          <tr>
            <td>{{ \Carbon\Carbon::parse($j->date)->format('d/m/Y') }}</td>
            <td>{{ $j->entry_no }}</td>
            <td>{{ $j->ref_no }}</td>
            <td>{{ $j->code }} — {{ $j->name }}</td>
            <td class="kr-right kr-money">Rp {{ number_format($j->debit,0,',','.') }}</td>
            <td class="kr-right kr-money">Rp {{ number_format($j->credit,0,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4">Total</th>
          <th class="kr-right kr-money">Rp {{ number_format($tDebit,0,',','.') }}</th>
          <th class="kr-right kr-money">Rp {{ number_format($tCredit,0,',','.') }}</th>
        </tr>
      </tfoot>
    </table>
  @endif
</div>
</body>
</html>
