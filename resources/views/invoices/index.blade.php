@extends('layouts.main')
@section('title', 'Piutang Invoice')

@push('styles')
<style>
  .inv-page {
    --inv-text: #0f172a;
    --inv-muted: #64748b;
    --inv-line: #e2e8f0;
    --inv-soft: #f8fafc;
    --inv-green: #059669;
    --inv-yellow: #f59e0b;
    --inv-red: #dc2626;
    color: var(--inv-text);
    max-width: 100%;
    overflow-x: hidden;
  }

  .main-content,
  .dashboard-main {
    min-width: 0;
  }

  .inv-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
  }

  .inv-title {
    font-size: clamp(22px, 2vw, 24px);
    font-weight: 700;
    line-height: 1.2;
  }

  .inv-subtitle {
    color: var(--inv-muted);
    font-size: 13px;
    margin-top: 4px;
  }

  .inv-alert {
    padding: 10px 12px;
    border: 1px solid #bbf7d0;
    background: #f0fdf4;
    color: #166534;
    border-radius: 8px;
    margin-bottom: 12px;
    font-size: 14px;
  }

  .inv-filter {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 10px;
    align-items: end;
    padding: 14px;
    border: 1px solid var(--inv-line);
    border-radius: 8px;
    background: #fff;
    margin-bottom: 14px;
  }

  .inv-field {
    grid-column: span 3 / span 3;
  }

  .inv-field label {
    display: block;
    font-size: 12px;
    color: var(--inv-muted);
    margin-bottom: 6px;
  }

  .inv-input {
    width: 100%;
    height: 40px;
    border: 1px solid var(--inv-line);
    border-radius: 8px;
    padding: 8px 10px;
    background: #fff;
    color: var(--inv-text);
  }

  .inv-actions-filter {
    grid-column: span 3 / span 3;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .inv-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    padding: 8px 11px;
    border-radius: 8px;
    border: 1px solid var(--inv-line);
    background: #fff;
    color: var(--inv-text);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    max-width: 100%;
  }

  .inv-btn:hover {
    background: var(--inv-soft);
  }

  .inv-btn--primary {
    background: var(--inv-green);
    border-color: var(--inv-green);
    color: #fff;
  }

  .inv-btn--paid {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #047857;
  }

  .inv-btn--send {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
  }

  .inv-btn--reminder {
    background: #fff7ed;
    border-color: #fed7aa;
    color: #c2410c;
  }

  .inv-btn--disabled {
    opacity: .55;
    cursor: not-allowed;
    pointer-events: none;
  }

  .inv-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin-bottom: 14px;
  }

  .inv-stat {
    border: 1px solid var(--inv-line);
    border-radius: 8px;
    background: #fff;
    padding: 12px;
  }

  .inv-stat__label {
    font-size: 12px;
    color: var(--inv-muted);
  }

  .inv-stat__value {
    margin-top: 4px;
    font-size: 20px;
    font-weight: 700;
  }

  .inv-table-wrap {
    overflow: hidden;
    border: 1px solid var(--inv-line);
    border-radius: 8px;
    background: #fff;
  }

  .inv-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
  }

  .inv-table th:nth-child(1) { width: 10%; }
  .inv-table th:nth-child(2) { width: 13%; }
  .inv-table th:nth-child(3) { width: 10%; }
  .inv-table th:nth-child(4) { width: 16%; }
  .inv-table th:nth-child(5) { width: 9%; }
  .inv-table th:nth-child(6) { width: 10%; }
  .inv-table th:nth-child(7) { width: 15%; }
  .inv-table th:nth-child(8) { width: 17%; }

  .inv-table th,
  .inv-table td {
    padding: 10px 11px;
    border-bottom: 1px solid #eef2f7;
    text-align: left;
    vertical-align: middle;
    font-size: 13px;
    overflow-wrap: anywhere;
    word-break: normal;
  }

  .inv-table th {
    background: var(--inv-soft);
    color: #475569;
    font-weight: 700;
    position: sticky;
    top: 0;
    z-index: 1;
  }

  .inv-table tbody tr:last-child td {
    border-bottom: 0;
  }

  .inv-row--waiting {
    background: #fffbeb;
    box-shadow: inset 4px 0 0 var(--inv-yellow);
  }

  .inv-row--overdue {
    background: #fff1f2;
    box-shadow: inset 4px 0 0 var(--inv-red);
  }

  .inv-row--paid {
    background: #f0fdf4;
    box-shadow: inset 4px 0 0 var(--inv-green);
  }

  .inv-money,
  .inv-days {
    font-variant-numeric: tabular-nums;
  }

  .inv-money {
    text-align: right;
  }

  .inv-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 26px;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    max-width: 100%;
    text-align: center;
    white-space: normal;
    overflow-wrap: anywhere;
  }

  .inv-status--waiting {
    background: #fef3c7;
    color: #92400e;
  }

  .inv-status--overdue {
    background: #dc2626;
    color: #fff;
  }

  .inv-status--paid {
    background: #dcfce7;
    color: #166534;
  }

  .inv-row-actions {
    display: flex;
    gap: 7px;
    align-items: center;
    flex-wrap: wrap;
  }

  .inv-row-actions form {
    min-width: 0;
  }

  .inv-empty {
    text-align: center;
    color: var(--inv-muted);
    padding: 28px 12px;
  }

  .inv-pagination {
    margin-top: 12px;
  }

  @media (max-width: 1100px) {
    .inv-field,
    .inv-actions-filter {
      grid-column: span 6 / span 6;
    }
  }

  @media (max-width: 1180px) {
    .inv-table,
    .inv-table thead,
    .inv-table tbody,
    .inv-table tr,
    .inv-table th,
    .inv-table td {
      display: block;
    }

    .inv-table thead {
      position: absolute;
      width: 1px;
      height: 1px;
      overflow: hidden;
      clip: rect(0 0 0 0);
      clip-path: inset(50%);
      white-space: nowrap;
    }

    .inv-table {
      table-layout: auto;
    }

    .inv-table tbody {
      display: grid;
      gap: 10px;
      padding: 10px;
    }

    .inv-table tbody tr {
      border: 1px solid var(--inv-line);
      border-radius: 8px;
      overflow: hidden;
      box-shadow: none;
    }

    .inv-table tbody tr:last-child td {
      border-bottom: 1px solid #eef2f7;
    }

    .inv-table td {
      display: grid;
      grid-template-columns: minmax(120px, 34%) 1fr;
      gap: 10px;
      align-items: center;
      padding: 10px 12px;
      border-bottom: 1px solid #eef2f7;
    }

    .inv-table td::before {
      content: attr(data-label);
      color: var(--inv-muted);
      font-size: 12px;
      font-weight: 700;
    }

    .inv-table td:last-child {
      border-bottom: 0;
    }

    .inv-table td.inv-empty {
      display: block;
      text-align: center;
    }

    .inv-table td.inv-empty::before {
      display: none;
    }

    .inv-table .inv-money {
      text-align: left;
    }

    .inv-row-actions {
      justify-content: flex-start;
    }

    .inv-row-actions .inv-btn {
      min-width: 112px;
    }
  }

  @media (max-width: 640px) {
    .inv-header {
      flex-direction: column;
    }

    .inv-field,
    .inv-actions-filter {
      grid-column: span 12 / span 12;
    }

    .inv-actions-filter .inv-btn {
      flex: 1;
    }

    .inv-table-wrap {
      border: 0;
      background: transparent;
    }

    .inv-table tbody {
      padding: 0;
    }

    .inv-table td {
      grid-template-columns: 1fr;
      gap: 4px;
    }

    .inv-row-actions .inv-btn,
    .inv-row-actions form,
    .inv-row-actions form .inv-btn {
      width: 100%;
    }
  }
