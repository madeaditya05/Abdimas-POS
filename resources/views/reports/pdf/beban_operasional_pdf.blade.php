<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Beban Operasional</title>
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
  <div class="title">Laporan Beban Operasional</div>
  <div class="subtitle">
    Periode: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
  </div>

  <table class="tbl">
    <thead>
      <tr>
        <th style="width: 40px;">No</th>
        <th>Tanggal</th>
        <th>No. Transaksi</th>
        <th>No. Ref</th>
        <th>Memo / Deskripsi</th>
        <th class="right">Nominal</th>
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
          <td class="right">Rp {{ number_format($nominal, 2, ',', '.') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align: center;">Belum ada data beban operasional pada periode ini.</td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <th colspan="5" class="right">Total Beban</th>
        <th class="right">Rp {{ number_format($totalBeban, 2, ',', '.') }}</th>
      </tr>
    </tfoot>
  </table>
</body>
</html>
