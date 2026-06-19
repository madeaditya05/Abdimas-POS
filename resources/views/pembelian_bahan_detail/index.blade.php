@extends('layouts.main')
@section('title', 'Laporan Pembelian Bahan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">

  <style>
    .report-shell {
      padding: 16px;
    }

    .report-hero {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 18px;
      margin-bottom: 16px;
      padding: 18px 20px;
      border: 1px solid var(--bb-line, #e5e7eb);
      border-radius: 18px;
      background:
        radial-gradient(circle at top right, rgba(15, 118, 110, 0.10), transparent 34%),
        linear-gradient(135deg, #fffdf9 0%, #f7f2ee 100%);
      box-shadow: 0 10px 28px rgba(59, 47, 47, 0.06);
    }

    .report-hero h3 {
      margin: 0 0 8px 0;
      font-size: 22px;
      line-height: 1.2;
      color: #1f2937;
    }

    .report-hero p {
      margin: 0;
      max-width: 640px;
      color: #6b7280;
      font-size: 14px;
      line-height: 1.6;
    }

    .report-hero-badge {
      flex: 0 0 auto;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.85);
      border: 1px solid rgba(59, 47, 47, 0.12);
      color: #3b2f2f;
      font-weight: 700;
      font-size: 13px;
      white-space: nowrap;
    }

    .report-hero-badge svg {
      width: 16px;
      height: 16px;
    }

    .report-filter {
      margin-bottom: 16px;
    }

    .report-filter .form-grid {
      align-items: end;
    }

    .report-filter .form-actions {
      justify-content: flex-start;
      padding-top: 0;
      margin-top: 0;
    }

    .report-filter .form-input,
    .report-filter .form-select {
      height: 42px;
    }

    .report-filter .field-with-icon .form-input,
    .report-filter .field-with-icon .form-select {
      padding-right: 40px;
    }

    .report-filter .field-with-icon .field-icon {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      align-items: center;
      justify-content: center;
      width: 18px;
      height: 18px;
      color: var(--bb-muted, #64748b);
      pointer-events: none;
    }

    .report-filter .field-icon svg {
      width: 18px;
      height: 18px;
      stroke: currentColor;
    }

    .summary-box {
      margin: 18px 0 0;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 14px;
    }

    .summary-item {
      position: relative;
      overflow: hidden;
      padding: 16px 18px;
      border-radius: 18px;
      border: 1px solid var(--bb-line, #e5e7eb);
      background: #fff;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    }

    .summary-item::after {
      content: "";
      position: absolute;
      inset: auto -24px -24px auto;
      width: 90px;
      height: 90px;
      border-radius: 999px;
      opacity: 0.12;
      background: currentColor;
    }

    .summary-item--total {
      color: #0f766e;
      background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);
    }

    .summary-item--min {
      color: #b45309;
      background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
    }

    .summary-item--max {
      color: #b91c1c;
      background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
    }

    .summary-item--avg {
      color: #4338ca;
      background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);
    }

    .summary-label {
      position: relative;
      z-index: 1;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      color: #6b7280;
      margin-bottom: 8px;
    }

    .summary-value {
      position: relative;
      z-index: 1;
      font-size: 22px;
      font-weight: 800;
      line-height: 1.2;
      color: #111827;
    }

    .summary-note {
      position: relative;
      z-index: 1;
      margin-top: 6px;
      color: #6b7280;
      font-size: 12px;
      line-height: 1.5;
    }

    .report-table-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }

    .report-table-title {
      font-size: 16px;
      font-weight: 800;
      color: #1f2937;
    }

    .report-table-meta {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 999px;
      background: #f8fafc;
      border: 1px solid #e5e7eb;
      color: #64748b;
      font-size: 12px;
      font-weight: 700;
    }

    .empty-state {
      padding: 28px 14px;
      text-align: center;
      color: #64748b;
    }

    .empty-state strong {
      display: block;
      margin-bottom: 6px;
      color: #1f2937;
      font-size: 15px;
    }

    nav[role="navigation"] svg {
      width:16px !important;
      height:16px !important;
    }

    @media (max-width: 780px) {
      .report-shell {
        padding: 12px;
      }

      .report-hero {
        flex-direction: column;
      }

      .report-hero-badge {
        white-space: normal;
      }
    }
  </style>
@endpush

@section('content')
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Laporan Pembelian Bahan</h2>
  </div>

  <div class="report-shell">
    <div class="report-hero">
      <div>
        <h3>Monitor pembelian bahan dalam satu tampilan</h3>
        <p>
          Gunakan filter periode dan bahan untuk melihat pola belanja, harga rata-rata tertimbang,
          dan total pembelian bahan baku dengan gaya tampilan yang lebih ringkas seperti halaman penyesuaian stok.
        </p>
      </div>
      <div class="report-hero-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path d="M4 7h16" />
          <path d="M7 12h10" />
          <path d="M10 17h4" />
        </svg>
        <span>{{ count($pembelianDetail['rows']) }} baris ringkasan</span>
      </div>
    </div>

    <form method="GET" action="{{ route('pembelian-bahan-detail.index') }}" class="form report-filter">
      <div class="form-section">
        <div class="form-title">Filter Laporan</div>
        <div class="form-grid">
          <div class="form-field">
            <label for="start_date">Tanggal Mulai</label>
            <div class="field-with-icon">
              <input class="form-input"
                     type="date"
                     id="start_date"
                     name="start_date"
                     value="{{ request('start_date') }}"
                     required>
              <span class="field-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" aria-hidden="true"><path d="M7 3v3" /><path d="M17 3v3" /><path d="M4 9h16" /><path d="M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
              </span>
            </div>
            <div class="form-help">Awal periode pembelian yang ingin dianalisis.</div>
          </div>

          <div class="form-field">
            <label for="end_date">Tanggal Selesai</label>
            <div class="field-with-icon">
              <input class="form-input"
                     type="date"
                     id="end_date"
                     name="end_date"
                     value="{{ request('end_date') }}"
                     required>
              <span class="field-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" aria-hidden="true"><path d="M7 3v3" /><path d="M17 3v3" /><path d="M4 9h16" /><path d="M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
              </span>
            </div>
            <div class="form-help">Akhir periode pembelian yang ingin ditampilkan.</div>
          </div>

          <div class="form-field">
            <label for="bahan_id">Bahan Baku</label>
            <div class="field-with-icon">
              <select name="bahan_id" id="bahan_id" class="form-input form-select">
                <option value="">Semua Bahan</option>
                @foreach ($bahanList as $bahan)
                  <option value="{{ $bahan->id }}" {{ request('bahan_id') == $bahan->id ? 'selected' : '' }}>
                    {{ $bahan->kode_bahan }} - {{ $bahan->nama_bahan }}
                  </option>
                @endforeach
              </select>
              <span class="field-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16" /><path d="M7 12h10" /><path d="M10 17h4" /></svg>
              </span>
            </div>
            <div class="form-help">Pilih satu bahan tertentu jika ingin fokus pada item tertentu.</div>
          </div>

          <div class="form-field span-2">
            <div class="form-actions">
              <button class="btn btn--outline-coffee" type="submit">Terapkan Filter</button>
              <a href="{{ route('pembelian-bahan-detail.index') }}" class="btn btn--danger">Reset</a>
              <a class="btn btn--outline-success" target="_blank" href="{{ route('pembelian-bahan-detail.pdf', request()->all()) }}">PDF</a>
              <a class="btn btn--outline-success" target="_blank" href="{{ route('pembelian-bahan-detail.excel', request()->all()) }}">Excel</a>
            </div>
          </div>
        </div>
      </div>
    </form>

    <div class="summary-box">
      <div class="summary-item summary-item--total">
        <div class="summary-label">Grand Total Pembelian</div>
        <div class="summary-value">Rp {{ number_format($pembelianDetail['stats']['grand_total'], 0, ',', '.') }}</div>
        <div class="summary-note">Akumulasi total nilai pembelian bahan pada periode yang dipilih.</div>
      </div>

      <div class="summary-item summary-item--min">
        <div class="summary-label">Harga Terendah</div>
        <div class="summary-value">Rp {{ number_format($pembelianDetail['stats']['min'], 0, ',', '.') }}</div>
        <div class="summary-note">Harga satuan beli termurah yang tercatat pada filter saat ini.</div>
      </div>

      <div class="summary-item summary-item--max">
        <div class="summary-label">Harga Tertinggi</div>
        <div class="summary-value">Rp {{ number_format($pembelianDetail['stats']['max'], 0, ',', '.') }}</div>
        <div class="summary-note">Harga satuan beli tertinggi yang tercatat pada filter saat ini.</div>
      </div>

      <div class="summary-item summary-item--avg">
        <div class="summary-label">Harga Rata-rata Tertimbang</div>
        <div class="summary-value">Rp {{ number_format($pembelianDetail['stats']['avg'], 0, ',', '.') }}</div>
        <div class="summary-note">Rata-rata harga berdasarkan bobot kuantitas beli, bukan rata-rata sederhana.</div>
      </div>
    </div>

    <div class="form-section" style="margin-top:16px;">
      <div class="report-table-head">
        <div class="report-table-title">Ringkasan Transaksi Pembelian</div>
        <div class="report-table-meta">
          Periode {{ request('start_date') ?: now()->toDateString() }} s/d {{ request('end_date') ?: now()->toDateString() }}
        </div>
      </div>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Nama Bahan</th>
              <th class="num">Qty</th>
              <th>Satuan</th>
              <th class="num">Avg Harga Tertimbang</th>
              <th class="num">Total</th>
            </tr>
          </thead>

          <tbody>
            @forelse ($pembelianDetail['rows'] as $row)
              <tr>
                <td>{{ $row->tanggal }}</td>
                <td>{{ $row->nama_bahan }}</td>
                <td class="num">{{ number_format((float)$row->qty, 2, ',', '.') }}</td>
                <td>{{ $row->satuan_beli ?: '-' }}</td>
                <td class="num">Rp {{ number_format((float)$row->avg_harga, 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format((float)$row->total, 0, ',', '.') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5">
                  <div class="empty-state">
                    <strong>Belum ada data pembelian pada periode ini.</strong>
                    Coba ubah rentang tanggal atau pilih bahan lain agar data yang dicari lebih spesifik.
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
