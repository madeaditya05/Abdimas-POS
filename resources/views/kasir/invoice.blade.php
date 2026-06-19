@extends('layouts.print')

@section('title', 'Invoice ' . ($penjualan->kode_penjualan ?? ''))

@php
  $rupiah = function ($n) {
    $n = (float) ($n ?? 0);
    return 'Rp ' . number_format($n, 0, ',', '.');
  };

  $brand = config('invoice.brand_name') ?: config('app.name');
  $logoPath = config('invoice.logo_path');
  $logoUrl = null;
  if ($logoPath) {
    $logoUrl = str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')
      ? $logoPath
      : asset(ltrim($logoPath, '/'));
  }

  $invoiceTo = trim((string) ($penjualan->invoice_to_name ?? '')) ?: trim((string) ($penjualan->customer?->name ?? ''));
  $invoiceCompany = trim((string) ($penjualan->invoice_to_company ?? ''));
  $invoiceCompany = $invoiceCompany !== '' ? $invoiceCompany : null;

  $date = $penjualan->tanggal ? $penjualan->tanggal->format('d/m/Y') : now()->format('d/m/Y');
  $due = $penjualan->tempo_due_date ? $penjualan->tempo_due_date->format('d/m/Y') : null;

  $subtotal = (float) ($penjualan->subtotal_sebelum_diskon ?? 0);
  if ($subtotal <= 0) {
    $subtotal = (float) ($penjualan->details->sum('subtotal') ?: ($penjualan->total ?? 0));
  }
  $discount = (float) ($penjualan->diskon_nominal ?? 0);
  $discountPercent = (float) ($penjualan->diskon_persen ?? 0);
  $tax = 0;
  $grand = (float) ($penjualan->total ?? max(0, $subtotal - $discount + $tax));
  $rawbtMode = request()->boolean('rawbt', false);
  $kasir = $penjualan->user?->name ?? '-';
  $metode = strtoupper((string) ($payment?->pg_payment_type ?? $penjualan->metode ?? '-'));
  $paymentStatusRaw = strtolower((string) ($payment?->transaction_status ?? ($penjualan->metode === 'tempo' ? 'pending' : 'settlement')));
  $paymentStatus = match ($paymentStatusRaw) {
    'settlement', 'capture' => 'LUNAS',
    'pending' => $penjualan->metode === 'tempo' ? 'BELUM LUNAS' : 'PENDING',
    'expire', 'expired' => 'EXPIRED',
    'cancel', 'cancelled' => 'BATAL',
    default => strtoupper($paymentStatusRaw ?: '-'),
  };

  $thermalReceipt = [
    'outlet_name' => strtoupper($brand),
    'outlet_address' => config('invoice.brand_tagline') ?: '',
    'transaction_code' => $penjualan->kode_penjualan,
    'date' => $date,
    'customer_name' => $invoiceTo ?: '-',
    'staff_name' => $kasir,
    'payment_method' => $metode,
    'payment_status' => $paymentStatus,
    'subtotal' => $subtotal,
    'discount' => $discount,
    'discount_percent' => $discountPercent,
    'total' => $grand,
    'due_date' => $due,
    'items' => $penjualan->details->map(function ($d) {
      $price = (float) ($d->harga ?? 0);
      $qty = (int) ($d->qty ?? 0);

      return [
        'name' => $d->nama_cetak,
        'qty' => $qty,
        'price' => $price,
        'total' => (float) ($d->subtotal ?? ($price * $qty)),
      ];
    })->values(),
  ];
@endphp