</style>
@endpush

@section('content')
@php
  $rupiah = function ($n) {
    return 'Rp ' . number_format((float) ($n ?? 0), 0, ',', '.');
  };
@endphp

<div class="inv-page">
  <div class="inv-header">
    <div>
      <div class="inv-title">Piutang Invoice</div>
      <div class="inv-subtitle">Daftar invoice tempo untuk memantau piutang dan histori pelunasan.</div>
    </div>

    <a class="inv-btn" href="{{ route('owner.reports.menu') }}">Ke Keuangan</a>
  </div>

  @if(session('success'))
    <div class="inv-alert">{{ session('success') }}</div>
  @endif

  <form class="inv-filter" method="GET" action="{{ route('invoices.index') }}">
    <div class="inv-field">
      <label for="nama_toko">Nama Toko</label>
      <input id="nama_toko" class="inv-input" type="text" name="nama_toko" value="{{ request('nama_toko') }}" placeholder="Cari nama toko">
    </div>

    <div class="inv-field">
      <label for="due_from">Jatuh Tempo Dari</label>
      <input id="due_from" class="inv-input" type="date" name="due_from" value="{{ request('due_from') }}">
    </div>

    <div class="inv-field">
      <label for="due_to">Jatuh Tempo Sampai</label>
      <input id="due_to" class="inv-input" type="date" name="due_to" value="{{ request('due_to') }}">
    </div>

    <div class="inv-field">
      <label for="status">Status</label>
      <select id="status" class="inv-input" name="status">
        <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Semua</option>
        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas</option>
      </select>
    </div>

    <div class="inv-actions-filter">
      <button class="inv-btn inv-btn--primary" type="submit">Filter</button>
      <a class="inv-btn" href="{{ route('invoices.index') }}">Reset</a>
      <a class="inv-btn" target="_blank" href="{{ route('invoices.pdf', request()->all()) }}">PDF</a>
      <a class="inv-btn" target="_blank" href="{{ route('invoices.excel', request()->all()) }}">Excel</a>
    </div>
  </form>

  <div class="inv-summary">
    <div class="inv-stat">
      <div class="inv-stat__label">Total Invoice</div>
      <div class="inv-stat__value">{{ number_format($summary['count'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="inv-stat">
      <div class="inv-stat__label">Belum Lunas</div>
      <div class="inv-stat__value">{{ number_format($summary['unpaid'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="inv-stat">
      <div class="inv-stat__label">Lunas</div>
      <div class="inv-stat__value">{{ number_format($summary['paid'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="inv-stat">
      <div class="inv-stat__label">Overdue</div>
      <div class="inv-stat__value">{{ number_format($summary['overdue'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="inv-stat">
      <div class="inv-stat__label">Total Tagihan</div>
      <div class="inv-stat__value">{{ $rupiah($summary['total'] ?? 0) }}</div>
    </div>
  </div>

  <div class="inv-table-wrap">
    <table class="inv-table">
      <thead>
        <tr>
          <th>Nama Toko</th>
          <th>No. Invoice</th>
          <th>Tgl Invoice</th>
          <th>Tgl Jatuh Tempo (Manual)</th>
          <th>Sisa Hari</th>
          <th class="inv-money">Total</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $invoice)
          @php
            $sisaHari = (int) $invoice->sisa_hari;
            $isOverdue = (bool) $invoice->is_overdue;
            $isPaid = $invoice->status === \App\Models\Invoice::STATUS_PAID;
            $invoiceUrl = $invoice->penjualan
              ? route('public.invoice', ['token' => $invoice->penjualan->invoice_token])
              : null;
            $tanggalJatuhTempo = $invoice->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-';
            $totalTagihanText = $rupiah($invoice->total_tagihan);
            $reminderText = "Halo, kami mengingatkan invoice {$invoice->nomor_invoice} atas nama {$invoice->nama_toko} sebesar {$totalTagihanText} jatuh tempo pada {$tanggalJatuhTempo}.";
            if ($invoiceUrl) {
              $reminderText .= " Link invoice: {$invoiceUrl}";
            }
            $reminderUrl = 'https://wa.me/?text=' . rawurlencode($reminderText);
            $rowClass = $isPaid ? 'inv-row--paid' : ($isOverdue ? 'inv-row--overdue' : 'inv-row--waiting');
            $statusClass = $isPaid ? 'inv-status--paid' : ($isOverdue ? 'inv-status--overdue' : 'inv-status--waiting');
          @endphp
          <tr class="{{ $rowClass }}">
            <td data-label="Nama Toko">{{ $invoice->nama_toko }}</td>
            <td data-label="No. Invoice">{{ $invoice->nomor_invoice }}</td>
            <td data-label="Tgl Invoice">{{ $invoice->tanggal_invoice?->format('d/m/Y') ?? '-' }}</td>
            <td data-label="Tgl Jatuh Tempo">{{ $tanggalJatuhTempo }}</td>
            <td class="inv-days" data-label="Sisa Hari">
              @if($sisaHari < 0)
                -{{ abs($sisaHari) }} hari
              @elseif($sisaHari === 0)
                0 hari
              @else
                {{ $sisaHari }} hari
              @endif
            </td>
            <td class="inv-money" data-label="Total">{{ $rupiah($invoice->total_tagihan) }}</td>
            <td data-label="Status">
              <span class="inv-status {{ $statusClass }}">
                {{ $invoice->status_piutang_label }}
              </span>
            </td>
            <td data-label="Aksi">
              <div class="inv-row-actions">
                @if($isPaid)
                  <span class="inv-btn inv-btn--paid inv-btn--disabled">Sudah Lunas</span>
                @else
                  <form method="POST" action="{{ route('invoices.updateStatusLunas', $invoice) }}" onsubmit="return confirm('Tandai invoice {{ $invoice->nomor_invoice }} sebagai lunas?');">
                    @csrf
                    @method('PATCH')
                    <button class="inv-btn inv-btn--paid" type="submit">Set Lunas</button>
                  </form>
                @endif

                @if($invoiceUrl)
                  <a class="inv-btn inv-btn--send" href="{{ $invoiceUrl }}" target="_blank" rel="noopener">Cetak/Kirim</a>
                @else
                  <span class="inv-btn inv-btn--send inv-btn--disabled">Cetak/Kirim</span>
                @endif

                @if($isPaid)
                  <span class="inv-btn inv-btn--reminder inv-btn--disabled">Reminder</span>
                @else
                  <a class="inv-btn inv-btn--reminder" href="{{ $reminderUrl }}" target="_blank" rel="noopener">Reminder</a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="inv-empty">Tidak ada invoice sesuai filter.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="inv-pagination">
    {{ $invoices->links() }}
  </div>
</div>
@endsection
