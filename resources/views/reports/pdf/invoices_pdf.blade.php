<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Piutang Invoice</title>
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
  
  .badge {
    display: inline-block;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
  }
  .badge--success { background: #dcfce7; color: #166534; }
  .badge--warning { background: #fef9c3; color: #854d0e; }
  .badge--danger { background: #fee2e2; color: #991b1b; }
</style>
</head>
<body>
  <div class="title">Laporan Piutang Invoice</div>
  <div class="subtitle">
    Dicetak pada: {{ now()->format('d/m/Y H:i') }}
  </div>

  <h3>Ringkasan</h3>
  <table class="tbl" style="width: 50%;">
    <tbody>
      <tr>
        <td>Total Invoice</td>
        <td class="right">{{ number_format($summary['count'] ?? 0, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Belum Lunas</td>
        <td class="right">{{ number_format($summary['unpaid'] ?? 0, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Lunas</td>
        <td class="right">{{ number_format($summary['paid'] ?? 0, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Overdue</td>
        <td class="right" style="color: #dc2626; font-weight: bold;">{{ number_format($summary['overdue'] ?? 0, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Total Tagihan</td>
        <td class="right" style="font-weight: bold;">Rp {{ number_format($summary['total'] ?? 0, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>

  <h3>Daftar Invoice</h3>
  <table class="tbl">
    <thead>
      <tr>
        <th>No. Invoice</th>
        <th>Nama Toko / Nama Perusahaan</th>
        <th>Tanggal Invoice</th>
        <th>Jatuh Tempo</th>
        <th>Status</th>
        <th class="right">Total Tagihan</th>
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
          <td>
            @if ($inv->status === \App\Models\Invoice::STATUS_PAID)
              <span class="badge badge--success">Lunas</span>
            @elseif ($isOverdue)
              <span class="badge badge--danger">Overdue</span>
            @else
              <span class="badge badge--warning">Belum Lunas</span>
            @endif
          </td>
          <td class="right">Rp {{ number_format($inv->total_tagihan, 2, ',', '.') }}</td>
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