@section('content')
@push('styles')
  <style>
    .inv-wrap { position: relative; }
    .inv-top { text-align:center; margin-top:6mm; }
    .inv-title {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 46px;
      font-weight: 800;
      letter-spacing: 1.2px;
      line-height: 1.1;
      margin: 0;
    }
    .inv-brand { margin-top: 4px; font-weight: 800; letter-spacing: 2.6px; }
    .inv-meta { display:flex; justify-content:space-between; margin-top: 18mm; gap: 18px; }
    .inv-to-label { color:#6b7280; font-size: 13px; }
    .inv-to-name { margin-top: 6px; font-weight: 600; font-size: 14px; min-height: 20px; }
    .inv-meta-right { width: 250px; font-size: 13px; }
    .inv-meta-row { display:flex; justify-content:space-between; gap: 12px; margin-top: 8px; }
    .inv-meta-row:first-child { margin-top: 0; }
    .inv-meta-key { color:#6b7280; }
    .inv-meta-val { font-weight: 600; }

    .inv-line-strong { border-top: 2px solid #111827; margin-top: 14mm; }

    .inv-table { margin-top: 8mm; }
    .inv-th { display:grid; grid-template-columns: 1fr 120px 70px 130px; gap: 10px; font-weight: 800; letter-spacing: 1px; font-size: 13px; }
    .inv-th > div { padding-bottom: 6px; }
    .inv-th-line { border-top: 1px solid #111827; margin-top: 2px; }
    .inv-body { min-height: 98mm; padding-top: 8mm; }
    .inv-row { display:grid; grid-template-columns: 1fr 120px 70px 130px; gap: 10px; font-size: 13px; margin-bottom: 10px; }
    .inv-row-desc { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .t-right { text-align:right; }

    .inv-bottom-line { border-top: 2px solid #111827; }
    .inv-bottom { display:flex; justify-content:space-between; gap: 18px; margin-top: 12mm; }
    .inv-payto { flex: 1; }
    .inv-payto-title { font-weight: 800; font-size: 13px; letter-spacing: 1px; }
    .inv-payto-box { margin-top: 10px; font-size: 12px; color:#6b7280; }
    .inv-payto-box b { color:#111827; }
    .inv-sum { width: 250px; font-size: 13px; }
    .inv-sum-row { display:flex; justify-content:space-between; margin-bottom: 10px; font-weight: 800; }
    .inv-sum-row.total { font-weight: 900; }

    .inv-footer { display:flex; justify-content:space-between; align-items:flex-end; gap: 18px; margin-top: 18mm; }
    .inv-thanks {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 34px;
      font-weight: 700;
      letter-spacing: .4px;
      line-height: 1.2;
      margin: 0;
    }
    .inv-logo { margin-top: 10px; }
    .inv-logo img { height: 76px; width: auto; }
    .inv-contact { width: 260px; font-size: 12px; color:#111827; }
    .inv-contact-row { display:flex; justify-content:flex-end; align-items:center; gap: 10px; margin-top: 8px; }
    .inv-contact-row:first-child { margin-top: 0; }
    .inv-contact-text { text-align:right; }
    .inv-ico { width: 14px; height: 14px; opacity:.85; }

    .inv-soft { color:#6b7280; }
  </style>
@endpush

<div class="page">
  <div class="no-print" style="display:flex; gap:8px; justify-content:flex-end; margin-bottom:12px;">
    @auth
      <a href="{{ route('kasir.index') }}" class="btn" style="text-decoration:none; padding:8px 10px; border:1px solid #d1d5db; border-radius:10px; color:#111827;">Kembali</a>
    @endauth
    <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" class="btn" style="text-decoration:none; padding:8px 10px; border:1px solid #d1d5db; border-radius:10px; color:#111827;">Cetak</a>
    @auth
      <button type="button" id="btnCetakRawBT" class="btn" style="cursor:pointer; padding:8px 10px; border:1px solid #0f766e; border-radius:10px; background:#fff; color:#0f766e;">Cetak Struk</button>
      @php
        $waMsg = "Invoice {$penjualan->kode_penjualan} - {$brand}%0A".
                 "Total: {$rupiah($grand)}%0A".
                 ($due ? "Jatuh tempo: {$due}%0A" : "").
                 "Link: " . urlencode(route('public.invoice', ['token' => $penjualan->invoice_token]));
        $waLink = "https://wa.me/?text={$waMsg}";
      @endphp
      <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn" style="text-decoration:none; padding:8px 10px; border:1px solid #d1d5db; border-radius:10px; color:#111827;">Kirim WhatsApp</a>
      <span id="rawbtStatus" style="align-self:center; font-size:12px; color:#334155;"></span>
    @endauth
  </div>

  <div class="inv-wrap">
    <div class="inv-top">
      <h1 class="inv-title">Invoice</h1>
      <div class="inv-brand">{{ strtoupper($brand) }}</div>
    </div>

    <div class="inv-meta">
      <div style="flex:1;">
        <div class="inv-to-label">Invoice To :</div>
        <div class="inv-to-name">{{ $invoiceTo ?: '-' }}</div>
        @if($invoiceCompany)
          <div class="inv-soft" style="margin-top:2px; font-size:13px;">{{ $invoiceCompany }}</div>
        @endif
      </div>
      <div class="inv-meta-right">
        <div class="inv-meta-row">
          <div class="inv-meta-key">Invoice No.</div>
          <div class="inv-meta-val">{{ $penjualan->kode_penjualan }}</div>
        </div>
        <div class="inv-meta-row">
          <div class="inv-meta-key">Date :</div>
          <div class="inv-meta-val">{{ $date }}</div>
        </div>
        @if($penjualan->metode === 'tempo' && $due)
          <div class="inv-meta-row">
            <div class="inv-meta-key">Tempo :</div>
            <div class="inv-meta-val">{{ $due }}</div>
          </div>
        @endif
      </div>
    </div>

    <div class="inv-line-strong"></div>

    <div class="inv-table">
      <div class="inv-th">
        <div>DESCRIPTION</div>
        <div class="t-right">PRICE</div>
        <div class="t-right">QTY</div>
        <div class="t-right">TOTAL</div>
      </div>
      <div class="inv-th-line"></div>

      <div class="inv-body">
        @foreach($penjualan->details as $d)
          @php
            $desc = $d->produk?->nama_barang ?? ('Item #' . $d->produk_id);
            $price = (float) ($d->harga ?? 0);
            $qty = (int) ($d->qty ?? 0);
            $line = $price * $qty;
          @endphp
          <div class="inv-row">
            <div class="inv-row-desc">{{ $desc }}</div>
            <div class="t-right">{{ $rupiah($price) }}</div>
            <div class="t-right">{{ $qty }}</div>
            <div class="t-right">{{ $rupiah($line) }}</div>
          </div>
        @endforeach
      </div>

      <div class="inv-bottom-line"></div>
    </div>

    <div class="inv-bottom">
      <div class="inv-payto">
        <div class="inv-payto-title">Send Payment To :</div>
        <div class="inv-payto-box">
          @if(config('invoice.bank_account_name') || config('invoice.bank_account_no'))
            <div><b>Account Name</b> : {{ config('invoice.bank_account_name') ?: '-' }}</div>
            <div style="margin-top:4px;"><b>Account No</b> : {{ config('invoice.bank_account_no') ?: '-' }}</div>
          @else
            <div>-</div>
          @endif
        </div>
      </div>
      <div class="inv-sum">
        <div class="inv-sum-row"><div>SUB TOTAL</div><div>{{ $rupiah($subtotal) }}</div></div>
        @if($discount > 0)
          <div class="inv-sum-row"><div>DISCOUNT {{ number_format($discountPercent, 2, ',', '.') }}%</div><div>-{{ $rupiah($discount) }}</div></div>
        @endif
        <div class="inv-sum-row"><div>TAX</div><div>{{ $rupiah($tax) }}</div></div>
        <div class="inv-sum-row total"><div>TOTAL</div><div>{{ $rupiah($grand) }}</div></div>
      </div>
    </div>

    <div class="inv-footer">
      <div style="flex:1;">
        <h2 class="inv-thanks">Thank You</h2>
        @if($logoUrl)
          <div class="inv-logo">
            <img src="{{ $logoUrl }}" alt="Logo">
          </div>
        @endif
      </div>

      <div class="inv-contact">
        @if(config('invoice.phone'))
          <div class="inv-contact-row">
            <div class="inv-contact-text">{{ config('invoice.phone') }}</div>
            <svg class="inv-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.12.86.3 1.7.54 2.5a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.58-1.06a2 2 0 0 1 2.11-.45c.8.24 1.64.42 2.5.54A2 2 0 0 1 22 16.92z"/>
            </svg>
          </div>
        @endif
        @if(config('invoice.email'))
          <div class="inv-contact-row">
            <div class="inv-contact-text">{{ config('invoice.email') }}</div>
            <svg class="inv-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16v16H4z"/>
              <path d="m22 6-10 7L2 6"/>
            </svg>
          </div>
        @endif
        @if(config('invoice.instagram'))
          <div class="inv-contact-row">
            <div class="inv-contact-text">{{ config('invoice.instagram') }}</div>
            <svg class="inv-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="5" ry="5"/>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
              <path d="M17.5 6.5h.01"/>
            </svg>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@push('scripts')
  @include('kasir.partials.rawbt-receipt-script')

  <script>
    (function () {
      const btnCetakRawBT = document.getElementById('btnCetakRawBT');
      const rawbtStatus = document.getElementById('rawbtStatus');
      const thermalReceipt = @json($thermalReceipt);

      function setRawBTStatus(text) {
        if (rawbtStatus) rawbtStatus.textContent = text;
      }

      if (btnCetakRawBT) {
        btnCetakRawBT.addEventListener('click', function () {
          window.cetakRawBT(thermalReceipt, {
            onStatus: setRawBTStatus
          });
        });
      }

      @if($rawbtMode)
        window.addEventListener('load', function () {
          window.cetakRawBT(thermalReceipt, {
            onStatus: setRawBTStatus
          });
        });
      @endif
    })();
  </script>

  @if($printMode && ! $rawbtMode)
    <script>
      window.addEventListener('load', function () {
        window.print();
      });
    </script>
  @endif
@endpush
@endsection
